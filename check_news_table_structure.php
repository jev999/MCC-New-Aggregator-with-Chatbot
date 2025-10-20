<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== CHECKING NEWS TABLE STRUCTURE ===\n\n";

// Check if news table exists
if (Schema::hasTable('news')) {
    echo "✅ News table exists\n\n";
    
    // Get table structure
    $columns = DB::select("DESCRIBE news");
    
    echo "NEWS TABLE COLUMNS:\n";
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type}) " . 
             ($column->Null === 'YES' ? 'NULL' : 'NOT NULL') . 
             ($column->Default !== null ? " DEFAULT '{$column->Default}'" : '') . "\n";
    }
    
    echo "\n";
    
    // Check specifically for media columns
    $mediaColumns = ['image', 'video', 'csv_file'];
    echo "MEDIA COLUMNS CHECK:\n";
    
    foreach ($mediaColumns as $column) {
        $exists = Schema::hasColumn('news', $column);
        echo "  $column: " . ($exists ? '✅ Exists' : '❌ Missing') . "\n";
    }
    
    echo "\n";
    
    // Check current data
    $newsCount = DB::table('news')->count();
    echo "Total news records: $newsCount\n";
    
    if ($newsCount > 0) {
        echo "\nNEWS RECORDS:\n";
        $newsRecords = DB::table('news')->orderBy('id')->get();
        
        foreach ($newsRecords as $news) {
            echo "  ID {$news->id}: {$news->title}\n";
            echo "    Image: " . ($news->image ?? 'NULL') . "\n";
            echo "    Video: " . ($news->video ?? 'NULL') . "\n";
            echo "    CSV: " . ($news->csv_file ?? 'NULL') . "\n";
            echo "    Published: " . ($news->is_published ? 'Yes' : 'No') . "\n";
            echo "    Created: {$news->created_at}\n\n";
        }
    }
    
} else {
    echo "❌ News table does not exist\n";
}

// Check migration files
echo "=== CHECKING MIGRATION FILES ===\n\n";

$migrationPath = database_path('migrations');
$migrationFiles = glob($migrationPath . '/*news*.php');

if (!empty($migrationFiles)) {
    echo "Found news migration files:\n";
    foreach ($migrationFiles as $file) {
        echo "  - " . basename($file) . "\n";
    }
    
    // Check the content of the latest migration
    $latestMigration = end($migrationFiles);
    echo "\nLatest migration content:\n";
    echo "File: " . basename($latestMigration) . "\n";
    
    $content = file_get_contents($latestMigration);
    
    // Extract the up() method
    if (preg_match('/public function up\(\).*?\{(.*?)\}/s', $content, $matches)) {
        $upMethod = $matches[1];
        echo "Up method content:\n";
        echo $upMethod . "\n";
    }
} else {
    echo "❌ No news migration files found\n";
}

echo "\n=== DIAGNOSIS COMPLETE ===\n";
