<?php
namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Department admins can only see their own announcements
        if ($admin->isDepartmentAdmin()) {
            $announcements = Announcement::with('admin')
                                        ->where('admin_id', $admin->id)
                                        ->latest()
                                        ->get();
            return view('department-admin.announcements.index', compact('announcements'));
        }

        // Office admins can see announcements they created (both office-specific and all-departments)
        if ($admin->isOfficeAdmin()) {
            $announcements = Announcement::with('admin')
                                        ->where('admin_id', $admin->id)
                                        ->latest()
                                        ->get();
            $office = $admin->office;
            return view('office-admin.announcements.index', compact('announcements', 'office'));
        }

        // Super admins and regular admins can see all announcements
        $announcements = Announcement::with('admin')->latest()->get();

        // Determine view based on admin type
        if ($admin->isSuperAdmin()) {
            return view('superadmin.announcements.index', compact('announcements'));
        }

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        if ($admin->isDepartmentAdmin()) {
            return view('department-admin.announcements.create');
        }

        if ($admin->isOfficeAdmin()) {
            $office = $admin->office;
            return view('office-admin.announcements.create', compact('office'));
        }

        if ($admin->isSuperAdmin()) {
            return view('superadmin.announcements.create');
        }

        return view('admin.announcements.create');
    }

    public function store(Request $request)
    {
        \Log::info('AnnouncementController@store method called');
        \Log::info('Request received:', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'all_data' => $request->all()
        ]);
        
        try {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin) {
                \Log::error('No authenticated admin found');
                return redirect()->route('admin.login')->with('error', 'Please log in first.');
            }
            
            \Log::info('Authenticated admin found:', [
                'id' => $admin->id,
                'username' => $admin->username,
                'role' => $admin->role
            ]);

            // Log the incoming request data for debugging
            \Log::info('Announcement creation attempt:', [
                'admin_id' => $admin->id,
                'admin_role' => $admin->role,
                'request_data' => $request->all(),
                'has_images' => $request->hasFile('images'),
                'has_videos' => $request->hasFile('videos'),
                'images_count' => $request->hasFile('images') ? count($request->file('images')) : 0,
                'videos_count' => $request->hasFile('videos') ? count($request->file('videos')) : 0,
                'request_method' => $request->method(),
                'request_url' => $request->url(),
            ]);

            // Different validation rules based on admin type
            $validationRules = [
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'expires_at' => 'nullable|date|after:now',
                'images' => 'nullable|array|max:2',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
                'videos' => 'nullable|array|max:1',
                'videos.*' => 'mimes:mp4|max:51200',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'video' => 'nullable|mimes:mp4|max:51200',
                'csv_file' => 'nullable|mimes:csv,txt|max:2048',
                'is_published' => 'boolean',
            ];

            // Only require visibility_scope for super admins, department admins, and office admins
            if ($admin->isSuperAdmin()) {
                $validationRules['visibility_scope'] = 'required|in:department,office,all';
                $validationRules['target_department'] = 'nullable|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Secondary Education,Bachelor of Science in Hospitality Management';
                $validationRules['target_office'] = 'nullable|in:NSTP,SSC,GUIDANCE,REGISTRAR,CLINIC';
            } elseif ($admin->isDepartmentAdmin()) {
                $validationRules['visibility_scope'] = 'required|in:department,all';
            } elseif ($admin->isOfficeAdmin()) {
                $validationRules['visibility_scope'] = 'required|in:all';
            }

            // Validate the request
            \Log::info('Validating announcement request with rules:', $validationRules);
            
            $validator = \Validator::make($request->all(), $validationRules);
            
            if ($validator->fails()) {
                \Log::error('Announcement validation failed:', [
                    'errors' => $validator->errors()->toArray(),
                    'request_data' => $request->all()
                ]);
                
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            \Log::info('Announcement validation passed successfully');

            // Initialize file path variables
            $imagePath = null;
            $videoPath = null;
            $csvPath = null;
            $imagePaths = [];
            $videoPaths = [];

            // Handle multiple images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePaths[] = $image->store('announcement-images', 'public');
                }
            }

            // Handle multiple videos
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $videoPaths[] = $video->store('announcement-videos', 'public');
                }
            }

            // Handle single image (backward compatibility)
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('announcement-images', 'public');
            }

            // Handle single video (backward compatibility)
            if ($request->hasFile('video')) {
                $videoPath = $request->file('video')->store('announcement-videos', 'public');
            }

            if ($request->hasFile('csv_file')) {
                $csvPath = $request->file('csv_file')->store('announcement-csv', 'public');
            }

            // Determine if announcement should be published
            // Either checkbox is checked OR "Save & Publish" button was clicked
            $isPublished = $request->has('is_published') || $request->input('action') === 'save_and_publish';

            // Handle visibility logic for different admin types
            $targetDepartment = null;
            $targetOffice = null;

            if ($admin->isOfficeAdmin()) {
                // Office admins: always visible to all users
                $visibilityScope = 'all';
                // target_office remains null for 'all' visibility
                $targetOffice = null;
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

            \Log::info('Creating announcement with data:', [
                'title' => $request->input('title'),
                'content_length' => strlen($request->input('content')),
                'expires_at' => $request->input('expires_at'),
                'image_path' => $imagePath,
                'video_path' => $videoPath,
                'csv_path' => $csvPath,
                'image_paths' => $imagePaths,
                'video_paths' => $videoPaths,
                'is_published' => $isPublished,
                'visibility_scope' => $visibilityScope,
                'target_department' => $targetDepartment,
                'target_office' => $targetOffice,
                'admin_id' => Auth::guard('admin')->id(),
            ]);

            Announcement::create([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'expires_at' => $request->input('expires_at'),
                'image_path' => $imagePath,
                'video_path' => $videoPath,
                'csv_path' => $csvPath,
                'image_paths' => !empty($imagePaths) ? $imagePaths : null,
                'video_paths' => !empty($videoPaths) ? $videoPaths : null,
                'is_published' => $isPublished,
                'visibility_scope' => $visibilityScope,
                'target_department' => $targetDepartment,
                'target_office' => $targetOffice,
                'admin_id' => Auth::guard('admin')->id(),
                'created_at' => now(), // Explicitly set to current timestamp
                'updated_at' => now(), // Explicitly set to current timestamp
            ]);

            \Log::info('Announcement created successfully');

            $admin = Auth::guard('admin')->user();

            if ($admin->isDepartmentAdmin()) {
                return redirect()->route('department-admin.announcements.index')
                    ->with('success', 'Announcement created successfully!');
            }

            if ($admin->isOfficeAdmin()) {
                return redirect()->route('office-admin.announcements.index')
                    ->with('success', 'Announcement created successfully!');
            }

            if ($admin->isSuperAdmin()) {
                return redirect()->route('superadmin.announcements.index')
                    ->with('success', 'Announcement created successfully!');
            }

            return redirect()->route('announcements.index')
                ->with('success', 'Announcement created successfully!');

        } catch (\Exception $e) {
            \Log::error('Error creating announcement:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $admin = Auth::guard('admin')->user();
            
            if ($admin->isDepartmentAdmin()) {
                return redirect()->route('department-admin.announcements.create')
                    ->with('error', 'Error creating announcement. Please try again.')
                    ->withInput();
            }

            if ($admin->isOfficeAdmin()) {
                return redirect()->route('office-admin.announcements.create')
                    ->with('error', 'Error creating announcement. Please try again.')
                    ->withInput();
            }

            if ($admin->isSuperAdmin()) {
                return redirect()->route('superadmin.announcements.create')
                    ->with('error', 'Error creating announcement. Please try again.')
                    ->withInput();
            }

            return redirect()->route('announcements.create')
                ->with('error', 'Error creating announcement. Please try again.')
                ->withInput();
        }
    }

    public function show($id)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $announcement = Announcement::findOrFail($id);

        // Check if the admin has permission to view this announcement
        if ($admin->isDepartmentAdmin() && $announcement->admin_id !== $admin->id) {
            abort(403, 'Unauthorized action.');
        }

        // Office admins can view announcements targeted to their office OR announcements they created
        if ($admin->isOfficeAdmin()) {
            $canViewByOffice = $announcement->target_office === $admin->office;
            $canViewByOwnership = $announcement->admin_id === $admin->id;

            if (!$canViewByOffice && !$canViewByOwnership) {
                abort(403, 'Unauthorized access to this announcement.');
            }
        }

        if ($admin->isDepartmentAdmin()) {
            return view('department-admin.announcements.show', compact('announcement'));
        }

        if ($admin->isOfficeAdmin()) {
            $office = $admin->office;
            return view('office-admin.announcements.show', compact('announcement', 'office'));
        }

        if ($admin->isSuperAdmin()) {
            return view('superadmin.announcements.show', compact('announcement'));
        }

        return view('admin.announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Department admins can only edit their own announcements
        if ($admin->isDepartmentAdmin() && $announcement->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this announcement.');
        }

        // Office admins can only edit announcements they created OR announcements targeted to their office
        if ($admin->isOfficeAdmin()) {
            $canEditByOffice = $announcement->target_office === $admin->office;
            $canEditByOwnership = $announcement->admin_id === $admin->id;

            if (!$canEditByOffice && !$canEditByOwnership) {
                abort(403, 'Unauthorized access to this announcement.');
            }
        }

        if ($admin->isDepartmentAdmin()) {
            return view('department-admin.announcements.edit', compact('announcement'));
        }

        if ($admin->isOfficeAdmin()) {
            $office = $admin->office;
            return view('office-admin.announcements.edit', compact('announcement', 'office'));
        }

        if ($admin->isSuperAdmin()) {
            return view('superadmin.announcements.edit', compact('announcement'));
        }

        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $validationRules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'expires_at' => 'nullable|date|after:now',
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

        // Add visibility scope validation for superadmin
        if ($admin->isSuperAdmin()) {
            $validationRules['visibility_scope'] = 'required|in:department,office,all';
            $validationRules['target_department'] = 'nullable|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Secondary Education,Bachelor of Science in Hospitality Management';
            $validationRules['target_office'] = 'nullable|in:NSTP,SSC,GUIDANCE,REGISTRAR,CLINIC';
        }

        $request->validate($validationRules);

        // Handle file uploads
        // Determine if announcement should be published
        // Either checkbox is checked OR "Save & Publish" button was clicked
        $isPublished = $request->has('is_published') || $request->input('action') === 'save_and_publish';

        $updateData = [
            'title' => $request->title,
            'content' => $request->content,
            'expires_at' => $request->expires_at,
            'is_published' => $isPublished,
            'updated_at' => now(), // Explicitly set to current timestamp
        ];

        // Handle visibility scope and targets based on admin type
        if ($admin->isOfficeAdmin()) {
            // Office admins: announcements are always office-targeted
            $visibilityScope = 'office';
            $targetOffice = $admin->office;

            $updateData['visibility_scope'] = $visibilityScope;
            $updateData['target_department'] = null;
            $updateData['target_office'] = $targetOffice;
        } elseif ($admin->isDepartmentAdmin()) {
            $visibilityScope = $request->input('visibility_scope', 'department');
            $targetDepartment = null;

            if ($visibilityScope === 'department') {
                $targetDepartment = $admin->department;
            }

            $updateData['visibility_scope'] = $visibilityScope;
            $updateData['target_department'] = $targetDepartment;
            $updateData['target_office'] = null;
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
        }

        // Handle legacy media removal
        if ($request->input('remove_legacy_image') == '1') {
            if ($announcement->image_path && Storage::disk('public')->exists($announcement->image_path)) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $updateData['image_path'] = null;
        }

        $removeLegacyVideo = $request->boolean('remove_legacy_video') || $request->boolean('remove_video');
        if ($removeLegacyVideo) {
            if ($announcement->video_path && Storage::disk('public')->exists($announcement->video_path)) {
                Storage::disk('public')->delete($announcement->video_path);
            }
            $updateData['video_path'] = null;
        }

        // Handle existing multiple images removal
        $currentImagePaths = $announcement->image_paths ? (is_string($announcement->image_paths) ? json_decode($announcement->image_paths, true) : $announcement->image_paths) : [];
        $removeImages = $request->input('remove_images');
        
        // Debug logging
        \Log::info('Media Removal Debug - Images:', [
            'current_image_paths' => $currentImagePaths,
            'remove_images_input' => $removeImages,
            'announcement_id' => $announcement->id
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

        // Handle existing multiple videos removal
        $currentVideoPaths = $announcement->video_paths ? (is_string($announcement->video_paths) ? json_decode($announcement->video_paths, true) : $announcement->video_paths) : [];
        $removeVideos = $request->input('remove_videos');
        $removedAnyVideoFlag = false;
        
        // Debug logging for videos
        \Log::info('Media Removal Debug - Videos:', [
            'current_video_paths' => $currentVideoPaths,
            'remove_videos_input' => $removeVideos,
            'announcement_id' => $announcement->id
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
                $currentImagePaths[] = $image->store('announcement-images', 'public');
            }
        }

        // Handle new videos upload with replacement semantics (max 1)
        if ($request->hasFile('videos')) {
            // Always replace legacy single video if present
            if ($announcement->video_path) {
                if (Storage::disk('public')->exists($announcement->video_path)) {
                    Storage::disk('public')->delete($announcement->video_path);
                }
                $updateData['video_path'] = null;
            }

            // Enforce max 1 video overall: remove existing multi videos
            if (!empty($currentVideoPaths)) {
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
                $stored = $firstVideo->store('announcement-videos', 'public');
                $currentVideoPaths[] = $stored;
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
            $updateData['video_path'] = $updateData['video_path'] ?? null;
        }

        // Debug final update data
        \Log::info('Final update data for media:', [
            'image_paths' => $updateData['image_paths'],
            'video_paths' => $updateData['video_paths'],
            'announcement_id' => $announcement->id
        ]);

        $announcement->update($updateData);
        
        // Force refresh the model to ensure we have the latest data
        $announcement->refresh();
        
        // Log the actual database state after update
        \Log::info('Database state after update:', [
            'db_image_paths' => $announcement->image_paths,
            'db_video_paths' => $announcement->video_paths,
            'hasMedia_attribute' => $announcement->hasMedia,
            'announcement_id' => $announcement->id
        ]);

        $admin = Auth::guard('admin')->user();

        if ($admin->isDepartmentAdmin()) {
            return redirect()->route('department-admin.announcements.index')
                ->with('success', 'Announcement updated successfully!');
        }

        if ($admin->isOfficeAdmin()) {
            return redirect()->route('office-admin.announcements.index')
                ->with('success', 'Announcement updated successfully!');
        }

        if ($admin->isSuperAdmin()) {
            return redirect()->route('superadmin.announcements.index')
                ->with('success', 'Announcement updated successfully!');
        }

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    public function destroy(Announcement $announcement)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Department admins can only delete their own announcements
        if ($admin->isDepartmentAdmin() && $announcement->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this announcement.');
        }

        // Office admins can only delete announcements they created OR announcements targeted to their office
        if ($admin->isOfficeAdmin()) {
            $canDeleteByOffice = $announcement->target_office === $admin->office;
            $canDeleteByOwnership = $announcement->admin_id === $admin->id;

            if (!$canDeleteByOffice && !$canDeleteByOwnership) {
                abort(403, 'Unauthorized access to this announcement.');
            }
        }

        // Delete associated files
        if ($announcement->image_path && \Storage::disk('public')->exists($announcement->image_path)) {
            \Storage::disk('public')->delete($announcement->image_path);
        }

        if ($announcement->video_path && \Storage::disk('public')->exists($announcement->video_path)) {
            \Storage::disk('public')->delete($announcement->video_path);
        }

        if ($announcement->csv_path && \Storage::disk('public')->exists($announcement->csv_path)) {
            \Storage::disk('public')->delete($announcement->csv_path);
        }

        $announcement->delete();

        if ($admin->isDepartmentAdmin()) {
            return redirect()->route('department-admin.announcements.index')
                ->with('success', 'Announcement deleted successfully!');
        }

        if ($admin->isOfficeAdmin()) {
            return redirect()->route('office-admin.announcements.index')
                ->with('success', 'Announcement deleted successfully!');
        }

        if ($admin->isSuperAdmin()) {
            return redirect()->route('superadmin.announcements.index')
                ->with('success', 'Announcement deleted successfully!');
        }

        return redirect()->route('announcements.index')
            ->with('success', 'Announcement deleted successfully!');
    }

    // Modal-specific methods for AJAX loading
    public function showModal(Announcement $announcement)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check permissions
        if ($admin->isDepartmentAdmin() && $announcement->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this announcement.');
        }

        if ($admin->isOfficeAdmin()) {
            $canViewByOffice = $announcement->target_office === $admin->office;
            $canViewByOwnership = $announcement->admin_id === $admin->id;

            if (!$canViewByOffice && !$canViewByOwnership) {
                abort(403, 'Unauthorized access to this announcement.');
            }
        }

        // Determine the correct view based on admin type
        if ($admin->isDepartmentAdmin()) {
            return view('department-admin.announcements.modal-show', compact('announcement'));
        } elseif ($admin->isOfficeAdmin()) {
            return view('office-admin.announcements.modal-show', compact('announcement'));
        } elseif ($admin->isSuperAdmin()) {
            return view('superadmin.announcements.modal-show', compact('announcement'));
        }

        return view('admin.announcements.modal-show', compact('announcement'));
    }

    public function editModal(Announcement $announcement)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check permissions
        if ($admin->isDepartmentAdmin() && $announcement->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this announcement.');
        }

        if ($admin->isOfficeAdmin()) {
            $canEditByOffice = $announcement->target_office === $admin->office;
            $canEditByOwnership = $announcement->admin_id === $admin->id;

            if (!$canEditByOffice && !$canEditByOwnership) {
                abort(403, 'Unauthorized access to this announcement.');
            }
        }

        if ($admin->isDepartmentAdmin()) {
            return view('department-admin.announcements.modal-edit', compact('announcement'));
        }

        if ($admin->isOfficeAdmin()) {
            $office = $admin->office;
            return view('office-admin.announcements.modal-edit', compact('announcement', 'office'));
        }

        if ($admin->isSuperAdmin()) {
            return view('superadmin.announcements.modal-edit', compact('announcement'));
        }

        return view('admin.announcements.modal-edit', compact('announcement'));
    }
}
