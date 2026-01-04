<?php

namespace Brain\Client;

use Illuminate\Support\Facades\Cache;

/**
 * Capability Registry for managing client capabilities.
 *
 * Stores and manages the client's declared capabilities.
 */
class CapabilityRegistry
{
    private array $capabilities = [];

    private string $cacheKey;

    public function __construct()
    {
        $this->cacheKey = 'brain_client_capabilities';
        $this->loadFromCache();
    }

    /**
     * Check if client has a specific capability.
     */
    public function hasCapability(string $name): bool
    {
        return isset($this->capabilities[$name]) && 
               ($this->capabilities[$name]['status'] ?? 'pending') === 'active';
    }

    /**
     * Get a specific capability.
     */
    public function getCapability(string $name): ?array
    {
        return $this->capabilities[$name] ?? null;
    }

    /**
     * Get all capabilities.
     */
    public function getAll(): array
    {
        return $this->capabilities;
    }

    /**
     * Register capabilities (from Brain response).
     */
    public function register(array $capabilities): void
    {
        foreach ($capabilities as $capability) {
            if (isset($capability['data_type'])) {
                $this->capabilities[$capability['data_type']] = $capability;
            }
        }

        $this->saveToCache();
    }

    /**
     * Update capability status.
     */
    public function updateStatus(string $name, string $status): void
    {
        if (isset($this->capabilities[$name])) {
            $this->capabilities[$name]['status'] = $status;
            $this->saveToCache();
        }
    }

    /**
     * Load capabilities from cache.
     */
    private function loadFromCache(): void
    {
        $cached = Cache::get($this->cacheKey);
        if (is_array($cached)) {
            $this->capabilities = $cached;
        }
    }

    /**
     * Save capabilities to cache.
     */
    private function saveToCache(): void
    {
        Cache::put($this->cacheKey, $this->capabilities, now()->addDays(30));
    }

    /**
     * Clear all capabilities.
     */
    public function clear(): void
    {
        $this->capabilities = [];
        Cache::forget($this->cacheKey);
    }
}

