<?php

namespace App\Providers;

use App\Helpers\CSPHelper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class CSPServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only register directives if we're not in console mode
        if (!$this->app->runningInConsole()) {
            // Register CSP nonce directive
            Blade::directive('nonce', function () {
                return "<?php echo 'nonce=\"' . App\Helpers\CSPHelper::getNonce() . '\"'; ?>";
            });

            // Register CSP nonce value directive
            Blade::directive('nonceValue', function () {
                return "<?php echo App\Helpers\CSPHelper::getNonce(); ?>";
            });
        }
    }
}
