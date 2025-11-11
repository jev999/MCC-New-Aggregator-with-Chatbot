<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        // If admin is not authenticated, redirect to login
        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Department admins can only see their own events
        if ($admin->isDepartmentAdmin()) {
            $events = Event::with('admin')
                           ->where('admin_id', $admin->id)
                           ->latest()
                           ->get();
            return view('department-admin.events.index', compact('events'));
        }

        // Office admins can only see their own events
        if ($admin->isOfficeAdmin()) {
            $events = Event::with('admin')
                           ->where('admin_id', $admin->id)
                           ->latest()
                           ->get();
            $office = $admin->office;
            return view('office-admin.events.index', compact('events', 'office'));
        }

        // Super admins and regular admins can see all events
        $events = Event::with('admin')->latest()->get();

        // Determine view based on admin type
        if ($admin->isSuperAdmin()) {
            return view('superadmin.events.index', compact('events'));
        }

        return view('superadmin.events.index', compact('events'));
    }

    public function create()
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        if ($admin->isDepartmentAdmin()) {
            return view('department-admin.events.create');
        }

        if ($admin->isOfficeAdmin()) {
            $office = $admin->office;
            return view('office-admin.events.create', compact('office'));
        }

        if ($admin->isSuperAdmin()) {
            return view('superadmin.events.create');
        }

        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Different validation rules based on admin type
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'images' => 'nullable|array|max:2',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'videos' => 'nullable|array|max:1',
            'videos.*' => 'mimes:mp4|max:51200',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video' => 'nullable|mimes:mp4|max:51200',
            'csv_file' => 'nullable|mimes:csv,txt|max:2048',
            'is_published' => 'boolean',
        ];

        // Handle different date formats based on admin type
        if ($admin->isOfficeAdmin()) {
            // Office admin uses datetime-local input
            $validationRules['event_date'] = 'required|date|after_or_equal:now';
        } else {
            // Other admins use separate date and time inputs
            $validationRules['event_date'] = 'required|date|after_or_equal:today';
            $validationRules['event_time'] = 'nullable|date_format:H:i';
        }

        // Add visibility scope validation for superadmin, department admin, and office admin
        if ($admin->isSuperAdmin()) {
            $validationRules['visibility_scope'] = 'required|in:department,office,all';
            $validationRules['target_department'] = 'nullable|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Secondary Education,Bachelor of Science in Hospitality Management';
            $validationRules['target_office'] = 'nullable|in:NSTP,SSC,GUIDANCE,REGISTRAR,CLINIC';
        } elseif ($admin->isDepartmentAdmin()) {
            $validationRules['visibility_scope'] = 'required|in:department,all';
        } elseif ($admin->isOfficeAdmin()) {
            $validationRules['visibility_scope'] = 'required|in:office,all';
        }

        $request->validate($validationRules);

        // Handle file uploads
        $imagePath = null;
        $videoPath = null;
        $csvPath = null;
        $imagePaths = [];
        $videoPaths = [];

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('event-images', 'public');
            }
        }

        // Handle multiple videos
        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $video) {
                $videoPaths[] = $video->store('event-videos', 'public');
            }
        }

        // Handle single image (backward compatibility)
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('event-images', 'public');
        }

        // Handle single video (backward compatibility)
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('event-videos', 'public');
        }

        if ($request->hasFile('csv_file')) {
            $csvPath = $request->file('csv_file')->store('event-csv', 'public');
        }

        // Handle department and office visibility logic
        $targetDepartment = null;
        $targetOffice = null;

        if ($admin->isOfficeAdmin()) {
            // Office admins: can select office or all departments
            $visibilityScope = $request->input('visibility_scope', 'office');
            if ($visibilityScope === 'office') {
                $targetOffice = $admin->office;
            }
        } elseif ($admin->isDepartmentAdmin()) {
            $visibilityScope = $request->input('visibility_scope', 'department');
            if ($visibilityScope === 'department') {
                $targetDepartment = $admin->department;
            }
        } else {
            // Super admin
            $visibilityScope = $request->input('visibility_scope', 'all');
            if ($visibilityScope === 'department' && $request->has('target_department')) {
                $targetDepartment = $request->input('target_department');
            } elseif ($visibilityScope === 'office' && $request->has('target_office')) {
                $targetOffice = $request->input('target_office');
            }
        }

        // Handle date/time based on admin type
        $eventDate = $request->input('event_date');
        $eventTime = null;

        if ($admin->isOfficeAdmin()) {
            // For office admin, event_date is datetime-local format
            // Split it into date and time components for storage
            if ($eventDate) {
                $datetime = \Carbon\Carbon::parse($eventDate);
                $eventDate = $datetime->format('Y-m-d');
                $eventTime = $datetime->format('H:i:s');
            }
        } else {
            // For other admins, use separate event_time field
            $eventTime = $request->input('event_time');
        }

        Event::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'event_date' => $eventDate,
            'event_time' => $eventTime,
            'location' => $request->input('location'),
            'image' => $imagePath,
            'video' => $videoPath,
            'csv_file' => $csvPath,
            'image_paths' => !empty($imagePaths) ? $imagePaths : null,
            'video_paths' => !empty($videoPaths) ? $videoPaths : null,
            'is_published' => $request->has('is_published'),
            'visibility_scope' => $visibilityScope,
            'target_department' => $targetDepartment,
            'target_office' => $targetOffice,
            'admin_id' => Auth::guard('admin')->id(),
        ]);

        $admin = Auth::guard('admin')->user();

        if ($admin->isDepartmentAdmin()) {
            return redirect()->route('department-admin.events.index')
                ->with('success', 'Event created successfully!');
        }

        if ($admin->isOfficeAdmin()) {
            return redirect()->route('office-admin.events.index')
                ->with('success', 'Event created successfully!');
        }

        if ($admin->isSuperAdmin()) {
            return redirect()->route('superadmin.events.index')
                ->with('success', 'Event created successfully!');
        }

        return redirect()->route('events.index')
            ->with('success', 'Event created successfully!');
    }

    public function show(Event $event)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Department admins can only view their own events
        if ($admin->isDepartmentAdmin() && $event->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Office admins can only view their own events
        if ($admin->isOfficeAdmin() && $event->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this event.');
        }

        if ($admin->isDepartmentAdmin()) {
            return view('department-admin.events.show', compact('event'));
        }

        if ($admin->isOfficeAdmin()) {
            $office = $admin->office;
            return view('office-admin.events.show', compact('event', 'office'));
        }

        if ($admin->isSuperAdmin()) {
            return view('superadmin.events.show', compact('event'));
        }

        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Department admins can only edit their own events
        if ($admin->isDepartmentAdmin() && $event->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Office admins can only edit their own events
        if ($admin->isOfficeAdmin() && $event->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this event.');
        }

        if ($admin->isDepartmentAdmin()) {
            return view('department-admin.events.edit', compact('event'));
        }

        if ($admin->isOfficeAdmin()) {
            $office = $admin->office;
            return view('office-admin.events.edit', compact('event', 'office'));
        }

        if ($admin->isSuperAdmin()) {
            return view('superadmin.events.edit', compact('event'));
        }

        return view('superadmin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        // DEBUG: Log the incoming request
        \Log::info('Event Update Debug - Event ID: ' . $event->id, [
            'request_data' => $request->all(),
            'has_image_file' => $request->hasFile('image'),
            'files' => $request->allFiles(),
            'current_image' => $event->image,
        ]);

        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Handle different datetime input formats based on admin type
        $validationRules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'images' => 'nullable|array|max:2',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'videos' => 'nullable|array|max:1',
            'videos.*' => 'mimes:mp4|max:51200',
            'is_published' => 'boolean',
            // Media removal parameters
            'remove_legacy_image' => 'nullable|boolean',
            'remove_legacy_video' => 'nullable|boolean',
            'remove_images' => 'nullable|array',
            'remove_videos' => 'nullable|array',
        ];

        // For office admin, validate datetime-local field (allow past dates for editing)
        if ($admin->isOfficeAdmin()) {
            $validationRules['event_datetime'] = 'nullable|date';
        } else if ($admin->isSuperAdmin()) {
            // For superadmin, make event_date optional
            $validationRules['event_date'] = 'nullable|date';
            $validationRules['event_time'] = 'nullable|date_format:H:i';
        } else {
            // For other admins, validate separate date and time fields (allow past dates for editing)
            $validationRules['event_date'] = 'required|date';
            $validationRules['event_time'] = 'nullable|date_format:H:i';
        }

        // Add visibility scope validation for superadmin and department admin only
        // Office admin visibility is automatically handled in the update logic
        if ($admin->isSuperAdmin()) {
            $validationRules['visibility_scope'] = 'required|in:department,office,all';
            $validationRules['target_department'] = 'nullable|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Secondary Education,Bachelor of Science in Hospitality Management';
            $validationRules['target_office'] = 'nullable|in:NSTP,SSC,GUIDANCE,REGISTRAR,CLINIC';
        } elseif ($admin->isDepartmentAdmin()) {
            $validationRules['visibility_scope'] = 'required|in:department,all';
        }
        // Note: Office admin visibility_scope is not required in validation as it's automatically set to 'office'

        $request->validate($validationRules);

        // Handle date/time based on admin type
        $eventDate = null;
        $eventTime = null;

        if ($admin->isOfficeAdmin()) {
            // For office admin, use datetime-local field
            $eventDateTime = $request->input('event_datetime');
            if ($eventDateTime) {
                $datetime = \Carbon\Carbon::parse($eventDateTime);
                $eventDate = $datetime->format('Y-m-d');
                $eventTime = $datetime->format('H:i:s');
            }
        } else {
            // For other admins, use separate date and time fields
            $eventDate = $request->input('event_date');
            $eventTime = $request->input('event_time');
        }

        // Handle file uploads
        $updateData = [
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'event_date' => $eventDate,
            'event_time' => $eventTime,
            'location' => $request->input('location'),
            'is_published' => $request->has('is_published'),
        ];

        // Handle visibility scope and targets based on admin type
        if ($admin->isOfficeAdmin()) {
            // Office admins: events are always office-targeted
            $updateData['visibility_scope'] = 'office';
            $updateData['target_office'] = $admin->office;
            $updateData['target_department'] = null;
        } elseif ($admin->isSuperAdmin()) {
            $visibilityScope = $request->input('visibility_scope', 'all');
            $targetDepartment = null;
            $targetOffice = null;

            if ($visibilityScope === 'department' && $request->has('target_department')) {
                $targetDepartment = $request->input('target_department');
            } elseif ($visibilityScope === 'office' && $request->has('target_office')) {
                $targetOffice = $request->input('target_office');
            }

            $updateData['visibility_scope'] = $visibilityScope;
            $updateData['target_department'] = $targetDepartment;
            $updateData['target_office'] = $targetOffice;
        } elseif ($admin->isDepartmentAdmin()) {
            // Department admins: keep existing logic
            $visibilityScope = $request->input('visibility_scope', 'department');
            $updateData['visibility_scope'] = $visibilityScope;
            $updateData['target_department'] = $admin->department;
            $updateData['target_office'] = null;
        }

        // Handle existing multiple images removal
        $currentImagePaths = $event->image_paths ? (is_string($event->image_paths) ? json_decode($event->image_paths, true) : $event->image_paths) : [];
        $removeImages = $request->input('remove_images');
        
        // Debug logging
        \Log::info('Media Removal Debug - Images:', [
            'current_image_paths' => $currentImagePaths,
            'remove_images_input' => $removeImages,
            'event_id' => $event->id
        ]);
        
        if ($removeImages) {
            // Handle both array and string inputs
            $removeIndices = is_array($removeImages) ? $removeImages : explode(',', $removeImages);
            \Log::info('Remove indices:', $removeIndices);
            
            foreach ($removeIndices as $index) {
                $index = trim($index); // Remove any whitespace
                if (isset($currentImagePaths[$index])) {
                    \Log::info('Removing image at index ' . $index . ': ' . $currentImagePaths[$index]);
                    if (Storage::disk('public')->exists($currentImagePaths[$index])) {
                        Storage::disk('public')->delete($currentImagePaths[$index]);
                        \Log::info('File deleted from storage: ' . $currentImagePaths[$index]);
                    }
                    unset($currentImagePaths[$index]);
                }
            }
            // Reindex array to remove gaps
            $currentImagePaths = array_values($currentImagePaths);
            \Log::info('Final image paths after removal:', $currentImagePaths);
        }

        // Handle single video removal (legacy)
        $removeLegacyVideo = $request->boolean('remove_legacy_video') || $request->boolean('remove_video');
        if ($removeLegacyVideo) {
            \Log::info('Removing legacy single video (event): ' . $event->video);
            if ($event->video && Storage::disk('public')->exists($event->video)) {
                Storage::disk('public')->delete($event->video);
                \Log::info('Legacy event video file deleted from storage: ' . $event->video);
            }
            $updateData['video'] = null;
        }

        // Handle existing multiple videos removal
        $currentVideoPaths = $event->video_paths ? (is_string($event->video_paths) ? json_decode($event->video_paths, true) : $event->video_paths) : [];
        $removeVideos = $request->input('remove_videos');
        $removedAnyVideoFlag = false;
        
        // Debug logging for videos
        \Log::info('Media Removal Debug - Videos:', [
            'current_video_paths' => $currentVideoPaths,
            'remove_videos_input' => $removeVideos,
            'event_id' => $event->id
        ]);
        
        if ($removeVideos) {
            // Handle both array and string inputs
            $removeIndices = is_array($removeVideos) ? $removeVideos : explode(',', $removeVideos);
            \Log::info('Remove video indices:', $removeIndices);
            
            foreach ($removeIndices as $index) {
                $index = trim($index); // Remove any whitespace
                if (isset($currentVideoPaths[$index])) {
                    \Log::info('Removing video at index ' . $index . ': ' . $currentVideoPaths[$index]);
                    if (Storage::disk('public')->exists($currentVideoPaths[$index])) {
                        Storage::disk('public')->delete($currentVideoPaths[$index]);
                        \Log::info('Video file deleted from storage: ' . $currentVideoPaths[$index]);
                    }
                    unset($currentVideoPaths[$index]);
                    $removedAnyVideoFlag = true;
                }
            }
            // Reindex array to remove gaps
            $currentVideoPaths = array_values($currentVideoPaths);
            \Log::info('Final video paths after removal:', $currentVideoPaths);
        }

        // Handle new multiple images upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $currentImagePaths[] = $image->store('event-images', 'public');
            }
        }

        // Handle new videos upload with replacement semantics (max 1)
        if ($request->hasFile('videos')) {
            // Always replace legacy single video if present
            if ($event->video) {
                \Log::info('Replacing legacy single event video due to new upload: ' . $event->video);
                if (Storage::disk('public')->exists($event->video)) {
                    Storage::disk('public')->delete($event->video);
                }
                $updateData['video'] = null;
            }

            // Enforce max 1 video overall: remove existing multi videos
            if (!empty($currentVideoPaths)) {
                \Log::info('Replacing existing multiple event videos with new upload', ['existing' => $currentVideoPaths]);
                foreach ($currentVideoPaths as $existingPath) {
                    if ($existingPath && Storage::disk('public')->exists($existingPath)) {
                        Storage::disk('public')->delete($existingPath);
                    }
                }
                $currentVideoPaths = [];
            }

            // Store first uploaded video only
            $uploadedVideos = $request->file('videos');
            $firstVideo = is_array($uploadedVideos) ? $uploadedVideos[0] : $uploadedVideos;
            if ($firstVideo) {
                $stored = $firstVideo->store('event-videos', 'public');
                $currentVideoPaths[] = $stored;
                \Log::info('Stored replacement event video', ['path' => $stored]);
            }
        }

        // Update the media paths in the database
        $updateData['image_paths'] = !empty($currentImagePaths) ? $currentImagePaths : null;
        $updateData['video_paths'] = !empty($currentVideoPaths) ? $currentVideoPaths : null;

        // Safety: if a removal flag was set and no new videos remain, also null legacy path
        if ($removedAnyVideoFlag || $removeLegacyVideo) {
            if (empty($currentVideoPaths)) {
                $updateData['video_paths'] = null;
            }
            $updateData['video'] = $updateData['video'] ?? null;
        }

        // Debug final update data
        \Log::info('Final update data for media:', [
            'image_paths' => $updateData['image_paths'],
            'video_paths' => $updateData['video_paths'],
            'event_id' => $event->id
        ]);

        $event->update($updateData);

        // DEBUG: Log the result after update
        $event->refresh();
        \Log::info('Event Update Debug - After Update', [
            'event_id' => $event->id,
            'final_image' => $event->image,
            'update_data_image' => $updateData['image'] ?? 'NOT_SET',
            'updated_at' => $event->updated_at,
        ]);

        $admin = Auth::guard('admin')->user();

        if ($admin->isDepartmentAdmin()) {
            return redirect()->route('department-admin.events.index')
                ->with('success', 'Event updated successfully!');
        }

        if ($admin->isOfficeAdmin()) {
            return redirect()->route('office-admin.events.index')
                ->with('success', 'Event updated successfully!');
        }

        if ($admin->isSuperAdmin()) {
            return redirect()->route('superadmin.events.index')
                ->with('success', 'Event updated successfully!');
        }

        return redirect()->route('events.index')
            ->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Department admins can only delete their own events
        if ($admin->isDepartmentAdmin() && $event->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Office admins can only delete their own events
        if ($admin->isOfficeAdmin() && $event->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Delete associated files
        if ($event->image && Storage::disk('public')->exists($event->image)) {
            Storage::disk('public')->delete($event->image);
        }

        if ($event->video && Storage::disk('public')->exists($event->video)) {
            Storage::disk('public')->delete($event->video);
        }

        if ($event->csv_file && Storage::disk('public')->exists($event->csv_file)) {
            Storage::disk('public')->delete($event->csv_file);
        }

        $event->delete();

        if ($admin->isDepartmentAdmin()) {
            return redirect()->route('department-admin.events.index')
                ->with('success', 'Event deleted successfully!');
        }

        if ($admin->isOfficeAdmin()) {
            return redirect()->route('office-admin.events.index')
                ->with('success', 'Event deleted successfully!');
        }

        if ($admin->isSuperAdmin()) {
            return redirect()->route('superadmin.events.index')
                ->with('success', 'Event deleted successfully!');
        }

        return redirect()->route('events.index')
            ->with('success', 'Event deleted successfully!');
    }
}
