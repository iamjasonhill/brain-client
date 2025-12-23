<?php

namespace Brain\Client;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Brain Service Client for Laravel
 *
 * Use this client to communicate with other services through Brain's service gateway.
 *
 * Usage:
 *   $client = new BrainServiceClient('https://brain.example.com', 'brn_svc_xxx');
 *   $response = $client->get('domain-monitor', 'api/health');
 *   $response = $client->post('webforge', 'api/scaffolds', ['platform' => 'laravel']);
 */
class BrainServiceClient
{
    public const CLIENT_VERSION = '1.1.0';

    private string $baseUrl;

    private string $serviceSecret;

    private int $timeout = 30;

    public function __construct(string $baseUrl, string $serviceSecret)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->serviceSecret = $serviceSecret;
    }

    /**
     * Set request timeout in seconds.
     */
    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    /**
     * Make a GET request to another service through Brain.
     *
     * @param  string  $targetService  The service name (e.g., 'domain-monitor')
     * @param  string  $path  The path to call (e.g., 'api/domains')
     * @param  array<string, mixed>  $query  Query parameters
     * @return array<string, mixed>|null Response data, or null on failure
     */
    public function get(string $targetService, string $path, array $query = []): ?array
    {
        return $this->proxy('GET', $targetService, $path, $query);
    }

    /**
     * Make a POST request to another service through Brain.
     *
     * @param  string  $targetService  The service name
     * @param  string  $path  The path to call
     * @param  array<string, mixed>  $data  Request body
     * @return array<string, mixed>|null Response data, or null on failure
     */
    public function post(string $targetService, string $path, array $data = []): ?array
    {
        return $this->proxy('POST', $targetService, $path, $data);
    }

    /**
     * Make a PUT request to another service through Brain.
     *
     * @param  string  $targetService  The service name
     * @param  string  $path  The path to call
     * @param  array<string, mixed>  $data  Request body
     * @return array<string, mixed>|null Response data, or null on failure
     */
    public function put(string $targetService, string $path, array $data = []): ?array
    {
        return $this->proxy('PUT', $targetService, $path, $data);
    }

    /**
     * Make a PATCH request to another service through Brain.
     *
     * @param  string  $targetService  The service name
     * @param  string  $path  The path to call
     * @param  array<string, mixed>  $data  Request body
     * @return array<string, mixed>|null Response data, or null on failure
     */
    public function patch(string $targetService, string $path, array $data = []): ?array
    {
        return $this->proxy('PATCH', $targetService, $path, $data);
    }

    /**
     * Make a DELETE request to another service through Brain.
     *
     * @param  string  $targetService  The service name
     * @param  string  $path  The path to call
     * @param  array<string, mixed>  $data  Request body
     * @return array<string, mixed>|null Response data, or null on failure
     */
    public function delete(string $targetService, string $path, array $data = []): ?array
    {
        return $this->proxy('DELETE', $targetService, $path, $data);
    }

    /**
     * Make a proxied request to another service through Brain.
     *
     * @param  string  $method  HTTP method
     * @param  string  $targetService  The service name
     * @param  string  $path  The path to call
     * @param  array<string, mixed>  $data  Request data
     * @return array<string, mixed>|null Response data, or null on failure
     */
    public function proxy(string $method, string $targetService, string $path, array $data = []): ?array
    {
        $url = "{$this->baseUrl}/api/v1/proxy/{$targetService}/" . ltrim($path, '/');

        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                'X-Brain-Service-Secret' => $this->serviceSecret,
                'X-Brain-Client-Version' => self::CLIENT_VERSION,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->{strtolower($method)}($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Brain proxy request failed', [
                'target' => $targetService,
                'path' => $path,
                'method' => $method,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Brain proxy request exception', [
                'target' => $targetService,
                'path' => $path,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get the client version.
     */
    public function getVersion(): string
    {
        return self::CLIENT_VERSION;
    }
}
