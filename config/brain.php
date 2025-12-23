<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Brain Nucleus Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your Brain Nucleus instance.
    |
    */
    'base_url' => env('BRAIN_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your API key for sending events to Brain. Get this from the Brain
    | admin panel when you create a service.
    |
    */
    'api_key' => env('BRAIN_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Service Secret
    |--------------------------------------------------------------------------
    |
    | Your service secret for making proxy calls through Brain. Get this
    | from the Brain admin panel when you create a service.
    |
    */
    'service_secret' => env('BRAIN_SERVICE_SECRET'),
];
