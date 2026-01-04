<?php

namespace Brain\Client;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class BrainNucleusClientServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/brain.php',
            'brain'
        );

        // Register BrainEventClient singleton
        $this->app->singleton(BrainEventClient::class, function ($app) {
            $baseUrl = config('brain.base_url') ?? config('services.brain.base_url') ?? config('services.brain.url');
            $apiKey = config('brain.api_key') ?? config('services.brain.api_key');

            if (!$baseUrl || !$apiKey) {
                // Return a null-safe client that logs warnings but doesn't break
                return new BrainEventClient('', '');
            }

            return new BrainEventClient($baseUrl, $apiKey);
        });

        // Register CapabilityRegistry singleton
        $this->app->singleton(CapabilityRegistry::class, function ($app) {
            return new CapabilityRegistry();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/brain.php' => config_path('brain.php'),
        ], 'brain-config');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\SendBrainHeartbeat::class,
            ]);

            // Auto-schedule heartbeat if enabled
            $this->app->booted(function () {
                if (config('brain.heartbeat.enabled', true)) {
                    $this->scheduleHeartbeat();
                }
            });
        }

        // Handle capability registration on boot
        if (config('brain.capabilities.check_on_boot', true)) {
            $this->app->booted(function () {
                $this->handleCapabilities();
            });
        }
    }

    /**
     * Handle capability registration and validation on boot.
     */
    protected function handleCapabilities(): void
    {
        try {
            $brain = $this->app->make(BrainEventClient::class);
            $registry = $this->app->make(CapabilityRegistry::class);

            // Skip if client is not configured
            if (!$brain->getVersion()) {
                return;
            }

            // Get Brain's configuration
            $config = $brain->getConfig();
            if (!$config) {
                \Illuminate\Support\Facades\Log::warning('Brain config not available for capability check');
                return;
            }

            // Check for required capabilities
            if (isset($config['data_types'])) {
                $requiredTypes = array_filter($config['data_types'], fn($dt) => $dt['required'] ?? false);
                if (!empty($requiredTypes)) {
                    $missingRequired = [];
                    foreach ($requiredTypes as $dataType) {
                        if (!$registry->hasCapability($dataType['name'])) {
                            $missingRequired[] = $dataType['name'];
                        }
                    }

                    if (!empty($missingRequired)) {
                        \Illuminate\Support\Facades\Log::warning('Brain missing required capabilities', [
                            'missing' => $missingRequired,
                        ]);
                    }
                }
            }

            // Auto-register capabilities if configured
            if (config('brain.capabilities.auto_register', true)) {
                $clientCapabilities = $this->getClientCapabilities();
                if (!empty($clientCapabilities)) {
                    $result = $brain->registerCapabilities($clientCapabilities);
                    if ($result) {
                        // Update registry with registered capabilities
                        $registry->register($result['results'] ?? []);
                        \Illuminate\Support\Facades\Log::info('Brain capabilities auto-registered', [
                            'registered' => $result['registered'] ?? 0,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Brain capability check failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get client capabilities from config.
     * Clients can define capabilities in config/brain.php.
     */
    protected function getClientCapabilities(): array
    {
        $capabilities = config('brain.capabilities.list', []);

        // Convert config format to API format
        $formatted = [];
        foreach ($capabilities as $name => $config) {
            if (is_string($config)) {
                // Simple format: 'seo_snapshot' => '1.0'
                $formatted[] = [
                    'data_type' => $name,
                    'version' => $config,
                    'status' => 'ready',
                ];
            } elseif (is_array($config)) {
                // Full format with options
                $formatted[] = array_merge([
                    'data_type' => $name,
                    'version' => $config['version'] ?? '1.0',
                    'status' => $config['status'] ?? 'ready',
                ], $config);
            }
        }

        return $formatted;
    }

    /**
     * Schedule the heartbeat command.
     */
    protected function scheduleHeartbeat(): void
    {
        $schedule = $this->app->make(Schedule::class);
        $interval = config('brain.heartbeat.interval', 5);

        $schedule->command('brain:heartbeat')
            ->everyFiveMinutes()
            ->when(function () use ($interval) {
                // Custom interval check - default is every 5 minutes
                if ($interval === 5) {
                    return true;
                }
                // For other intervals, check if current minute is divisible
                return now()->minute % $interval === 0;
            })
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/brain-heartbeat.log'));
    }
}
