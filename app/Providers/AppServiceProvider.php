<?php

namespace App\Providers;

use App\Models\Booking;
use App\Observers\BookingObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\GuzzleHttp\ClientInterface::class, \GuzzleHttp\Client::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Log::info("AppServiceProvider boot called");
        Booking::observe(BookingObserver::class);
    }
}
