<?php

namespace App\Providers;

use App\Models\Subscription;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

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
        // Force HTTPS scheme when using ngrok or similar proxy
        URL::forceScheme('https'); 

        // Register our custom Subscription model with Cashier
        Cashier::useSubscriptionModel(Subscription::class);

        // Remove or comment out the forceRootUrl line if it's still there
        // URL::forceRootUrl(config('app.url')); 

        Vite::prefetch(concurrency: 3);
    }
}
