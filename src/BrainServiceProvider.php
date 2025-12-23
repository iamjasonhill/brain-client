<?php

namespace Brain\Client;

use Illuminate\Support\ServiceProvider;

/**
 * Brain Service Provider for Laravel
 *
 * This provider auto-registers with Laravel's package discovery.
 * It binds BrainEventClient and BrainServiceClient as singletons
 * using environment variables for configuration.
 *
 * Required environment variables:
 * - BRAIN_BASE_URL: The base URL of your Brain Nucleus instance
 * - BRAIN_API_KEY: Your API key for sending events
 * - BRAIN_SERVICE_SECRET: Your service secret for proxy calls (optional)
 */
class BrainServiceProvider extends ServiceProvider
{
    /**
     * Register the service bindings.
     */
    public function register(): void
    {
        // Bind BrainEventClient as singleton for sending events
        $this->app->singleton(BrainEventClient::class, function ($app) {
            $baseUrl = config('brain.base_url', env('BRAIN_BASE_URL'));
            $apiKey = config('brain.api_key', env('BRAIN_API_KEY'));

            if (!$baseUrl || !$apiKey) {
                throw new \RuntimeException(
                    'Brain client not configured. Set BRAIN_BASE_URL and BRAIN_API_KEY in your .env file.'
                );
            }

            return new BrainEventClient($baseUrl, $apiKey);
        });

        // Bind BrainServiceClient as singleton for proxy calls
        $this->app->singleton(BrainServiceClient::class, function ($app) {
            $baseUrl = config('brain.base_url', env('BRAIN_BASE_URL'));
            $serviceSecret = config('brain.service_secret', env('BRAIN_SERVICE_SECRET'));

            if (!$baseUrl || !$serviceSecret) {
                throw new \RuntimeException(
                    'Brain service client not configured. Set BRAIN_BASE_URL and BRAIN_SERVICE_SECRET in your .env file.'
                );
            }

            return new BrainServiceClient($baseUrl, $serviceSecret);
        });

        // Merge default config
        $this->mergeConfigFrom(__DIR__ . '/../config/brain.php', 'brain');
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // Publish config file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/brain.php' => config_path('brain.php'),
            ], 'brain-config');
        }
    }
}
