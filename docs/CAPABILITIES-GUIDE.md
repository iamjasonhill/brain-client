# Brain Capabilities Integration Guide

This guide explains how to integrate with Brain's capability system, allowing your application to declare what data it can provide and automatically discover what Brain needs.

## Overview

The capability system enables two-way communication between your application and Brain:

- **Brain declares** what data types it accepts and what fields are required
- **Your application declares** what capabilities it has (what data it can send)
- **Automatic matching** ensures compatibility
- **Schema validation** ensures data integrity

## Basic Integration

### 1. Get Brain's Configuration

On application boot, fetch what Brain needs:

```php
use Brain\Client\BrainEventClient;

public function boot()
{
    $this->app->booted(function() {
        $brain = app(BrainEventClient::class);
        
        // Get what Brain needs
        $config = $brain->getConfig();
        
        if ($config) {
            // $config contains:
            // - data_types: Available data types Brain accepts
            // - client_capabilities: Your current registered capabilities
            // - recommendations: Suggested capabilities to add
        }
    });
}
```

### 2. Register Your Capabilities

Declare what your application can provide:

```php
$brain = app(BrainEventClient::class);

$brain->registerCapabilities([
    [
        'data_type' => 'seo_snapshot',
        'version' => '1.0',
        'supported_fields' => ['traffic', 'keywords', 'pages', 'serp_rankings'],
        'status' => 'ready'
    ]
]);
```

### 3. Auto-Registration (Recommended)

Configure auto-registration in `config/brain.php`:

```php
'capabilities' => [
    'auto_register' => true,
    'check_on_boot' => true,
    'cache_ttl' => 3600,
],

// Define your capabilities
'capabilities' => [
    'list' => [
        'seo_snapshot' => [
            'version' => '1.0',
            'supported_fields' => ['traffic', 'keywords', 'pages', 'serp_rankings'],
            'status' => 'ready',
        ],
    ],
],
```

The service provider will automatically register these on boot.

## Sending Data

### Validate Before Sending

Always validate data before sending:

```php
$brain = app(BrainEventClient::class);

// Validate locally first
$errors = $brain->validateData('seo_snapshot', $snapshotData);
if (!empty($errors)) {
    // Handle validation errors
    Log::warning('Data validation failed', ['errors' => $errors]);
    return;
}

// Send data (validates again server-side)
$result = $brain->sendData('seo_snapshot', $snapshotData);
```

### Using Capability Registry

Check if you have a capability before including optional data:

```php
use Brain\Client\CapabilityRegistry;

$registry = app(CapabilityRegistry::class);

$snapshotData = [
    'site' => 'example.com',
    'site_url' => 'https://example.com',
    'snapshot_name' => 'Monthly Report',
    'captured_at' => now()->toIso8601String(),
    'traffic' => [...],
];

// Only include SERP rankings if capability is registered
if ($registry->hasCapability('serp_rankings')) {
    $snapshotData['serp_rankings'] = $this->getSerpRankings();
}

$brain->sendData('seo_snapshot', $snapshotData);
```

## API Methods

### `getConfig(): ?array`

Get Brain's configuration including available data types and your current capabilities.

**Returns:**
```php
[
    'data_types' => [
        [
            'name' => 'seo_snapshot',
            'version' => '1.0',
            'schema' => [...],
            'endpoint' => '/api/v1/seo-snapshot',
            'method' => 'POST',
            'required' => false,
            'priority' => 'high',
            'setup_instructions' => '...',
            'example_payload' => [...],
        ],
    ],
    'client_capabilities' => [
        [
            'data_type' => 'seo_snapshot',
            'status' => 'active',
            'version' => '1.0',
        ],
    ],
    'recommendations' => [
        [
            'data_type' => 'serp_rankings',
            'reason' => 'Would enhance SEO tracking',
            'priority' => 'medium',
        ],
    ],
]
```

### `registerCapabilities(array $capabilities): ?array`

Register your application's capabilities with Brain.

**Parameters:**
```php
[
    [
        'data_type' => 'seo_snapshot',
        'version' => '1.0',
        'supported_fields' => ['traffic', 'keywords', 'pages'],
        'status' => 'ready',
        'metadata' => [], // Optional
    ],
]
```

**Returns:**
```php
[
    'registered' => 1,
    'matched' => 1,
    'pending_approval' => 0,
    'results' => [
        [
            'data_type' => 'seo_snapshot',
            'status' => 'active',
            'matched' => true,
            'missing_fields' => [],
            'recommendations' => [],
        ],
    ],
]
```

### `getSchema(string $dataType): ?array`

Get JSON Schema for a specific data type.

**Returns:**
```php
[
    'name' => 'seo_snapshot',
    'version' => '1.0',
    'schema' => [...],
    'required_fields' => ['site', 'site_url', 'snapshot_name', 'captured_at', 'traffic'],
    'optional_fields' => ['keywords', 'pages', 'cannibalization', 'serp_rankings'],
    'example_payload' => [...],
]
```

### `sendData(string $dataType, array $data): ?array`

Send data for a specific data type. Validates before sending.

**Returns:** Response data from Brain or `null` on failure.

### `validateData(string $dataType, array $data): array`

Validate data locally against the data type's schema.

**Returns:** Array of validation errors (empty if valid).

## Capability Registry

The `CapabilityRegistry` class manages your registered capabilities:

```php
use Brain\Client\CapabilityRegistry;

$registry = app(CapabilityRegistry::class);

// Check if you have a capability
if ($registry->hasCapability('seo_snapshot')) {
    // Include SEO snapshot data
}

// Get a specific capability
$capability = $registry->getCapability('seo_snapshot');

// Get all capabilities
$all = $registry->getAll();
```

## Best Practices

1. **Always validate before sending** - Use `validateData()` to catch errors early
2. **Check capabilities before including optional data** - Use `CapabilityRegistry` to conditionally include data
3. **Handle validation errors gracefully** - Log warnings but don't break your application
4. **Use auto-registration** - Configure capabilities in `config/brain.php` for automatic registration
5. **Monitor recommendations** - Check Brain's recommendations and add missing capabilities when possible

## Troubleshooting

### Capability Not Found

If Brain returns "Data type not found", ensure:
- The data type name matches exactly (case-sensitive)
- The data type is active in Brain
- You're using the correct version

### Validation Errors

If validation fails:
- Check required fields are present
- Verify field types match the schema
- Review the example payload in Brain's config

### Auto-Registration Not Working

If capabilities aren't auto-registering:
- Check `config/brain.php` has `auto_register => true`
- Verify `BRAIN_AUTO_REGISTER_CAPABILITIES` env var is set
- Check application logs for errors
- Ensure Brain API key is configured correctly

## Example: Complete Integration

```php
<?php

namespace App\Providers;

use Brain\Client\BrainEventClient;
use Brain\Client\CapabilityRegistry;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function() {
            $brain = app(BrainEventClient::class);
            $registry = app(CapabilityRegistry::class);
            
            // Get Brain's configuration
            $config = $brain->getConfig();
            
            if ($config) {
                // Check for required capabilities
                $requiredTypes = array_filter(
                    $config['data_types'] ?? [],
                    fn($dt) => $dt['required'] ?? false
                );
                
                foreach ($requiredTypes as $dataType) {
                    if (!$registry->hasCapability($dataType['name'])) {
                        Log::warning('Missing required capability', [
                            'data_type' => $dataType['name'],
                        ]);
                    }
                }
            }
        });
    }
}
```

## Configuration Reference

```php
// config/brain.php
'capabilities' => [
    // Automatically register capabilities on boot
    'auto_register' => env('BRAIN_AUTO_REGISTER_CAPABILITIES', true),
    
    // Check Brain config on boot
    'check_on_boot' => env('BRAIN_CHECK_CAPABILITIES_ON_BOOT', true),
    
    // Cache TTL for Brain config and schemas (seconds)
    'cache_ttl' => env('BRAIN_CONFIG_CACHE_TTL', 3600),
    
    // Define your capabilities
    'list' => [
        'seo_snapshot' => [
            'version' => '1.0',
            'supported_fields' => ['traffic', 'keywords', 'pages', 'serp_rankings'],
            'status' => 'ready',
        ],
    ],
],
```

