<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
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
        // Register @nonce directive for CSP
        Blade::directive('nonce', function () {
            return "<?php echo 'nonce=\"' . csp_nonce() . '\"'; ?>";
        });
    }
}
