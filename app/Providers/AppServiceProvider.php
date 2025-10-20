<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;
use App\Models\Admin;
use App\Observers\AnnouncementObserver;
use App\Observers\EventObserver;
use App\Observers\NewsObserver;
use App\Observers\AdminObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Announcement::observe(AnnouncementObserver::class);
        Event::observe(EventObserver::class);
        News::observe(NewsObserver::class);
        Admin::observe(AdminObserver::class);
    }
}
