<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SecurityScanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:scan {--type=all : Type of scan (all, dependencies, files, config)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform comprehensive security scan of the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        
        $this->info('🔒 Starting Security Scan...');
        $this->newLine();
        
        $results = [];
        
        switch ($type) {
            case 'dependencies':
                $results = $this->scanDependencies();
                break;
            case 'files':
                $results = $this->scanFiles();
                break;
            case 'config':
                $results = $this->scanConfiguration();
                break;
            case 'all':
            default:
                $results = array_merge(
                    $this->scanDependencies(),
                    $this->scanFiles(),
                    $this->scanConfiguration()
                );
                break;
        }
        
        $this->displayResults($results);
        
        return 0;
    }
    
    /**
     * Scan dependencies for vulnerabilities
     */
    private function scanDependencies()
    {
        $this->info('📦 Scanning Dependencies...');
        $results = [];
        
        // Check if composer.lock exists
        if (!File::exists(base_path('composer.lock'))) {
            $results[] = [
                'type' => 'warning',
                'message' => 'composer.lock file not found. Run composer install to generate it.'
            ];
            return $results;
        }
        
        // Check for outdated packages
        $outdated = $this->checkOutdatedPackages();
        if (!empty($outdated)) {
            $results[] = [
                'type' => 'warning',
                'message' => 'Outdated packages found: ' . implode(', ', $outdated)
            ];
        }
        
        // Check for known vulnerabilities (simplified check)
        $vulnerabilities = $this->checkKnownVulnerabilities();
        if (!empty($vulnerabilities)) {
            foreach ($vulnerabilities as $vuln) {
                $results[] = [
                    'type' => 'error',
                    'message' => "Vulnerability found in {$vuln['package']}: {$vuln['description']}"
                ];
            }
        }
        
        $this->info('✅ Dependencies scan completed');
        return $results;
    }
    
    /**
     * Scan files for security issues
     */
    private function scanFiles()
    {
        $this->info('📁 Scanning Files...');
        $results = [];
        
        $patterns = [
            'dangerous_functions' => [
                'eval(',
                'exec(',
                'system(',
                'shell_exec(',
                'passthru(',
                'file_get_contents(',
                'file_put_contents(',
                'unserialize(',
            ],
            'sql_patterns' => [
                'DB::raw(',
                'DB::select(',
                'DB::statement(',
            ],
            'hardcoded_secrets' => [
                'password.*=.*[\'"][^\'"]+[\'"]',
                'secret.*=.*[\'"][^\'"]+[\'"]',
                'key.*=.*[\'"][^\'"]+[\'"]',
                'token.*=.*[\'"][^\'"]+[\'"]',
            ]
        ];
        
        $directories = [
            app_path(),
            config_path(),
            database_path('migrations'),
            routes_path(),
        ];
        
        foreach ($directories as $directory) {
            if (File::exists($directory)) {
                $files = File::allFiles($directory);
                
                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $content = File::get($file->getPathname());
                        
                        foreach ($patterns as $patternType => $patternList) {
                            foreach ($patternList as $pattern) {
                                if (strpos($content, $pattern) !== false) {
                                    $results[] = [
                                        'type' => 'warning',
                                        'message' => "Potential security issue in {$file->getRelativePathname()}: {$patternType} - {$pattern}"
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $this->info('✅ Files scan completed');
        return $results;
    }
    
    /**
     * Scan configuration for security issues
     */
    private function scanConfiguration()
    {
        $this->info('⚙️ Scanning Configuration...');
        $results = [];
        
        // Check environment configuration
        $envFile = base_path('.env');
        if (File::exists($envFile)) {
            $envContent = File::get($envFile);
            
            // Check for debug mode in production
            if (strpos($envContent, 'APP_DEBUG=true') !== false) {
                $results[] = [
                    'type' => 'error',
                    'message' => 'APP_DEBUG is set to true. This should be false in production.'
                ];
            }
            
            // Check for weak session configuration
            if (strpos($envContent, 'SESSION_DRIVER=file') !== false) {
                $results[] = [
                    'type' => 'warning',
                    'message' => 'Using file-based sessions. Consider using database or redis for better security.'
                ];
            }
            
            // Check for missing security configurations
            $requiredConfigs = [
                'APP_KEY',
                'DB_PASSWORD',
                'MAIL_PASSWORD',
            ];
            
            foreach ($requiredConfigs as $config) {
                if (strpos($envContent, $config . '=') === false || 
                    strpos($envContent, $config . '=""') !== false) {
                    $results[] = [
                        'type' => 'error',
                        'message' => "Missing or empty configuration: {$config}"
                    ];
                }
            }
        }
        
        // Check security configuration file
        $securityConfig = config_path('security.php');
        if (!File::exists($securityConfig)) {
            $results[] = [
                'type' => 'warning',
                'message' => 'Security configuration file not found. Consider implementing security.php'
            ];
        }
        
        $this->info('✅ Configuration scan completed');
        return $results;
    }
    
    /**
     * Check for outdated packages
     */
    private function checkOutdatedPackages()
    {
        $outdated = [];
        
        try {
            $output = shell_exec('composer outdated --direct 2>/dev/null');
            if ($output) {
                $lines = explode("\n", $output);
                foreach ($lines as $line) {
                    if (strpos($line, ' ') !== false) {
                        $parts = explode(' ', $line);
                        if (count($parts) >= 2) {
                            $outdated[] = $parts[0];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore errors in package checking
        }
        
        return $outdated;
    }
    
    /**
     * Check for known vulnerabilities (simplified)
     */
    private function checkKnownVulnerabilities()
    {
        $vulnerabilities = [];
        
        // This is a simplified check. In production, you would use tools like:
        // - composer audit
        // - Snyk
        // - GitHub Security Advisories API
        
        $knownVulnerablePackages = [
            // Add known vulnerable packages here
        ];
        
        $composerLock = json_decode(File::get(base_path('composer.lock')), true);
        
        if ($composerLock && isset($composerLock['packages'])) {
            foreach ($composerLock['packages'] as $package) {
                if (in_array($package['name'], $knownVulnerablePackages)) {
                    $vulnerabilities[] = [
                        'package' => $package['name'],
                        'version' => $package['version'],
                        'description' => 'Known vulnerability in this version'
                    ];
                }
            }
        }
        
        return $vulnerabilities;
    }
    
    /**
     * Display scan results
     */
    private function displayResults($results)
    {
        $this->newLine();
        $this->info('📊 Security Scan Results:');
        $this->newLine();
        
        if (empty($results)) {
            $this->info('✅ No security issues found!');
            return;
        }
        
        $errorCount = 0;
        $warningCount = 0;
        
        foreach ($results as $result) {
            switch ($result['type']) {
                case 'error':
                    $this->error("❌ {$result['message']}");
                    $errorCount++;
                    break;
                case 'warning':
                    $this->warn("⚠️ {$result['message']}");
                    $warningCount++;
                    break;
                default:
                    $this->info("ℹ️ {$result['message']}");
                    break;
            }
        }
        
        $this->newLine();
        $this->info("Summary: {$errorCount} errors, {$warningCount} warnings");
        
        if ($errorCount > 0) {
            $this->newLine();
            $this->error('🚨 Security issues found! Please address them immediately.');
        }
    }
}
