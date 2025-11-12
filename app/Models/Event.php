<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Traits\CreatesNotifications;

class Event extends Model
{
    use HasFactory, CreatesNotifications;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'event_time',
        'location',
        'admin_id',
        'image',
        'video',
        'csv_file',
        'image_paths',
        'video_paths',
        'is_published',
        'visibility_scope',
        'target_department',
        'target_office',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_published' => 'boolean',
        'image_paths' => 'array',
        'video_paths' => 'array',
    ];

    protected $appends = [
        'hasMedia',
        'mediaUrl',
        'thumbnailUrl',
        'eventStatus',
        'eventStatusText',
        'status',
        'category',
        'body',
        'publisherName',
        'publisherRole',
        'publisherInfo',
        'allImageUrls',
        'allVideoUrls',
        'allImagePaths',
        'allVideoPaths'
    ];

    /**
     * Build a publicly accessible URL for a file stored on the public disk.
     * Uses Laravel's Storage facade for proper URL generation.
     */
    private function buildPublicUrl(string $relativePath): string
    {
        // Absolute URL already
        if (preg_match('/^https?:\/\//i', $relativePath)) {
            return $relativePath;
        }

        // Remove any leading slashes
        $relativePath = ltrim($relativePath, '/');
        
        // Use Laravel's Storage facade to generate the proper URL
        // This handles the APP_URL and storage disk configuration properly
        return Storage::disk('public')->url($relativePath);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get all comments for this event.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id')->with('user', 'replies')->orderBy('created_at', 'asc');
    }

    /**
     * Get the real-time formatted created time for display
     */
    public function getCreatedTimeAttribute()
    {
        return $this->created_at->setTimezone(config('app.timezone', 'UTC'))->format('H:i');
    }

    /**
     * Check if event has media (image or video)
     */
    public function getHasMediaAttribute()
    {
        // Handle single media files
        $hasSingleImage = !empty($this->image);
        $hasSingleVideo = !empty($this->video);
        
        // Handle multiple media files - handle double JSON encoding
        $imagePaths = $this->image_paths;
        if (is_string($imagePaths)) {
            $decoded = json_decode($imagePaths, true);
            if (is_string($decoded)) {
                $imagePaths = json_decode($decoded, true);
            } else {
                $imagePaths = $decoded;
            }
        }
        $hasMultipleImages = !empty($imagePaths) && is_array($imagePaths) && count($imagePaths) > 0;
        
        $videoPaths = $this->video_paths;
        if (is_string($videoPaths)) {
            $decoded = json_decode($videoPaths, true);
            if (is_string($decoded)) {
                $videoPaths = json_decode($decoded, true);
            } else {
                $videoPaths = $decoded;
            }
        }
        $hasMultipleVideos = !empty($videoPaths) && is_array($videoPaths) && count($videoPaths) > 0;
        
        $hasImages = $hasSingleImage || $hasMultipleImages;
        $hasVideos = $hasSingleVideo || $hasMultipleVideos;
        
        if ($hasImages && $hasVideos) {
            return 'both';
        } elseif ($hasImages) {
            return 'image';
        } elseif ($hasVideos) {
            return 'video';
        }
        return 'none';
    }

    /**
     * Get the media URL for display (prioritizes first image from multiple uploads)
     */
    public function getMediaUrlAttribute()
    {
        // First check for multiple images
        $allImageUrls = $this->getAllImageUrlsAttribute();
        if (!empty($allImageUrls)) {
            return $allImageUrls[0]; // Return first image
        }
        
        // Then check for multiple videos
        $allVideoUrls = $this->getAllVideoUrlsAttribute();
        if (!empty($allVideoUrls)) {
            return $allVideoUrls[0]; // Return first video
        }
        
        return null;
    }

    /**
     * Get thumbnail URL for videos (prioritizes first image from multiple uploads)
     */
    public function getThumbnailUrlAttribute()
    {
        // First check for multiple videos - use placeholder
        $allVideoUrls = $this->getAllVideoUrlsAttribute();
        if (!empty($allVideoUrls)) {
            return asset('images/video-placeholder.jpg');
        }
        
        // Then check for multiple images - use first image
        $allImageUrls = $this->getAllImageUrlsAttribute();
        if (!empty($allImageUrls)) {
            return $allImageUrls[0];
        }
        
        return null;
    }

    /**
     * Get the status for display (draft/published)
     */
    public function getStatusAttribute()
    {
        return $this->is_published ? 'published' : 'draft';
    }

    /**
     * Get the category for this content type
     */
    public function getCategoryAttribute()
    {
        return 'event';
    }

    /**
     * Get the description/body content
     */
    public function getBodyAttribute()
    {
        return $this->description;
    }

    /**
     * Get the status of the event based on its date
     */
    public function getEventStatus()
    {
        if (!$this->event_date) {
            return [
                'status' => 'tbd',
                'text' => 'TBD',
                'icon' => 'calendar-question'
            ];
        }

        $now = Carbon::now();
        $eventStart = Carbon::parse($this->event_date);
        $eventEnd = $eventStart->copy()->addHours(24); // Event is considered ongoing for 24 hours

        if ($now < $eventStart) {
            return [
                'status' => 'upcoming',
                'text' => 'Upcoming',
                'icon' => 'calendar-plus'
            ];
        } elseif ($now >= $eventStart && $now <= $eventEnd) {
            return [
                'status' => 'ongoing',
                'text' => 'Ongoing',
                'icon' => 'calendar-day'
            ];
        } else {
            return [
                'status' => 'past',
                'text' => 'Past',
                'icon' => 'calendar-check'
            ];
        }
    }

    /**
     * Get the event status attribute for Laravel's appends functionality
     */
    public function getEventStatusAttribute()
    {
        $status = $this->getEventStatus();
        return $status['status'] ?? 'unknown';
    }

    /**
     * Get the event status text for display
     */
    public function getEventStatusTextAttribute()
    {
        $status = $this->getEventStatus();
        return $status['text'] ?? 'Unknown';
    }

    /**
     * Normalize department name to abbreviated form
     */
    private function normalizeDepartmentName($department)
    {
        if (!$department) return null;
        
        $departmentMap = [
            'Bachelor of Science in Information Technology' => 'BSIT',
            'Bachelor of Science in Business Administration' => 'BSBA',
            'Bachelor of Elementary Education' => 'EDUC',
            'Bachelor of Science in Hospitality Management' => 'BSHM',
            'Bachelor of Secondary Education' => 'BSED',
        ];
        
        return $departmentMap[$department] ?? $department;
    }

    /**
     * Check if event is visible to a specific department
     */
    public function isVisibleToDepartment($department)
    {
        if ($this->visibility_scope === 'all') {
            return true;
        }

        $normalizedUserDept = $this->normalizeDepartmentName($department);
        $normalizedTargetDept = $this->normalizeDepartmentName($this->target_department);
        
        return $this->visibility_scope === 'department' && $normalizedTargetDept === $normalizedUserDept;
    }

    /**
     * Determine if this event is visible to a specific user.
     */
    public function isVisibleToUser($user): bool
    {
        // Always visible if marked for all or legacy null/empty
        if ($this->visibility_scope === 'all' || $this->visibility_scope === null || $this->visibility_scope === '') {
            return true;
        }

        if (!$user) {
            return false;
        }

        // Department-targeted visibility
        if ($this->visibility_scope === 'department') {
            $normalizedUserDept = $this->normalizeDepartmentName($user->department);
            $normalizedTargetDept = $this->normalizeDepartmentName($this->target_department);
            return $user->department && $normalizedTargetDept === $normalizedUserDept;
        }

        // Office-targeted visibility
        if ($this->visibility_scope === 'office') {
            if ($this->target_office === 'NSTP') {
                // NSTP events are visible to 1st-year students in allowed departments
                $allowedDepartments = ['BSIT', 'BSBA', 'BEED', 'BSHM', 'BSED'];
                $allowedFullNames = [
                    'Bachelor of Science in Information Technology',
                    'Bachelor of Science in Business Administration',
                    'Bachelor of Elementary Education',
                    'Bachelor of Science in Hospitality Management',
                    'Bachelor of Secondary Education'
                ];
                $isFirstYear = in_array(strtolower($user->year_level ?? ''), ['1st year', '1st-year', 'first year']);
                $isInAllowedDept = $user->department && (
                    in_array($user->department, $allowedDepartments, true) ||
                    in_array($user->department, $allowedFullNames, true)
                );
                return ($user->role === 'student') && $isInAllowedDept && $isFirstYear;
            }

            if ($this->target_office === 'GUIDANCE') {
                // GUIDANCE events are visible to all students
                return $user->role === 'student';
            }

            if ($this->target_office === 'SSC') {
                // SSC events are visible to all students
                return $user->role === 'student';
            }

            if ($this->target_office === 'REGISTRAR') {
                // REGISTRAR events are visible to all students
                return $user->role === 'student';
            }

            if ($this->target_office === 'CLINIC') {
                // CLINIC events are visible to all students
                return $user->role === 'student';
            }

            // Other offices are not visible to students by default
            return false;
        }

        return false;
    }

    /**
     * Scope to get events visible to a specific department
     */
    public function scopeVisibleToDepartment($query, $department)
    {
        return $query->where(function($q) use ($department) {
            // Show content marked for all departments or null/empty visibility_scope (backward compatibility)
            $q->where('visibility_scope', 'all')
              ->orWhereNull('visibility_scope')
              ->orWhere('visibility_scope', '')
              // Show content specifically targeted to this department
              ->orWhere(function($subQ) use ($department) {
                  $subQ->where('visibility_scope', 'department')
                       ->where('target_department', $department);
              });
        });
    }

    /**
     * Scope to get events visible to a specific user (department + NSTP 1st-year rule)
     */
    public function scopeVisibleToUser($query, $user)
    {
        $allowedDepartments = ['BSIT', 'BSBA', 'BEED', 'BSHM', 'BSED'];
        $allowedFullNames = [
            'Bachelor of Science in Information Technology',
            'Bachelor of Science in Business Administration',
            'Bachelor of Elementary Education',
            'Bachelor of Science in Hospitality Management',
            'Bachelor of Secondary Education'
        ];

        return $query->where(function ($q) use ($user, $allowedDepartments, $allowedFullNames) {
            // Visible to all (including legacy null/empty)
            $q->where('visibility_scope', 'all')
              ->orWhereNull('visibility_scope')
              ->orWhere('visibility_scope', '');

            if ($user && $user->department) {
                // Department-targeted content for this user
                $normalizedUserDept = $this->normalizeDepartmentName($user->department);
                $fullNameMap = [
                    'BSIT' => 'Bachelor of Science in Information Technology',
                    'BSBA' => 'Bachelor of Science in Business Administration', 
                    'BEED' => 'Bachelor of Elementary Education',
                    'BSHM' => 'Bachelor of Science in Hospitality Management',
                    'BSED' => 'Bachelor of Secondary Education',
                ];
                
                $q->orWhere(function ($subQ) use ($normalizedUserDept, $fullNameMap) {
                    $subQ->where('visibility_scope', 'department')
                         ->where(function($deptQ) use ($normalizedUserDept, $fullNameMap) {
                             // Match abbreviated name
                             $deptQ->where('target_department', $normalizedUserDept);
                             // Also match full name if exists
                             if (isset($fullNameMap[$normalizedUserDept])) {
                                 $deptQ->orWhere('target_department', $fullNameMap[$normalizedUserDept]);
                             }
                         });
                });
            }

            // Office-targeted content for students
            if ($user && $user->role === 'student') {
                // NSTP office content for 1st-year students in allowed departments
                $isFirstYear = in_array(strtolower($user->year_level ?? ''), ['1st year', '1st-year', 'first year']);
                $isInAllowedDept = $user->department && (
                    in_array($user->department, $allowedDepartments, true) ||
                    in_array($user->department, $allowedFullNames, true)
                );
                if ($isInAllowedDept && $isFirstYear) {
                    $q->orWhere(function ($subQ) {
                        $subQ->where('visibility_scope', 'office')
                             ->where('target_office', 'NSTP');
                    });
                }

                // Other office content visible to all students
                $q->orWhere(function ($subQ) {
                    $subQ->where('visibility_scope', 'office')
                         ->whereIn('target_office', ['GUIDANCE', 'SSC', 'REGISTRAR', 'CLINIC']);
                });
            }
        });
    }

    /**
     * Get the publisher's name
     */
    public function getPublisherNameAttribute()
    {
        return $this->admin ? $this->admin->username : 'Unknown';
    }

    /**
     * Get the publisher's role display
     */
    public function getPublisherRoleAttribute()
    {
        return $this->admin ? $this->admin->role_display : 'Unknown';
    }

    /**
     * Get comprehensive publisher information
     */
    public function getPublisherInfoAttribute()
    {
        if (!$this->admin) {
            return 'Published by Unknown';
        }

        // For department admins posting to all departments, show department attribution
        if ($this->admin->role === 'department_admin' && $this->admin->department && $this->visibility_scope === 'all') {
            return 'Posted by ' . $this->admin->department_display;
        }

        $info = 'Published by ' . $this->admin->username;
        
        if ($this->admin->role === 'department_admin' && $this->admin->department) {
            $info .= ' (' . $this->admin->department_display . ')';
        } elseif ($this->admin->role === 'office_admin' && $this->admin->office) {
            $info .= ' (' . $this->admin->office_display . ')';
        } elseif ($this->admin->role === 'superadmin') {
            $info .= ' (Super Administrator)';
        } else {
            $info .= ' (' . $this->admin->role_display . ')';
        }
        
        return $info;
    }

    /**
     * Get all image URLs (single + multiple) - returns full URLs for display
     */
    public function getAllImageUrlsAttribute()
    {
        $paths = [];
        
        // Add single image if exists
        if ($this->image) {
            $paths[] = $this->image;
        }
        
        // Add multiple images if exist - handle double JSON encoding
        $imagePaths = $this->image_paths;
        
        // Handle double JSON encoding issue
        if (is_string($imagePaths)) {
            // First decode
            $decoded = json_decode($imagePaths, true);
            
            // If it's still a string, decode again (double encoded)
            if (is_string($decoded)) {
                $imagePaths = json_decode($decoded, true);
            } else {
                $imagePaths = $decoded;
            }
        }
        
        if (!empty($imagePaths) && is_array($imagePaths)) {
            foreach ($imagePaths as $path) {
                if (!empty($path)) {
                    $paths[] = $path;
                }
            }
        }
        
        // Convert file paths to full URLs using Storage facade for production compatibility
        return array_map(function($path) {
            return $this->buildPublicUrl($path);
        }, $paths);
    }

    /**
     * Get all video URLs (single + multiple) - returns full URLs for display
     */
    public function getAllVideoUrlsAttribute()
    {
        $paths = [];
        
        // Add single video if exists
        if ($this->video) {
            $paths[] = $this->video;
        }
        
        // Add multiple videos if exist - handle double JSON encoding
        $videoPaths = $this->video_paths;
        
        // Handle double JSON encoding issue
        if (is_string($videoPaths)) {
            // First decode
            $decoded = json_decode($videoPaths, true);
            
            // If it's still a string, decode again (double encoded)
            if (is_string($decoded)) {
                $videoPaths = json_decode($decoded, true);
            } else {
                $videoPaths = $decoded;
            }
        }
        
        if (!empty($videoPaths) && is_array($videoPaths)) {
            foreach ($videoPaths as $path) {
                if (!empty($path)) {
                    $paths[] = $path;
                }
            }
        }
        
        // Convert file paths to full URLs using Storage facade for production compatibility
        return array_map(function($path) {
            return $this->buildPublicUrl($path);
        }, $paths);
    }

    /**
     * Get all image paths (single + multiple) - raw paths for editing
     */
    public function getAllImagePathsAttribute()
    {
        $paths = [];
        
        // Add single image if exists
        if ($this->image) {
            $paths[] = $this->image;
        }
        
        // Add multiple images if exist - handle double JSON encoding
        $imagePaths = $this->image_paths;
        
        // Handle double JSON encoding issue
        if (is_string($imagePaths)) {
            // First decode
            $decoded = json_decode($imagePaths, true);
            
            // If it's still a string, decode again (double encoded)
            if (is_string($decoded)) {
                $imagePaths = json_decode($decoded, true);
            } else {
                $imagePaths = $decoded;
            }
        }
        
        if (!empty($imagePaths) && is_array($imagePaths)) {
            foreach ($imagePaths as $path) {
                if (!empty($path)) {
                    $paths[] = $path;
                }
            }
        }
        
        return $paths;
    }

    /**
     * Get all video paths (single + multiple) - raw paths for editing
     */
    public function getAllVideoPathsAttribute()
    {
        $paths = [];
        
        // Add single video if exists
        if ($this->video) {
            $paths[] = $this->video;
        }
        
        // Add multiple videos if exist - handle double JSON encoding
        $videoPaths = $this->video_paths;
        
        // Handle double JSON encoding issue
        if (is_string($videoPaths)) {
            // First decode
            $decoded = json_decode($videoPaths, true);
            
            // If it's still a string, decode again (double encoded)
            if (is_string($decoded)) {
                $videoPaths = json_decode($decoded, true);
            } else {
                $videoPaths = $decoded;
            }
        }
        
        if (!empty($videoPaths) && is_array($videoPaths)) {
            foreach ($videoPaths as $path) {
                if (!empty($path)) {
                    $paths[] = $path;
                }
            }
        }
        
        return $paths;
    }

    /**
     * Get the notification type for this content.
     */
    public function getNotificationType(): string
    {
        return 'event';
    }
}
