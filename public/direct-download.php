<?php
/**
 * Direct Download Script
 * This script bypasses Laravel entirely to provide a direct download mechanism
 */

// Basic security check
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header('HTTP/1.0 400 Bad Request');
    echo 'Error: No file specified';
    exit;
}

// Get the filename
$filename = basename($_GET['file']);

// Security check - only allow SQL and ZIP files
if (!preg_match('/\.(sql|zip)$/i', $filename)) {
    header('HTTP/1.0 400 Bad Request');
    echo 'Error: Invalid file type';
    exit;
}

// Define possible storage locations
$storagePath = __DIR__ . '/../storage/app';
$possibleDirs = [
    $storagePath . '/Laravel',
    $storagePath . '/backups',
    $storagePath,  // Root directory
];

// Try to find the file
$filePath = null;
foreach ($possibleDirs as $dir) {
    $path = $dir . '/' . $filename;
    if (file_exists($path)) {
        $filePath = $path;
        break;
    }
}

// If file not found, try with alternative extension
if (!$filePath) {
    $altFilename = preg_match('/\.zip$/i', $filename) 
        ? preg_replace('/\.zip$/i', '.sql', $filename)
        : preg_replace('/\.sql$/i', '.zip', $filename);
    
    foreach ($possibleDirs as $dir) {
        $path = $dir . '/' . $altFilename;
        if (file_exists($path)) {
            $filePath = $path;
            $filename = $altFilename; // Update filename to match what we found
            break;
        }
    }
}

// If still not found, try to find any file with similar name pattern
if (!$filePath) {
    $fileBaseName = pathinfo($filename, PATHINFO_FILENAME);
    $fileBaseParts = explode('_', $fileBaseName);
    
    if (count($fileBaseParts) >= 2) {
        $dbName = $fileBaseParts[0];
        $datePattern = isset($fileBaseParts[1]) ? $fileBaseParts[1] : '';
        
        foreach ($possibleDirs as $dir) {
            if (is_dir($dir)) {
                $files = scandir($dir);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    
                    if (strpos($file, $dbName) !== false && 
                        (empty($datePattern) || strpos($file, $datePattern) !== false)) {
                        $filePath = $dir . '/' . $file;
                        $filename = $file; // Update filename to match what we found
                        break 2;
                    }
                }
            }
        }
    }
}

// If file not found, return error
if (!$filePath || !file_exists($filePath)) {
    header('HTTP/1.0 404 Not Found');
    echo 'Error: File not found';
    exit;
}

// Check if file is readable
if (!is_readable($filePath)) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Error: File is not readable';
    exit;
}

// Get file size
$fileSize = filesize($filePath);
if ($fileSize === false) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Error: Could not determine file size';
    exit;
}

// Determine MIME type
$mimeType = 'application/octet-stream';
if (function_exists('mime_content_type')) {
    $detectedType = mime_content_type($filePath);
    if ($detectedType !== false) {
        $mimeType = $detectedType;
    }
}

// Set appropriate headers for download
header('Content-Description: File Transfer');
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $fileSize);

// Clear output buffer
ob_clean();
flush();

// Output file content
readfile($filePath);
exit;
