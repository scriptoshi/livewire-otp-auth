<?php

namespace Scriptoshi\LivewireOtpAuth;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Scriptoshi\LivewireOtpAuth\Components\OtpAuthentication;

class LivewireOtpAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/livewire-otp-auth.php', 'livewire-otp-auth'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'livewire-otp-auth');
        
        // Register Livewire component
        Livewire::component('otp-authentication', OtpAuthentication::class);
        
        // Publish assets
        $this->publishes([
            __DIR__.'/../config/livewire-otp-auth.php' => config_path('livewire-otp-auth.php'),
            __DIR__.'/../resources/views' => resource_path('views/vendor/livewire-otp-auth'),
        ], 'livewire-otp-auth');
    }
}
