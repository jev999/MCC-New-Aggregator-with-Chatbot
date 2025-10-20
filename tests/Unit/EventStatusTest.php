<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Event;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventStatusTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test admin
        $this->admin = Admin::factory()->create([
            'role' => 'superadmin',
            'department' => 'BSIT'
        ]);
    }

    /** @test */
    public function it_returns_tbd_status_when_event_date_is_null()
    {
        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'event_date' => null
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('tbd', $status['status']);
        $this->assertEquals('TBD', $status['text']);
        $this->assertEquals('calendar-question', $status['icon']);
    }

    /** @test */
    public function it_returns_upcoming_status_for_future_events()
    {
        $futureDate = Carbon::now()->addDays(5);
        
        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'event_date' => $futureDate
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('upcoming', $status['status']);
        $this->assertEquals('Upcoming', $status['text']);
        $this->assertEquals('calendar-plus', $status['icon']);
    }

    /** @test */
    public function it_returns_ongoing_status_for_events_today()
    {
        $today = Carbon::now();

        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'event_date' => $today
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('ongoing', $status['status']);
        $this->assertEquals('Ongoing', $status['text']);
        $this->assertEquals('calendar-day', $status['icon']);
    }

    /** @test */
    public function it_returns_ongoing_status_for_events_within_24_hours()
    {
        // Event started 12 hours ago - should still be ongoing
        $twelveHoursAgo = Carbon::now()->subHours(12);

        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'event_date' => $twelveHoursAgo
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('ongoing', $status['status']);
        $this->assertEquals('Ongoing', $status['text']);
        $this->assertEquals('calendar-day', $status['icon']);
    }

    /** @test */
    public function it_returns_ongoing_status_for_events_exactly_24_hours_ago()
    {
        // Event started exactly 24 hours ago - should still be ongoing (at the boundary)
        $exactlyTwentyFourHoursAgo = Carbon::now()->subHours(24);

        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'event_date' => $exactlyTwentyFourHoursAgo
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('ongoing', $status['status']);
        $this->assertEquals('Ongoing', $status['text']);
        $this->assertEquals('calendar-day', $status['icon']);
    }

    /** @test */
    public function it_returns_past_status_for_events_more_than_24_hours_ago()
    {
        // Event started 25 hours ago - should be past
        $twentyFiveHoursAgo = Carbon::now()->subHours(25);

        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'event_date' => $twentyFiveHoursAgo
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('past', $status['status']);
        $this->assertEquals('Past', $status['text']);
        $this->assertEquals('calendar-check', $status['icon']);
    }

    /** @test */
    public function it_returns_past_status_for_events_from_yesterday()
    {
        $yesterday = Carbon::now()->subDays(1);

        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'event_date' => $yesterday
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('past', $status['status']);
        $this->assertEquals('Past', $status['text']);
        $this->assertEquals('calendar-check', $status['icon']);
    }

    /** @test */
    public function it_returns_past_status_for_events_from_multiple_days_ago()
    {
        $pastDate = Carbon::now()->subDays(10);
        
        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'event_date' => $pastDate
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('past', $status['status']);
        $this->assertEquals('Past', $status['text']);
        $this->assertEquals('calendar-check', $status['icon']);
    }
}
