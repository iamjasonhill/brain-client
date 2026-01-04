<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Brain Nucleus Connection
    |--------------------------------------------------------------------------
    |
    | Configure your connection to Brain Nucleus. These values can also
    | be set via the services.brain config for backwards compatibility.
    |
    */

    'base_url' => env('BRAIN_BASE_URL'),
    'api_key' => env('BRAIN_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Heartbeat Configuration
    |--------------------------------------------------------------------------
    |
    | The heartbeat sends periodic health.ping events to Brain Nucleus,
    | allowing it to monitor the health of your application.
    |
    */

    'heartbeat' => [
        // Enable automatic heartbeat scheduling
        'enabled' => env('BRAIN_HEARTBEAT_ENABLED', true),

        // Heartbeat interval in minutes (default: every 5 minutes)
        'interval' => env('BRAIN_HEARTBEAT_INTERVAL', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Events
    |--------------------------------------------------------------------------
    |
    | Define site-specific events here. These will be automatically
    | registered with Brain Nucleus on each heartbeat.
    |
    | Format: 'event.type' => 'Description of what this event means'
    |
    | Example:
    | 'events' => [
    |     'carrier.rate_received' => 'Rate received from transport carrier',
    |     'quote.manual_override' => 'Quote manually adjusted by staff',
    | ],
    |
    */

    'events' => [
        // Add your site-specific events here
    ],

    /*
    |--------------------------------------------------------------------------
    | Capabilities Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how the client handles capability registration and discovery.
    |
    */

    'capabilities' => [
        // Automatically register capabilities on application boot
        'auto_register' => env('BRAIN_AUTO_REGISTER_CAPABILITIES', true),

        // Check Brain config on application boot
        'check_on_boot' => env('BRAIN_CHECK_CAPABILITIES_ON_BOOT', true),

        // Cache TTL for Brain config and schemas (in seconds)
        'cache_ttl' => env('BRAIN_CONFIG_CACHE_TTL', 3600),
    ],
];
