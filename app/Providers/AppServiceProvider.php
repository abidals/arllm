<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        Gate::define('access-dashboard', fn ($user) => $user->hasAnyRole(['admin', 'editor', 'author']));
        Gate::define('manage-users', fn ($user) => $user->hasRole('admin'));
        Gate::define('manage-settings', fn ($user) => $user->hasRole('admin'));
        Gate::define('manage-categories', fn ($user) => $user->hasAnyRole(['admin', 'editor']));

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by((string) $request->email.'|'.$request->ip()));
        RateLimiter::for('comments', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));
        RateLimiter::for('admin', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
    }
}