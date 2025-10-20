# SuperAdmin Events Integration in User Dashboard

## Overview
This document explains how events created by superadmin are automatically displayed in the user dashboard, providing students with visibility into institution-wide events and activities.

## ✅ **Recent Updates**
- **Added Published Icon**: SuperAdmin events create/edit forms now include a "Publish Event" checkbox with an eye icon
- **Enhanced UI**: Clean checkbox styling with hover effects and visual feedback
- **Immediate Visibility**: When superadmin checks "Publish Event", the event immediately appears in user dashboards
- **Role-based Attribution**: Events show "MCC Administration" as the organizer for superadmin events
- **24-Hour Event Duration**: Events now remain "ongoing" for 24 hours instead of 2 hours before becoming "past"

## Implementation Details

### 1. User Dashboard Controller Updates
The `UserDashboardController` has been enhanced to:
- Fetch all published events from any admin (including superadmin)
- Include both upcoming and recent past events (within 30 days)
- Show TBD (To Be Determined) events
- Load admin relationship data for proper attribution

### 2. Event Query Logic
```php
$events = Event::where('is_published', true)
    ->with('admin')
    ->where(function($query) {
        // Show upcoming events and events from the last 30 days
        $query->where('event_date', '>=', now()->subDays(30))
              ->orWhereNull('event_date'); // Include TBD events
    })
    ->orderByRaw('CASE WHEN event_date IS NULL THEN 1 ELSE 0 END') // TBD events last
    ->orderBy('event_date', 'asc')
    ->take(6)
    ->get();
```

### 3. Event Display Features
Each event card in the user dashboard now shows:
- **Event Title** - The name of the event
- **Description** - Brief description with character limit
- **Date & Time** - When the event will occur (or "TBD" if not set)
- **Location** - Where the event will take place
- **Status Badge** - Upcoming, Ongoing, Past, or TBD
- **Organizer** - Who created the event:
  - "MCC Administration" for superadmin events
  - "[Department] Department" for department admin events
  - Admin name for regular admin events

### 4. Event Status Logic
Events are automatically categorized based on their date:
- **TBD**: Events with no date set
- **Upcoming**: Events scheduled for the future
- **Ongoing**: Events happening now (within 24-hour window from start time)
- **Past**: Events that ended more than 24 hours ago

### 5. Visual Indicators
- **Organizer Badge**: Shows who created the event with appropriate icon
- **Status Badge**: Color-coded status indicator
- **Date Display**: Formatted date or "Date TBD" for undetermined events

## Benefits for Students

### 1. Centralized Event Information
Students can see all published events from:
- MCC Administration (superadmin)
- Individual departments
- Other administrative units

### 2. Clear Attribution
Each event clearly shows its source, helping students understand:
- Institution-wide events from MCC Administration
- Department-specific events
- Who to contact for more information

### 3. Comprehensive Timeline
The dashboard shows:
- Upcoming events to plan for
- Recent past events for reference
- TBD events to watch for updates

## Usage Examples

### SuperAdmin Creating Events
1. SuperAdmin logs into the admin panel
2. Creates a new event (e.g., "Foundation Week 2024")
3. Sets the event as "Published"
4. Event automatically appears in all user dashboards
5. Students see "MCC Administration" as the organizer

### Department Admin Creating Events
1. Department Admin creates department-specific event
2. Event appears in user dashboard
3. Students see "[Department] Department" as organizer

## Technical Implementation

### Database Structure
Events table includes:
- `admin_id` - Links to the admin who created the event
- `is_published` - Controls visibility to users
- `event_date` - Can be null for TBD events

### Controller Logic
```php
// Get published events from all admins
$events = Event::where('is_published', true)
    ->with('admin') // Load admin relationship
    ->where(function($query) {
        $query->where('event_date', '>=', now()->subDays(30))
              ->orWhereNull('event_date');
    })
    ->orderBy('event_date', 'asc')
    ->take(6)
    ->get();
```

### View Logic
```blade
@if($event->admin)
    <div class="event-organizer">
        <i class="fas fa-user-tie"></i>
        <span>
            @if($event->admin->role === 'super_admin')
                MCC Administration
            @elseif($event->admin->role === 'department_admin')
                {{ $event->admin->department }} Department
            @else
                {{ $event->admin->name }}
            @endif
        </span>
    </div>
@endif
```

## Testing
A comprehensive test suite has been created to verify:
- SuperAdmin events appear in user dashboard
- Department admin events appear correctly
- Unpublished events are hidden
- Event counts are accurate
- Past events within 30 days are included
- TBD events are displayed properly

## Future Enhancements
Potential improvements could include:
- Event categories/tags
- RSVP functionality
- Event reminders
- Calendar integration
- Event search and filtering
