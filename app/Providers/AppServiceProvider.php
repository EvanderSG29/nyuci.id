<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        File::ensureDirectoryExists(storage_path('framework/views-runtime'));

        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            $limit = max(1, (int) config('notifications.dropdown_limit', 6));

            $view->with([
                'headerNotifications' => $user?->unreadNotifications()->latest()->limit($limit)->get() ?? collect(),
                'headerUnreadNotificationCount' => $user?->unreadNotifications()->count() ?? 0,
            ]);
        });
    }
}
