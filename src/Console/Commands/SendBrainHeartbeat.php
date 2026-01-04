<?php

namespace Brain\Client\Console\Commands;

use Brain\Client\BrainEventClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendBrainHeartbeat extends Command
{
    protected $signature = 'brain:heartbeat 
                            {--sync-events : Force sync of custom events even if cached}';

    protected $description = 'Send a heartbeat to Brain Nucleus and sync custom events';

    public function handle(): int
    {
        $baseUrl = config('brain.base_url') ?? config('services.brain.base_url') ?? config('services.brain.url');
        $apiKey = config('brain.api_key') ?? config('services.brain.api_key');

        if (!$baseUrl || !$apiKey) {
            $this->warn('Brain configuration missing. Set BRAIN_BASE_URL and BRAIN_API_KEY in .env');
            return Command::SUCCESS; // Don't fail, just skip
        }

        try {
            $client = app(BrainEventClient::class);

            // Send heartbeat
            $result = $this->sendHeartbeat($client);

            if ($result) {
                $this->info('✓ Heartbeat sent to Brain');
            } else {
                $this->error('✗ Failed to send heartbeat');
            }

            // Sync custom events
            $this->syncCustomEvents($baseUrl, $apiKey);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Heartbeat exception: ' . $e->getMessage());
            Log::error('Brain heartbeat failed', ['error' => $e->getMessage()]);
            return Command::SUCCESS; // Don't fail the scheduler
        }
    }

    /**
     * Send the health.ping heartbeat event.
     */
    protected function sendHeartbeat(BrainEventClient $client): ?array
    {
        return $client->send('health.ping', [
            'site' => config('app.name', 'Unknown'),
            'environment' => app()->environment(),
            'url' => config('app.url'),
            'metadata' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'hostname' => gethostname() ?: 'unknown',
                'client_version' => BrainEventClient::CLIENT_VERSION,
            ],
        ]);
    }

    /**
     * Sync custom events from config to Brain.
     */
    protected function syncCustomEvents(string $baseUrl, string $apiKey): void
    {
        $events = config('brain.events', []);

        if (empty($events)) {
            return;
        }

        // Cache key based on hash of events config
        $configHash = md5(json_encode($events));
        $cacheKey = 'brain_events_synced_' . $configHash;

        // Skip if already synced (unless forced)
        if (!$this->option('sync-events') && Cache::has($cacheKey)) {
            $this->line('  Events already synced (use --sync-events to force)');
            return;
        }

        $this->line('  Syncing ' . count($events) . ' custom event(s) to Brain...');
        $synced = 0;

        foreach ($events as $eventType => $description) {
            try {
                $response = Http::withHeaders([
                    'X-Brain-Key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])->post("{$baseUrl}/api/v1/client/events/register", [
                            'event_type' => $eventType,
                            'description' => $description,
                        ]);

                if ($response->successful()) {
                    $synced++;
                    $this->line("    ✓ {$eventType}");
                } else {
                    $this->warn("    ✗ {$eventType}: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->warn("    ✗ {$eventType}: " . $e->getMessage());
            }
        }

        // Cache for 24 hours
        Cache::put($cacheKey, true, now()->addDay());

        $this->info("  Synced {$synced}/" . count($events) . " events");
    }
}
