<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\News;
use App\Models\Admin;

echo "=== TESTING DEPARTMENT ADMIN NEWS FUNCTIONALITY ===\n\n";

// Check if we have department admins
$departmentAdmins = Admin::where('role', 'department_admin')->get();

if ($departmentAdmins->isEmpty()) {
    echo "❌ No department admins found. Creating a test department admin...\n";
    
    $departmentAdmin = Admin::create([
        'username' => 'dept_admin_test',
        'email' => 'dept.admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'department_admin',
        'department' => 'Computer Science',
        'is_active' => true
    ]);
    
    echo "✅ Created test department admin: {$departmentAdmin->username}\n";
} else {
    $departmentAdmin = $departmentAdmins->first();
    echo "✅ Using existing department admin: {$departmentAdmin->username} ({$departmentAdmin->department})\n";
}

echo "\n";

// Simulate department admin creating news with media
$sourceImages = glob(storage_path('app/public/announcement-images/*'));
$sourceVideos = glob(storage_path('app/public/announcement-videos/*'));

if (empty($sourceImages) || empty($sourceVideos)) {
    echo "❌ No source files available for testing\n";
    exit;
}

// Create news articles that simulate department admin uploads
$testNews = [
    [
        'title' => 'Department News - Image Test',
        'content' => 'This news article was created by a department admin to test image upload functionality.',
        'media_type' => 'image'
    ],
    [
        'title' => 'Department News - Video Test', 
        'content' => 'This news article was created by a department admin to test video upload functionality.',
        'media_type' => 'video'
    ],
    [
        'title' => 'Department News - Both Media Test',
        'content' => 'This news article was created by a department admin to test both image and video upload functionality.',
        'media_type' => 'both'
    ]
];

$newsImageDir = storage_path('app/public/news-images');
$newsVideoDir = storage_path('app/public/news-videos');

foreach ($testNews as $index => $data) {
    echo "Creating: {$data['title']}\n";
    
    $news = new News();
    $news->title = $data['title'];
    $news->content = $data['content'];
    $news->is_published = true;
    $news->admin_id = $departmentAdmin->id;
    
    // Add media based on type
    if ($data['media_type'] === 'image' || $data['media_type'] === 'both') {
        $sourceImage = $sourceImages[$index % count($sourceImages)];
        $imageName = 'dept_admin_test_' . time() . '_' . $index . '_' . basename($sourceImage);
        $targetImage = $newsImageDir . '/' . $imageName;
        
        if (copy($sourceImage, $targetImage)) {
            $news->image = 'news-images/' . $imageName;
            echo "  ✅ Added image: $imageName\n";
        }
    }
    
    if ($data['media_type'] === 'video' || $data['media_type'] === 'both') {
        $sourceVideo = $sourceVideos[$index % count($sourceVideos)];
        $videoName = 'dept_admin_test_' . time() . '_' . $index . '_' . basename($sourceVideo);
        $targetVideo = $newsVideoDir . '/' . $videoName;
        
        if (copy($sourceVideo, $targetVideo)) {
            $news->video = 'news-videos/' . $videoName;
            echo "  ✅ Added video: $videoName\n";
        }
    }
    
    $news->save();
    echo "  ✅ Created news ID: {$news->id}\n\n";
    
    // Small delay to ensure unique timestamps
    sleep(1);
}

echo "=== VERIFICATION ===\n\n";

// Show all news articles by department admins
$departmentNews = News::whereHas('admin', function($query) {
    $query->where('role', 'department_admin');
})->with('admin')->orderBy('created_at', 'desc')->get();

echo "NEWS ARTICLES BY DEPARTMENT ADMINS:\n";
foreach ($departmentNews as $news) {
    echo "ID {$news->id}: {$news->title}\n";
    echo "  Created by: {$news->admin->username} ({$news->admin->department} Department)\n";
    
    $mediaTypes = [];
    if ($news->image) {
        $imagePath = storage_path('app/public/' . $news->image);
        $imageExists = file_exists($imagePath);
        $mediaTypes[] = '📷 Image ' . ($imageExists ? '✅' : '❌');
    }
    if ($news->video) {
        $videoPath = storage_path('app/public/' . $news->video);
        $videoExists = file_exists($videoPath);
        $mediaTypes[] = '🎥 Video ' . ($videoExists ? '✅' : '❌');
    }
    if ($news->csv_file) {
        $csvPath = storage_path('app/public/' . $news->csv_file);
        $csvExists = file_exists($csvPath);
        $mediaTypes[] = '📄 CSV ' . ($csvExists ? '✅' : '❌');
    }
    
    if (empty($mediaTypes)) {
        echo "  ❌ No media\n";
    } else {
        echo "  " . implode(', ', $mediaTypes) . "\n";
    }
    
    echo "  Created: {$news->created_at}\n";
    echo "  Published: " . ($news->is_published ? 'Yes' : 'No') . "\n\n";
}

// Show summary statistics
$totalNews = News::count();
$departmentNewsCount = $departmentNews->count();
$departmentNewsWithMedia = $departmentNews->filter(function($news) {
    return $news->image || $news->video || $news->csv_file;
})->count();

echo "SUMMARY:\n";
echo "Total news articles: $totalNews\n";
echo "Department admin articles: $departmentNewsCount\n";
echo "Department articles with media: $departmentNewsWithMedia\n";
if ($departmentNewsCount > 0) {
    echo "Department success rate: " . round(($departmentNewsWithMedia / $departmentNewsCount) * 100, 1) . "%\n";
}

echo "\n=== TESTING COMPLETE ===\n";
echo "\nNEXT STEPS:\n";
echo "1. Login as department admin: {$departmentAdmin->username}\n";
echo "2. Go to: http://127.0.0.1:8000/department-admin/news/create\n";
echo "3. Test creating news with media files\n";
echo "4. Verify articles appear on user dashboard\n";
echo "5. Check that media displays properly\n\n";

echo "Department admin login credentials:\n";
echo "Username: {$departmentAdmin->username}\n";
echo "Password: password\n";
echo "Department: {$departmentAdmin->department}\n";
