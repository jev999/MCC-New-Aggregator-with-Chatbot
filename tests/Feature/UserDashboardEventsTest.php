<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Event;
use App\Models\Announcement;
use App\Models\News;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserDashboardEventsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $superAdmin;
    protected $departmentAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create([
            'role' => 'student',
            'department' => 'BSIT'
        ]);

        // Create superadmin
        $this->superAdmin = Admin::factory()->create([
            'role' => 'super_admin',
            'name' => 'Super Admin',
            'department' => 'Administration'
        ]);

        // Create department admin
        $this->departmentAdmin = Admin::factory()->create([
            'role' => 'department_admin',
            'name' => 'BSIT Admin',
            'department' => 'BSIT'
        ]);
    }

    /** @test */
    public function user_dashboard_displays_published_events_from_superadmin()
    {
        // Create published event by superadmin
        $superAdminEvent = Event::factory()->create([
            'admin_id' => $this->superAdmin->id,
            'title' => 'MCC Foundation Week',
            'description' => 'Annual foundation week celebration',
            'event_date' => Carbon::now()->addDays(7),
            'is_published' => true
        ]);

        // Create unpublished event by superadmin (should not appear)
        Event::factory()->create([
            'admin_id' => $this->superAdmin->id,
            'title' => 'Unpublished Event',
            'is_published' => false
        ]);

        // Login as user and visit dashboard
        $response = $this->actingAs($this->user)
            ->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('MCC Foundation Week');
        $response->assertSee('MCC Administration'); // Should show superadmin as organizer
        $response->assertDontSee('Unpublished Event');
    }

    /** @test */
    public function user_dashboard_displays_events_from_department_admin()
    {
        // Create published event by department admin
        $departmentEvent = Event::factory()->create([
            'admin_id' => $this->departmentAdmin->id,
            'title' => 'BSIT Department Seminar',
            'description' => 'Technology seminar for BSIT students',
            'event_date' => Carbon::now()->addDays(5),
            'is_published' => true
        ]);

        // Login as user and visit dashboard
        $response = $this->actingAs($this->user)
            ->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('BSIT Department Seminar');
        $response->assertSee('BSIT Department'); // Should show department as organizer
    }

    /** @test */
    public function user_dashboard_shows_correct_event_counts()
    {
        // Create multiple published events
        Event::factory()->count(3)->create([
            'admin_id' => $this->superAdmin->id,
            'is_published' => true,
            'event_date' => Carbon::now()->addDays(rand(1, 30))
        ]);

        Event::factory()->count(2)->create([
            'admin_id' => $this->departmentAdmin->id,
            'is_published' => true,
            'event_date' => Carbon::now()->addDays(rand(1, 30))
        ]);

        // Create unpublished events (should not be counted)
        Event::factory()->count(2)->create([
            'admin_id' => $this->superAdmin->id,
            'is_published' => false
        ]);

        // Login as user and visit dashboard
        $response = $this->actingAs($this->user)
            ->get(route('user.dashboard'));

        $response->assertStatus(200);
        
        // Check that the total count shows 5 published events
        $response->assertViewHas('totalEvents', 5);
    }

    /** @test */
    public function user_dashboard_includes_past_events_within_30_days()
    {
        // Create recent past event (within 30 days)
        $recentPastEvent = Event::factory()->create([
            'admin_id' => $this->superAdmin->id,
            'title' => 'Recent Past Event',
            'event_date' => Carbon::now()->subDays(15),
            'is_published' => true
        ]);

        // Create old past event (more than 30 days ago)
        $oldPastEvent = Event::factory()->create([
            'admin_id' => $this->superAdmin->id,
            'title' => 'Old Past Event',
            'event_date' => Carbon::now()->subDays(45),
            'is_published' => true
        ]);

        // Login as user and visit dashboard
        $response = $this->actingAs($this->user)
            ->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Recent Past Event'); // Should appear
        $response->assertDontSee('Old Past Event'); // Should not appear
    }

    /** @test */
    public function user_dashboard_includes_tbd_events()
    {
        // Create TBD event (no date set)
        $tbdEvent = Event::factory()->create([
            'admin_id' => $this->superAdmin->id,
            'title' => 'TBD Event',
            'event_date' => null,
            'is_published' => true
        ]);

        // Login as user and visit dashboard
        $response = $this->actingAs($this->user)
            ->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('TBD Event');
        $response->assertSee('Date TBD');
    }
}
