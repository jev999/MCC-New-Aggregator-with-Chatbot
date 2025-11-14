<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DirectDownloadController extends Controller
{
    /**
     * Handle direct file download
     */
    public function download($filename)
    {
        try {
            Log::info('Direct download requested', ['filename' => $filename]);
            
            // Decode URL if it's URL encoded
            $decodedFilename = urldecode($filename);
            
            // Get the storage path
            $storagePath = storage_path('app');
            
            // Possible directories where backup files might be stored
            $possibleDirs = [
                'Laravel',
                config('backup.backup.name', 'Laravel'),
                config('app.name', 'Laravel'),
                'backups',
                '', // Root directory
            ];
            
            // Try to find the file
            $filePath = null;
            
            // First, try with the exact filename
            foreach ($possibleDirs as $dir) {
                $path = $storagePath . '/' . $dir . '/' . $decodedFilename;
                if (file_exists($path)) {
                    $filePath = $path;
                    break;
                }
            }
            
            // If not found, try with alternative extension
            if (!$filePath) {
                $altFilename = preg_match('/\.zip$/i', $decodedFilename) 
                    ? preg_replace('/\.zip$/i', '.sql', $decodedFilename)
                    : preg_replace('/\.sql$/i', '.zip', $decodedFilename);
                
                foreach ($possibleDirs as $dir) {
                    $path = $storagePath . '/' . $dir . '/' . $altFilename;
                    if (file_exists($path)) {
                        $filePath = $path;
                        break;
                    }
                }
            }
            
            // If still not found, try to find any file with similar name pattern
            if (!$filePath) {
                $fileBaseName = pathinfo($decodedFilename, PATHINFO_FILENAME);
                $fileBaseParts = explode('_', $fileBaseName);
                
                // If we have a database name and date pattern
                if (count($fileBaseParts) >= 3) {
                    $dbName = $fileBaseParts[0];
                    $datePattern = isset($fileBaseParts[1]) ? $fileBaseParts[1] : '';
                    
                    // Scan directories for matching files
                    foreach ($possibleDirs as $dir) {
                        $dirPath = $storagePath . '/' . $dir;
                        if (is_dir($dirPath)) {
                            $files = scandir($dirPath);
                            foreach ($files as $file) {
                                if ($file === '.' || $file === '..') continue;
                                
                                if (strpos($file, $dbName) !== false && 
                                    (empty($datePattern) || strpos($file, $datePattern) !== false)) {
                                    $filePath = $dirPath . '/' . $file;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
            
            // If file not found, return 404
            if (!$filePath || !file_exists($filePath)) {
                Log::warning('Backup file not found for direct download', [
                    'requested' => $filename,
                    'decoded' => $decodedFilename,
                    'searched_dirs' => $possibleDirs
                ]);
                
                return response()->json([
                    'error' => 'File not found',
                    'message' => 'The requested backup file could not be found.'
                ], 404);
            }
            
            // Get file info
            $fileSize = filesize($filePath);
            $fileName = basename($filePath);
            $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
            
            Log::info('File found for direct download', [
                'path' => $filePath,
                'size' => $fileSize,
                'mime' => $mimeType
            ]);
            
            // Set headers for download
            $headers = [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Content-Length' => $fileSize,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ];
            
            // Return file response
            return new BinaryFileResponse($filePath, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Direct download failed', [
                'filename' => $filename,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Download failed',
                'message' => 'An error occurred while trying to download the file: ' . $e->getMessage()
            ], 500);
        }
    }
}
