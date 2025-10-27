<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SecureFileUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:2048', // 2MB max size
                'dimensions:max_width=4000,max_height=4000',
            ],
            'images' => [
                'nullable',
                'array',
                'max:10', // Maximum 10 images
            ],
            'images.*' => [
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:2048',
                'dimensions:max_width=4000,max_height=4000',
            ],
            'video' => [
                'nullable',
                'mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-msvideo',
                'max:51200', // 50MB max for videos
            ],
            'videos' => [
                'nullable',
                'array',
                'max:5', // Maximum 5 videos
            ],
            'videos.*' => [
                'mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-msvideo',
                'max:51200',
            ],
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Only JPEG, JPG, PNG, GIF, and WebP images are allowed.',
            'image.max' => 'Image size must not exceed 2MB.',
            'image.dimensions' => 'Image dimensions must not exceed 4000x4000 pixels.',
            'images.array' => 'Images must be an array.',
            'images.max' => 'Maximum 10 images are allowed.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Only JPEG, JPG, PNG, GIF, and WebP images are allowed.',
            'images.*.max' => 'Each image must not exceed 2MB.',
            'images.*.dimensions' => 'Image dimensions must not exceed 4000x4000 pixels.',
            'video.mimetypes' => 'Only MP4, MPEG, MOV, and AVI video formats are allowed.',
            'video.max' => 'Video size must not exceed 50MB.',
            'videos.array' => 'Videos must be an array.',
            'videos.max' => 'Maximum 5 videos are allowed.',
            'videos.*.mimetypes' => 'Only MP4, MPEG, MOV, and AVI video formats are allowed.',
            'videos.*.max' => 'Each video must not exceed 50MB.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Scan uploaded files for viruses (placeholder for actual virus scanning)
            $this->scanForViruses($validator);
        });
    }

    /**
     * Scan files for viruses
     * In production, integrate with ClamAV or VirusTotal API
     */
    protected function scanForViruses($validator): void
    {
        $uploadedFiles = array_filter([
            $this->file('image'),
            $this->file('video'),
            $this->file('images') ? (array) $this->file('images') : [],
            $this->file('videos') ? (array) $this->file('videos') : [],
        ]);

        if (empty($uploadedFiles)) {
            return;
        }

        // Collect all files to scan
        $allFiles = [];
        if ($this->hasFile('image')) {
            $allFiles[] = $this->file('image');
        }
        if ($this->hasFile('video')) {
            $allFiles[] = $this->file('video');
        }
        if ($this->hasFile('images')) {
            $allFiles = array_merge($allFiles, $this->file('images'));
        }
        if ($this->hasFile('videos')) {
            $allFiles = array_merge($allFiles, $this->file('videos'));
        }

        foreach ($allFiles as $file) {
            if (!$file) {
                continue;
            }

            // Basic security checks
            if (!$this->isFileSafe($file)) {
                $validator->errors()->add(
                    $file->getClientOriginalName(),
                    'File failed security validation: ' . $file->getClientOriginalName()
                );
            }
        }

        // In production: Integrate with ClamAV
        // Example: $this->scanWithClamAV($file)
        
        // In production: Integrate with VirusTotal API
        // Example: $this->scanWithVirusTotal($file)
    }

    /**
     * Basic file safety checks
     */
    protected function isFileSafe($file): bool
    {
        // Check file extension matches mime type
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mpeg', 'mov', 'avi'];
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            return false;
        }

        // Check for suspicious double extensions
        $filename = strtolower($file->getClientOriginalName());
        if (preg_match('/\.(php|exe|bat|cmd|sh|phtml|jsp|asp|aspx)$/i', $filename)) {
            return false;
        }

        // Check file size
        if ($file->getSize() > 52428800) { // 50MB
            return false;
        }

        // Check for null bytes (common in malicious files)
        if (strpos($filename, "\0") !== false) {
            return false;
        }

        return true;
    }

    /**
     * Scan file with ClamAV (placeholder for production implementation)
     */
    protected function scanWithClamAV($file): bool
    {
        // Example implementation:
        // $command = "clamdscan --no-summary " . escapeshellarg($file->getPathname());
        // exec($command, $output, $returnCode);
        // return $returnCode === 0;
        
        return true; // Placeholder
    }

    /**
     * Scan file with VirusTotal API (placeholder for production implementation)
     */
    protected function scanWithVirusTotal($file): bool
    {
        // Example implementation:
        // $apiKey = config('services.virustotal.key');
        // $hash = md5_file($file->getPathname());
        // Make API call to VirusTotal
        // Return scan results
        
        return true; // Placeholder
    }
}

