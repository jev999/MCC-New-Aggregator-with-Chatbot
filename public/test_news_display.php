<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Media Display Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .test-container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .test-section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .test-image { max-width: 300px; max-height: 200px; border: 1px solid #ccc; object-fit: contain; }
        .test-video { max-width: 400px; max-height: 300px; }
        .error { color: red; background: #fee; padding: 10px; border-radius: 4px; }
        .success { color: green; background: #efe; padding: 10px; border-radius: 4px; }
        .info { color: blue; background: #eef; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1><i class="fas fa-newspaper"></i> News Media Display Test</h1>
        
        <?php
        // Include Laravel bootstrap
        require_once '../vendor/autoload.php';
        $app = require_once '../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        
        use App\Models\News;
        
        $publishedNews = News::where('is_published', true)->orderBy('created_at', 'desc')->get();
        ?>
        
        <div class="info">
            <strong>Found <?= $publishedNews->count() ?> published news articles</strong>
        </div>
        
        <?php foreach ($publishedNews as $article): ?>
            <div class="test-section">
                <h3><?= htmlspecialchars($article->title) ?></h3>
                <p><strong>ID:</strong> <?= $article->id ?> | <strong>Created:</strong> <?= $article->created_at ?></p>
                
                <!-- Image Test -->
                <?php if ($article->image): ?>
                    <?php
                    $imagePath = asset('storage/' . $article->image);
                    $imageFile = public_path('storage/' . $article->image);
                    $imageExists = file_exists($imageFile);
                    ?>
                    
                    <div style="margin: 15px 0;">
                        <h4><i class="fas fa-image"></i> Image Test</h4>
                        <p><strong>Database Path:</strong> <?= htmlspecialchars($article->image) ?></p>
                        <p><strong>Asset URL:</strong> <?= htmlspecialchars($imagePath) ?></p>
                        <p><strong>File Exists:</strong> <?= $imageExists ? '✅ Yes' : '❌ No' ?></p>
                        
                        <?php if ($imageExists): ?>
                            <p><strong>File Size:</strong> <?= number_format(filesize($imageFile) / 1024, 2) ?> KB</p>
                            <div class="success">Image file found and accessible</div>
                            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($article->title) ?>" class="test-image" 
                                 onload="this.nextElementSibling.innerHTML = '✅ Image loaded successfully';"
                                 onerror="this.nextElementSibling.innerHTML = '❌ Image failed to load';">
                            <div style="margin-top: 10px; font-weight: bold;"></div>
                        <?php else: ?>
                            <div class="error">Image file not found on server</div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="margin: 15px 0;">
                        <h4><i class="fas fa-image"></i> Image Test</h4>
                        <div class="info">No image uploaded for this article</div>
                    </div>
                <?php endif; ?>
                
                <!-- Video Test -->
                <?php if ($article->video): ?>
                    <?php
                    $videoPath = asset('storage/' . $article->video);
                    $videoFile = public_path('storage/' . $article->video);
                    $videoExists = file_exists($videoFile);
                    $extension = pathinfo($article->video, PATHINFO_EXTENSION);
                    ?>
                    
                    <div style="margin: 15px 0;">
                        <h4><i class="fas fa-video"></i> Video Test</h4>
                        <p><strong>Database Path:</strong> <?= htmlspecialchars($article->video) ?></p>
                        <p><strong>Asset URL:</strong> <?= htmlspecialchars($videoPath) ?></p>
                        <p><strong>File Exists:</strong> <?= $videoExists ? '✅ Yes' : '❌ No' ?></p>
                        <p><strong>Format:</strong> <?= strtoupper($extension) ?></p>
                        
                        <?php if ($videoExists): ?>
                            <p><strong>File Size:</strong> <?= number_format(filesize($videoFile) / (1024 * 1024), 2) ?> MB</p>
                            <div class="success">Video file found and accessible</div>
                            <video controls class="test-video" preload="metadata" playsinline>
                                <source src="<?= $videoPath ?>" type="video/<?= $extension ?>">
                                <source src="<?= $videoPath ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div style="margin-top: 10px;">
                                <a href="<?= $videoPath ?>" target="_blank" download>📥 Download Video</a>
                            </div>
                        <?php else: ?>
                            <div class="error">Video file not found on server</div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="margin: 15px 0;">
                        <h4><i class="fas fa-video"></i> Video Test</h4>
                        <div class="info">No video uploaded for this article</div>
                    </div>
                <?php endif; ?>
                
                <!-- CSV Test -->
                <?php if ($article->csv_file): ?>
                    <?php
                    $csvPath = asset('storage/' . $article->csv_file);
                    $csvFile = public_path('storage/' . $article->csv_file);
                    $csvExists = file_exists($csvFile);
                    ?>
                    
                    <div style="margin: 15px 0;">
                        <h4><i class="fas fa-file-csv"></i> CSV Test</h4>
                        <p><strong>Database Path:</strong> <?= htmlspecialchars($article->csv_file) ?></p>
                        <p><strong>Asset URL:</strong> <?= htmlspecialchars($csvPath) ?></p>
                        <p><strong>File Exists:</strong> <?= $csvExists ? '✅ Yes' : '❌ No' ?></p>
                        
                        <?php if ($csvExists): ?>
                            <p><strong>File Size:</strong> <?= number_format(filesize($csvFile) / 1024, 2) ?> KB</p>
                            <div class="success">CSV file found and accessible</div>
                            <a href="<?= $csvPath ?>" target="_blank" download>📥 Download CSV</a>
                        <?php else: ?>
                            <div class="error">CSV file not found on server</div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="margin: 15px 0;">
                        <h4><i class="fas fa-file-csv"></i> CSV Test</h4>
                        <div class="info">No CSV file uploaded for this article</div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="test-section">
            <h3><i class="fas fa-link"></i> Direct File Access Test</h3>
            <p>Test direct access to media files:</p>
            <ul>
                <li><a href="storage/news-images/" target="_blank">News Images Directory</a></li>
                <li><a href="storage/news-videos/" target="_blank">News Videos Directory</a></li>
                <li><a href="storage/announcement-images/" target="_blank">Announcement Images Directory</a></li>
                <li><a href="storage/announcement-videos/" target="_blank">Announcement Videos Directory</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
