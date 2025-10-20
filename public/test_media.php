<?php
// Simple test script to check media file access
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Media Files Test</h1>";

// Test image
$imagePath = 'storage/announcement-images/H5JP6pLc5w3A0KugmHIatusSNTed1sQGBdDSfPkC.png';
$videoPath = 'storage/announcement-videos/mO7WKKD4b2BC88h7UuB6So9lpPSut1ArrYPR01ku.mp4';

echo "<h2>Image Test</h2>";
echo "<p>Path: $imagePath</p>";
echo "<p>File exists: " . (file_exists($imagePath) ? 'Yes' : 'No') . "</p>";
echo "<img src='$imagePath' style='max-width: 300px; border: 1px solid #ccc;' onerror='this.style.display=\"none\"; this.nextElementSibling.style.display=\"block\";'>";
echo "<div style='display:none; color: red;'>❌ Image failed to load</div>";

echo "<h2>Video Test</h2>";
echo "<p>Path: $videoPath</p>";
echo "<p>File exists: " . (file_exists($videoPath) ? 'Yes' : 'No') . "</p>";
echo "<video controls style='max-width: 400px; border: 1px solid #ccc;'>";
echo "<source src='$videoPath' type='video/mp4'>";
echo "Your browser does not support the video tag.";
echo "</video>";

echo "<h2>Direct File Access Test</h2>";
echo "<p><a href='$imagePath' target='_blank'>Open Image Directly</a></p>";
echo "<p><a href='$videoPath' target='_blank'>Open Video Directly</a></p>";

echo "<h2>Storage Directory Contents</h2>";
if (is_dir('storage')) {
    echo "<p>Storage directory exists</p>";
    if (is_dir('storage/announcement-images')) {
        $files = scandir('storage/announcement-images');
        echo "<p>Images: " . (count($files) - 2) . " files</p>";
    }
    if (is_dir('storage/announcement-videos')) {
        $files = scandir('storage/announcement-videos');
        echo "<p>Videos: " . (count($files) - 2) . " files</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Storage directory not found</p>";
}
?>
