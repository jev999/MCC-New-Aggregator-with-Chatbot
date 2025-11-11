<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Department admins can only see their own news
        if ($admin->isDepartmentAdmin()) {
            $news = News::with('admin')
                        ->where('admin_id', $admin->id)
                        ->latest()
                        ->get();
            return view('department-admin.news.index', compact('news'));
        }

        // Office admins can see news they created (both office-specific and all-departments)
        if ($admin->isOfficeAdmin()) {
            $news = News::with('admin')
                        ->where('admin_id', $admin->id)
                        ->latest()
                        ->get();
            $office = $admin->office;
            return view('office-admin.news.index', compact('news', 'office'));
        }

        // Super admins and regular admins can see all news
        $news = News::with('admin')->latest()->get();

        // Determine view based on admin type
        if ($admin->isSuperAdmin()) {
            return view('superadmin.news.index', compact('news'));
        }

        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        if ($admin->isDepartmentAdmin()) {
            return view('department-admin.news.create');
        }

        if ($admin->isOfficeAdmin()) {
            $office = $admin->office;
            return view('office-admin.news.create', compact('office'));
        }

        if ($admin->isSuperAdmin()) {
            return view('superadmin.news.create');
        }

        return view('admin.news.create');
    }

    public function store(Request $request)
    {
        // Debug logging
        \Log::info('News creation started', [
            'title' => $request->title,
            'has_image' => $request->hasFile('image'),
            'has_video' => $request->hasFile('video'),
            'has_csv' => $request->hasFile('csv_file'),
            'admin_id' => Auth::guard('admin')->id()
        ]);

        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Different validation rules based on admin type
        $validationRules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'images' => 'nullable|array|max:2',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'videos' => 'nullable|array|max:3',
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
                try {
                    $imagePaths[] = $image->store('news-images', 'public');
                    \Log::info('Multiple image uploaded successfully', ['path' => end($imagePaths)]);
                } catch (\Exception $e) {
                    \Log::error('Multiple image upload failed', ['error' => $e->getMessage()]);
                }
            }
        }

        // Handle multiple videos
        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $video) {
                try {
                    $videoPaths[] = $video->store('news-videos', 'public');
                    \Log::info('Multiple video uploaded successfully', ['path' => end($videoPaths)]);
                } catch (\Exception $e) {
                    \Log::error('Multiple video upload failed', ['error' => $e->getMessage()]);
                }
            }
        }

        // Handle single image (backward compatibility)
        if ($request->hasFile('image')) {
            try {
                $imagePath = $request->file('image')->store('news-images', 'public');
                \Log::info('Image uploaded successfully', ['path' => $imagePath]);
            } catch (\Exception $e) {
                \Log::error('Image upload failed', ['error' => $e->getMessage()]);
            }
        }

        // Handle single video (backward compatibility)
        if ($request->hasFile('video')) {
            try {
                $videoPath = $request->file('video')->store('news-videos', 'public');
                \Log::info('Video uploaded successfully', ['path' => $videoPath]);
            } catch (\Exception $e) {
                \Log::error('Video upload failed', ['error' => $e->getMessage()]);
            }
        }

        if ($request->hasFile('csv_file')) {
            try {
                $csvPath = $request->file('csv_file')->store('news-csv', 'public');
                \Log::info('CSV uploaded successfully', ['path' => $csvPath]);
            } catch (\Exception $e) {
                \Log::error('CSV upload failed', ['error' => $e->getMessage()]);
            }
        }

        // Handle department and office visibility logic based on admin type
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

        $news = News::create([
            'title' => $request->title,
            'content' => $request->content,
            'image_path' => $imagePath,
            'video_path' => $videoPath,
            'csv_path' => $csvPath,
            'image_paths' => !empty($imagePaths) ? $imagePaths : null,
            'video_paths' => !empty($videoPaths) ? $videoPaths : null,
            'is_published' => $request->has('is_published'),
            'visibility_scope' => $visibilityScope,
            'target_department' => $targetDepartment,
            'target_office' => $targetOffice,
            'admin_id' => Auth::guard('admin')->id(),
        ]);

        \Log::info('News created successfully', [
            'news_id' => $news->id,
            'image_path' => $imagePath,
            'video_path' => $videoPath,
            'csv_path' => $csvPath
        ]);

        if ($admin->isDepartmentAdmin()) {
            return redirect()->route('department-admin.news.index')
                ->with('success', 'News created successfully!');
        }

        if ($admin->isOfficeAdmin()) {
            return redirect()->route('office-admin.news.index')
                ->with('success', 'News created successfully!');
        }

        if ($admin->isSuperAdmin()) {
            return redirect()->route('superadmin.news.index')
                ->with('success', 'News created successfully!');
        }

        return redirect()->route('news.index')
            ->with('success', 'News created successfully!');
    }

    public function show(News $news)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Check if admin can view this news
        if ($admin->isDepartmentAdmin() && $news->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to news article.');
        }

        if ($admin->isOfficeAdmin() && $news->target_office !== $admin->office) {
            abort(403, 'Unauthorized access to news article.');
        }

        if ($admin->isDepartmentAdmin()) {
            return view('department-admin.news.show', compact('news'));
        }

        if ($admin->isOfficeAdmin()) {
            return view('office-admin.news.show', compact('news'));
        }

        if ($admin->isSuperAdmin()) {
            return view('superadmin.news.show', compact('news'));
        }

        return view('admin.news.show', compact('news'));
    }

    public function showData(News $news)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if admin can view this news
        if ($admin->isDepartmentAdmin() && $news->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to news article.');
        }

        if ($admin->isOfficeAdmin() && $news->target_office !== $admin->office) {
            abort(403, 'Unauthorized access to news article.');
        }

        return response()->json([
            'id' => $news->id,
            'title' => $news->title,
            'content' => nl2br(e($news->content)),
            'is_published' => $news->is_published,
            'created_at' => $news->created_at->format('F d, Y \a\t g:i A'),
            'author' => $news->admin->username,
            'role' => ucfirst(str_replace('_', ' ', $news->admin->role)),
            'department' => $news->admin->department,
            'has_media' => $news->hasMedia,
            'images' => $news->allImageUrls ?? [],
            'videos' => $news->allVideoUrls ?? []
        ]);
    }

    public function edit(News $news)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Department admins can only edit their own news
        if ($admin->isDepartmentAdmin() && $news->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this news.');
        }

        // For AJAX requests, return just the form content
        if (request()->ajax() || request()->wantsJson()) {
            if ($admin->isSuperAdmin()) {
                return view('superadmin.news.edit-modal', compact('news'));
            }
            // Add other admin types as needed
            return view('superadmin.news.edit-modal', compact('news'));
        }

        // For regular requests, return full page
        if ($admin->isDepartmentAdmin()) {
            return view('department-admin.news.edit', compact('news'));
        }

        if ($admin->isOfficeAdmin()) {
            $office = $admin->office;
            return view('office-admin.news.edit', compact('news', 'office'));
        }

        if ($admin->isSuperAdmin()) {
            return view('superadmin.news.edit', compact('news'));
        }

        return view('admin.news.edit', compact('news'));
    }

    public function update(Request $request, News $news)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Department admins can only update their own news
        if ($admin->isDepartmentAdmin() && $news->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this news.');
        }

        $validationRules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'images' => 'nullable|array|max:2',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'videos' => 'nullable|array|max:1',
            'videos.*' => 'mimes:mp4|max:51200',
            'is_published' => 'boolean',
            // Media removal parameters
            'remove_legacy_image' => 'nullable|boolean',
            'remove_legacy_video' => 'nullable|boolean',
            'remove_images' => 'nullable|array', // Array of indexes
            'remove_videos' => 'nullable|array', // Array of indexes
        ];

        // Add visibility scope validation for superadmin
        if ($admin->isSuperAdmin()) {
            $validationRules['visibility_scope'] = 'required|in:department,office,all';
            $validationRules['target_department'] = 'nullable|in:Bachelor of Science in Information Technology,Bachelor of Science in Business Administration,Bachelor of Elementary Education,Bachelor of Secondary Education,Bachelor of Science in Hospitality Management';
            $validationRules['target_office'] = 'nullable|in:NSTP,SSC,GUIDANCE,REGISTRAR,CLINIC';
        }

        $request->validate($validationRules);

        // Handle file uploads
        $updateData = [
            'title' => $request->title,
            'content' => $request->content,
            'is_published' => $request->has('is_published'),
        ];

        // Handle visibility scope and targets based on admin type
        if ($admin->isOfficeAdmin()) {
            // Office admins: news are always office-targeted
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

        // Handle single image removal (legacy)
        if ($request->input('remove_legacy_image')) {
            \Log::info('Removing legacy single image: ' . $news->image_path);
            if ($news->image_path && Storage::disk('public')->exists($news->image_path)) {
                Storage::disk('public')->delete($news->image_path);
                \Log::info('Legacy image file deleted from storage: ' . $news->image_path);
            }
            $updateData['image_path'] = null;
        }

        // Handle existing multiple images removal
        $currentImagePaths = $news->image_paths ? (is_string($news->image_paths) ? json_decode($news->image_paths, true) : $news->image_paths) : [];
        $removeImages = $request->input('remove_images');

        // Debug logging
        \Log::info('Media Removal Debug - Images:', [
            'current_image_paths' => $currentImagePaths,
            'remove_images_input' => $removeImages,
            'remove_legacy_image' => $request->input('remove_legacy_image'),
            'news_id' => $news->id
        ]);

        if ($removeImages) {
            // Handle both array and string inputs
            $removeIndices = is_array($removeImages) ? $removeImages : explode(',', $removeImages);
            \Log::info('Remove image indices:', $removeIndices);

            foreach ($removeIndices as $index) {
                $index = trim($index); // Remove any whitespace
                if (isset($currentImagePaths[$index])) {
                    \Log::info('Removing image at index ' . $index . ': ' . $currentImagePaths[$index]);
                    if (Storage::disk('public')->exists($currentImagePaths[$index])) {
                        Storage::disk('public')->delete($currentImagePaths[$index]);
                        \Log::info('Image file deleted from storage: ' . $currentImagePaths[$index]);
                    }
                    unset($currentImagePaths[$index]);
                }
            }
            // Reindex array to remove gaps
            $currentImagePaths = array_values($currentImagePaths);
            \Log::info('Final image paths after removal:', $currentImagePaths);
        }

        // Handle single video removal (support legacy and fallback name)
        $removeLegacyVideo = $request->boolean('remove_legacy_video') || $request->boolean('remove_video');
        if ($removeLegacyVideo) {
            \Log::info('Removing legacy single video: ' . $news->video_path);
            if ($news->video_path && Storage::disk('public')->exists($news->video_path)) {
                Storage::disk('public')->delete($news->video_path);
                \Log::info('Legacy video file deleted from storage: ' . $news->video_path);
            }
            $updateData['video_path'] = null;
        }

        // Handle existing multiple videos removal
        $currentVideoPaths = $news->video_paths ? (is_string($news->video_paths) ? json_decode($news->video_paths, true) : $news->video_paths) : [];
        $removeVideos = $request->input('remove_videos');
        $removedAnyVideoFlag = false;

        // Debug logging for videos
        \Log::info('Media Removal Debug - Videos:', [
            'current_video_paths' => $currentVideoPaths,
            'remove_videos_input' => $removeVideos,
            'remove_legacy_video' => $request->input('remove_legacy_video'),
            'news_id' => $news->id
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
                $currentImagePaths[] = $image->store('news-images', 'public');
            }
        }

        // Handle new videos upload with replacement semantics (max 1)
        if ($request->hasFile('videos')) {
            // Always replace legacy single video if present
            if ($news->video_path) {
                \Log::info('Replacing legacy single video due to new upload: ' . $news->video_path);
                if (Storage::disk('public')->exists($news->video_path)) {
                    Storage::disk('public')->delete($news->video_path);
                    \Log::info('Legacy video file deleted for replacement: ' . $news->video_path);
                }
                $updateData['video_path'] = null;
            }

            // Enforce max 1 video overall: if any existing multi videos remain, delete them for replacement
            if (!empty($currentVideoPaths)) {
                \Log::info('Replacing existing multiple videos with new upload', ['existing' => $currentVideoPaths]);
                foreach ($currentVideoPaths as $existingPath) {
                    if ($existingPath && Storage::disk('public')->exists($existingPath)) {
                        Storage::disk('public')->delete($existingPath);
                    }
                }
                $currentVideoPaths = [];
            }

            // Store only the first uploaded video (since max is 1 in UI)
            $uploadedVideos = $request->file('videos');
            $firstVideo = is_array($uploadedVideos) ? $uploadedVideos[0] : $uploadedVideos;
            if ($firstVideo) {
                $stored = $firstVideo->store('news-videos', 'public');
                $currentVideoPaths[] = $stored;
                \Log::info('Stored replacement video', ['path' => $stored]);
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
            'news_id' => $news->id
        ]);

        $news->update($updateData);

        if ($admin->isDepartmentAdmin()) {
            return redirect()->route('department-admin.news.index')
                ->with('success', 'News updated successfully!');
        }

        if ($admin->isOfficeAdmin()) {
            return redirect()->route('office-admin.news.index')
                ->with('success', 'News updated successfully!');
        }

        if ($admin->isSuperAdmin()) {
            return redirect()->route('superadmin.news.index')
                ->with('success', 'News updated successfully!');
        }

        return redirect()->route('news.index')
            ->with('success', 'News updated successfully!');
    }

    public function destroy(News $news)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Department admins can only delete their own news
        if ($admin->isDepartmentAdmin() && $news->admin_id !== $admin->id) {
            abort(403, 'Unauthorized access to this news.');
        }

        // Delete associated files
        if ($news->image && Storage::disk('public')->exists($news->image)) {
            Storage::disk('public')->delete($news->image);
        }

        if ($news->video && Storage::disk('public')->exists($news->video)) {
            Storage::disk('public')->delete($news->video);
        }

        if ($news->csv_file && Storage::disk('public')->exists($news->csv_file)) {
            Storage::disk('public')->delete($news->csv_file);
        }

        $news->delete();

        if ($admin->isDepartmentAdmin()) {
            return redirect()->route('department-admin.news.index')
                ->with('success', 'News deleted successfully!');
        }

        if ($admin->isOfficeAdmin()) {
            return redirect()->route('office-admin.news.index')
                ->with('success', 'News deleted successfully!');
        }

        if ($admin->isSuperAdmin()) {
            return redirect()->route('superadmin.news.index')
                ->with('success', 'News deleted successfully!');
        }

        return redirect()->route('news.index')
            ->with('success', 'News deleted successfully!');
    }
}
