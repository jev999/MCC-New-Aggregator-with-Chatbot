<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Event;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventStatus24HourTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test admin
        $this->admin = Admin::factory()->create([
            'role' => 'superadmin',
            'department' => 'Administration'
        ]);
    }

    /** @test */
    public function event_remains_ongoing_for_24_hours()
    {
        // Create an event that started 12 hours ago
        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'title' => 'Test Event - 12 Hours Ago',
            'event_date' => Carbon::now()->subHours(12),
            'is_published' => true
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('ongoing', $status['status']);
        $this->assertEquals('Ongoing', $status['text']);
        $this->assertEquals('calendar-day', $status['icon']);
    }

    /** @test */
    public function event_remains_ongoing_at_24_hour_boundary()
    {
        // Create an event that started exactly 24 hours ago
        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'title' => 'Test Event - Exactly 24 Hours Ago',
            'event_date' => Carbon::now()->subHours(24),
            'is_published' => true
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('ongoing', $status['status']);
        $this->assertEquals('Ongoing', $status['text']);
        $this->assertEquals('calendar-day', $status['icon']);
    }

    /** @test */
    public function event_becomes_past_after_24_hours()
    {
        // Create an event that started 25 hours ago
        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'title' => 'Test Event - 25 Hours Ago',
            'event_date' => Carbon::now()->subHours(25),
            'is_published' => true
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('past', $status['status']);
        $this->assertEquals('Past', $status['text']);
        $this->assertEquals('calendar-check', $status['icon']);
    }

    /** @test */
    public function event_becomes_past_after_48_hours()
    {
        // Create an event that started 48 hours ago
        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'title' => 'Test Event - 48 Hours Ago',
            'event_date' => Carbon::now()->subHours(48),
            'is_published' => true
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('past', $status['status']);
        $this->assertEquals('Past', $status['text']);
        $this->assertEquals('calendar-check', $status['icon']);
    }

    /** @test */
    public function upcoming_event_remains_upcoming()
    {
        // Create an event that will start in 2 hours
        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'title' => 'Test Event - Future',
            'event_date' => Carbon::now()->addHours(2),
            'is_published' => true
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('upcoming', $status['status']);
        $this->assertEquals('Upcoming', $status['text']);
        $this->assertEquals('calendar-plus', $status['icon']);
    }

    /** @test */
    public function event_just_started_is_ongoing()
    {
        // Create an event that started 1 hour ago
        $event = Event::factory()->create([
            'admin_id' => $this->admin->id,
            'title' => 'Test Event - Just Started',
            'event_date' => Carbon::now()->subHour(),
            'is_published' => true
        ]);

        $status = $event->getEventStatus();

        $this->assertEquals('ongoing', $status['status']);
        $this->assertEquals('Ongoing', $status['text']);
        $this->assertEquals('calendar-day', $status['icon']);
    }
}
