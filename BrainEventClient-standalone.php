<?php

/**
 * Brain Event Client - Standalone Version
 * 
 * Standalone client for static PHP websites. Only requires PHP 8.1+ and cURL.
 * No Composer or Laravel dependencies needed.
 * 
 * INSTALLATION:
 * 1. Copy this file to your project (e.g., includes/BrainEventClient.php)
 * 2. Set your credentials at the bottom of this file or in your config
 * 
 * USAGE:
 *   require_once 'BrainEventClient.php';
 *   
 *   // Simple event
 *   brain_event('contact.submitted', ['email' => 'user@example.com']);
 *   
 *   // With options (severity, fingerprint, etc.)
 *   brain_event('error.exception', ['message' => 'Something broke'], [
 *       'severity' => 'error',
 *       'fingerprint' => 'error.exception:MyPage',
 *   ]);
 */

class BrainEventClient
{
    public const VERSION = '1.1.0';

    private string $baseUrl;
    private string $apiKey;
    private static ?self $instance = null;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    /**
     * Get singleton instance (for helper function)
     */
    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    /**
     * Set singleton instance
     */
    public static function setInstance(self $client): void
    {
        self::$instance = $client;
    }

    /**
     * Send an event to Brain Nucleus
     * 
     * @param string $eventType The type of event (e.g., 'contact.submitted')
     * @param array $payload Event data
     * @param array|null $options Optional: severity, fingerprint, message, context, occurred_at
     * @return array|null Response data with 'id' and 'status', or null on failure
     */
    public function send(string $eventType, array $payload, ?array $options = null): ?array
    {
        $data = [
            'event_type' => $eventType,
            'payload' => $payload,
        ];

        // Merge options if provided
        if ($options !== null) {
            if (isset($options['occurred_at'])) {
                $occurredAt = $options['occurred_at'];
                if ($occurredAt instanceof \DateTimeInterface) {
                    $data['occurred_at'] = $occurredAt->format('c');
                } else {
                    $data['occurred_at'] = $occurredAt;
                }
                unset($options['occurred_at']);
            }

            foreach (['severity', 'fingerprint', 'message', 'context'] as $key) {
                if (isset($options[$key])) {
                    $data[$key] = $options[$key];
                }
            }
        }

        $ch = curl_init("{$this->baseUrl}/api/v1/events");

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'X-Brain-Key: ' . $this->apiKey,
                'X-Brain-Client-Version: ' . self::VERSION,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Brain event error: {$error}");
            return null;
        }

        if ($status >= 200 && $status < 300) {
            return json_decode($response, true);
        }

        error_log("Brain event failed: HTTP {$status} - {$response}");
        return null;
    }
}

/**
 * Helper function - send an event to Brain
 * 
 * @param string $eventType Event type (e.g., 'contact.submitted')
 * @param array $payload Event data
 * @param array|null $options Optional: severity, fingerprint, message, context
 * @return array|null
 */
function brain_event(string $eventType, array $payload, ?array $options = null): ?array
{
    $client = BrainEventClient::getInstance();
    if ($client === null) {
        error_log('Brain client not configured. Call BrainEventClient::setInstance() first.');
        return null;
    }
    return $client->send($eventType, $payload, $options);
}

// ============================================================================
// CONFIGURATION - Edit these values for your site
// ============================================================================

// Option 1: Set credentials here (simple)
$brainBaseUrl = getenv('BRAIN_BASE_URL') ?: 'https://again.com.au';
$brainApiKey = getenv('BRAIN_API_KEY') ?: '';  // Set your API key here or via env

if (!empty($brainApiKey)) {
    BrainEventClient::setInstance(new BrainEventClient($brainBaseUrl, $brainApiKey));
}

// Option 2: Or configure in your own bootstrap file:
// require_once 'BrainEventClient.php';
// BrainEventClient::setInstance(new BrainEventClient('https://again.com.au', 'your-key'));
