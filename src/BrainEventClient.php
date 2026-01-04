<?php

namespace Brain\Client;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Brain Event Client for Laravel
 *
 * Official client for sending events to Brain Nucleus.
 *
 * Usage:
 *   $client = app(BrainEventClient::class);
 *   $client->send('user.signup', ['email' => 'user@example.com']);
 *
 * With options (severity, fingerprint, etc.):
 *   $client->send('queue.failed', ['job_id' => 123], [
 *       'severity' => 'error',
 *       'fingerprint' => 'queue.failed:MyJob',
 *       'message' => 'Job failed',
 *   ]);
 */
class BrainEventClient
{
    public const CLIENT_VERSION = '1.2.0';

    private string $baseUrl;

    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    /**
     * Get the client version.
     */
    public function getVersion(): string
    {
        return self::CLIENT_VERSION;
    }

    /**
     * Send an event to Brain Nucleus
     *
     * @param  string  $eventType  The type of event (e.g., 'user.signup', 'order.completed')
     * @param  array<string, mixed>  $payload  Event data (will be stored as JSON)
     * @param  array<string, mixed>|null  $options  Optional: severity, fingerprint, message, context, occurred_at
     * @return array<string, mixed>|null Response data with 'id' and 'status', or null on failure
     */
    public function send(string $eventType, array $payload, ?array $options = null): ?array
    {
        try {
            $requestData = [
                'event_type' => $eventType,
                'payload' => $payload,
            ];

            // Merge options into request if provided
            if ($options !== null) {
                // Handle occurred_at - can be DateTimeInterface or string
                if (isset($options['occurred_at'])) {
                    $occurredAt = $options['occurred_at'];
                    if ($occurredAt instanceof \DateTimeInterface) {
                        $requestData['occurred_at'] = $occurredAt->format('c');
                    } else {
                        $requestData['occurred_at'] = $occurredAt;
                    }
                    unset($options['occurred_at']);
                }

                // Merge remaining options (severity, fingerprint, message, context)
                foreach (['severity', 'fingerprint', 'message', 'context'] as $key) {
                    if (isset($options[$key])) {
                        $requestData[$key] = $options[$key];
                    }
                }
            }

            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'X-Brain-Key' => $this->apiKey,
                'X-Brain-Client-Version' => self::CLIENT_VERSION,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/v1/events", $requestData);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Brain event send failed', [
                'event_type' => $eventType,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Brain event send exception', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Send an event asynchronously (fire and forget)
     *
     * @param  string  $eventType  The type of event
     * @param  array<string, mixed>  $payload  Event data
     * @param  array<string, mixed>|null  $options  Optional: severity, fingerprint, message, context, occurred_at
     */
    public function sendAsync(string $eventType, array $payload, ?array $options = null): void
    {
        dispatch(function () use ($eventType, $payload, $options) {
            $this->send($eventType, $payload, $options);
        });
    }

    /**
     * Check for client version updates.
     *
     * @return array<string, mixed>|null Response with 'latest_version', 'current_version', and 'update_required', or null on failure
     */
    public function checkVersion(): ?array
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::get("{$this->baseUrl}/api/v1/client/version");

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Brain version check failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Brain version check exception', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get Brain's configuration including available data types and current capabilities.
     * Results are cached for performance.
     *
     * @return array<string, mixed>|null Configuration data or null on failure
     */
    public function getConfig(): ?array
    {
        $cacheKey = "brain_config_{$this->apiKey}";
        $cacheTtl = config('brain.capabilities.cache_ttl', 3600);

        return Cache::remember($cacheKey, $cacheTtl, function () {
            try {
                /** @var \Illuminate\Http\Client\Response $response */
                $response = Http::withHeaders([
                    'X-Brain-Key' => $this->apiKey,
                    'X-Brain-Client-Version' => self::CLIENT_VERSION,
                    'Content-Type' => 'application/json',
                ])->get("{$this->baseUrl}/api/v1/client/config");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('Brain config fetch failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Brain config fetch exception', [
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    /**
     * Register client capabilities with Brain.
     *
     * @param  array<int, array<string, mixed>>  $capabilities  Array of capability definitions
     * @return array<string, mixed>|null Registration results or null on failure
     */
    public function registerCapabilities(array $capabilities): ?array
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'X-Brain-Key' => $this->apiKey,
                'X-Brain-Client-Version' => self::CLIENT_VERSION,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/api/v1/client/capabilities", [
                'capabilities' => $capabilities,
            ]);

            if ($response->successful()) {
                // Clear config cache after registration
                $cacheKey = "brain_config_{$this->apiKey}";
                Cache::forget($cacheKey);

                return $response->json();
            }

            Log::warning('Brain capability registration failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Brain capability registration exception', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get JSON Schema for a specific data type.
     *
     * @param  string  $dataType  Data type name (e.g., 'seo_snapshot')
     * @return array<string, mixed>|null Schema data or null on failure
     */
    public function getSchema(string $dataType): ?array
    {
        $cacheKey = "brain_schema_{$dataType}";
        $cacheTtl = config('brain.capabilities.cache_ttl', 3600);

        return Cache::remember($cacheKey, $cacheTtl, function () use ($dataType) {
            try {
                /** @var \Illuminate\Http\Client\Response $response */
                $response = Http::get("{$this->baseUrl}/api/v1/data-types/{$dataType}/schema");

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('Brain schema fetch failed', [
                    'data_type' => $dataType,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('Brain schema fetch exception', [
                    'data_type' => $dataType,
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    /**
     * Send data for a specific data type (validates before sending).
     *
     * @param  string  $dataType  Data type name (e.g., 'seo_snapshot')
     * @param  array<string, mixed>  $data  Data to send
     * @return array<string, mixed>|null Response data or null on failure
     */
    public function sendData(string $dataType, array $data): ?array
    {
        // Get schema and validate locally first
        $schema = $this->getSchema($dataType);
        if ($schema) {
            $errors = $this->validateData($dataType, $data);
            if (!empty($errors)) {
                Log::warning('Brain data validation failed', [
                    'data_type' => $dataType,
                    'errors' => $errors,
                ]);
                // Still send to server for server-side validation, but log the warning
            }
        }

        // Get endpoint from config or schema
        $config = $this->getConfig();
        $endpoint = null;

        if ($config && isset($config['data_types'])) {
            foreach ($config['data_types'] as $dt) {
                if ($dt['name'] === $dataType) {
                    $endpoint = $dt['endpoint'];
                    break;
                }
            }
        }

        if (!$endpoint) {
            Log::error('Brain data type endpoint not found', [
                'data_type' => $dataType,
            ]);
            return null;
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'X-Brain-Key' => $this->apiKey,
                'X-Brain-Client-Version' => self::CLIENT_VERSION,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}{$endpoint}", $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Brain data send failed', [
                'data_type' => $dataType,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Brain data send exception', [
                'data_type' => $dataType,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Validate data against a data type's schema locally.
     *
     * @param  string  $dataType  Data type name
     * @param  array<string, mixed>  $data  Data to validate
     * @return array<string, mixed> Validation errors (empty if valid)
     */
    public function validateData(string $dataType, array $data): array
    {
        $schema = $this->getSchema($dataType);
        if (!$schema) {
            return ['_schema' => 'Schema not found for data type'];
        }

        $errors = [];

        // Check required fields
        if (isset($schema['required_fields']) && is_array($schema['required_fields'])) {
            foreach ($schema['required_fields'] as $field) {
                if (!isset($data[$field])) {
                    $errors[$field] = "The {$field} field is required.";
                }
            }
        }

        // Basic type validation (can be enhanced with full JSON Schema validator)
        if (isset($schema['schema']) && is_array($schema['schema'])) {
            $schemaRules = $this->convertSchemaToRules($schema['schema']);
            $validator = \Illuminate\Support\Facades\Validator::make($data, $schemaRules);
            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->toArray());
            }
        }

        return $errors;
    }

    /**
     * Convert JSON Schema to Laravel validation rules.
     *
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function convertSchemaToRules(array $schema): array
    {
        $rules = [];

        if (isset($schema['properties'])) {
            foreach ($schema['properties'] as $field => $definition) {
                $fieldRules = [];

                if (isset($schema['required']) && in_array($field, $schema['required'])) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                if (isset($definition['type'])) {
                    switch ($definition['type']) {
                        case 'string':
                            $fieldRules[] = 'string';
                            if (isset($definition['maxLength'])) {
                                $fieldRules[] = 'max:' . $definition['maxLength'];
                            }
                            break;
                        case 'integer':
                        case 'number':
                            $fieldRules[] = 'numeric';
                            break;
                        case 'array':
                            $fieldRules[] = 'array';
                            break;
                        case 'boolean':
                            $fieldRules[] = 'boolean';
                            break;
                    }
                }

                $rules[$field] = $fieldRules;
            }
        }

        return $rules;
    }
}
