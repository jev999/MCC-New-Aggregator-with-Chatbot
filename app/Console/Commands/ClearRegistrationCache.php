<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearRegistrationCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registration:clear-cache {token?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear registration cache for specific token or all registration caches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = $this->argument('token');
        $all = $this->option('all');

        if ($all) {
            $this->clearAllRegistrationCaches();
        } elseif ($token) {
            $this->clearSpecificTokenCache($token);
        } else {
            $this->error('Please provide a token or use --all flag');
            return 1;
        }

        return 0;
    }

    private function clearSpecificTokenCache($token)
    {
        $this->info("Clearing cache for token: {$token}");

        // Clear admin registration cache
        $adminCacheKey = 'admin_registration_' . $token;
        $adminCachedData = Cache::get($adminCacheKey);
        Cache::forget($adminCacheKey);

        // Clear office admin registration cache
        $officeCacheKey = 'office_admin_registration_' . $token;
        $officeCachedData = Cache::get($officeCacheKey);
        Cache::forget($officeCacheKey);

        // Clear potential case variations
        Cache::forget('admin_registration_' . strtolower($token));
        Cache::forget('admin_registration_' . strtoupper($token));
        Cache::forget('office_admin_registration_' . strtolower($token));
        Cache::forget('office_admin_registration_' . strtoupper($token));

        $this->info('Cache cleared successfully!');
        
        if ($adminCachedData) {
            $this->line('Previous admin cache data:');
            $this->line(json_encode($adminCachedData, JSON_PRETTY_PRINT));
        }
        
        if ($officeCachedData) {
            $this->line('Previous office admin cache data:');
            $this->line(json_encode($officeCachedData, JSON_PRETTY_PRINT));
        }

        if (!$adminCachedData && !$officeCachedData) {
            $this->warn('No cache data found for this token');
        }
    }

    private function clearAllRegistrationCaches()
    {
        $this->info('Clearing all registration caches...');

        $cleared = 0;
        
        // Try to get all cache keys (works with Redis/Database cache)
        try {
            if (config('cache.default') === 'redis') {
                $cacheKeys = Cache::getRedis()->keys('*registration_*');
                foreach ($cacheKeys as $key) {
                    $cleanKey = str_replace(config('cache.prefix') . ':', '', $key);
                    Cache::forget($cleanKey);
                    $cleared++;
                }
            } else {
                // For database/file cache, we'll clear known patterns
                $patterns = [
                    'admin_registration_',
                    'office_admin_registration_'
                ];
                
                // This is a simplified approach - in production you might want to
                // store registration tokens in a separate table for easier cleanup
                $this->warn('Database cache detected. Manual cleanup recommended.');
                $this->line('Consider running: php artisan cache:clear');
            }
        } catch (\Exception $e) {
            $this->error('Error accessing cache: ' . $e->getMessage());
            $this->line('Falling back to cache:clear command...');
            $this->call('cache:clear');
            return;
        }

        $this->info("Cleared {$cleared} registration cache entries");
    }
}
