<?php

/**
 * Simple Test Script for Brain Event Client
 * 
 * Usage: php brain-client/test-client.php
 */

require __DIR__ . '/../vendor/autoload.php';

// Simple Brain Event Client (standalone version for testing)
class SimpleBrainClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    public function send(string $eventType, array $payload, ?\DateTimeInterface $occurredAt = null): ?array
    {
        $ch = curl_init("{$this->baseUrl}/api/v1/events");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'X-Brain-Key: ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'event_type' => $eventType,
                'payload' => $payload,
                'occurred_at' => $occurredAt?->format('c'),
            ]),
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status >= 200 && $status < 300) {
            return json_decode($response, true);
        }

        return null;
    }
}

// Configuration - update these for your setup
$baseUrl = 'http://localhost:8000';
$apiKey = 'brain_GqOdeFbwZXRyg9OxeuVEDQV4saS41sA8cdFuzaPjuekEgiBs'; // From test project

echo "🧠 Brain Event Client Test\n";
echo "==========================\n\n";

$client = new SimpleBrainClient($baseUrl, $apiKey);

// Test: Send a user signup event
echo "📤 Sending user.signup event...\n";
$result = $client->send('user.signup', [
    'email' => 'test-client@example.com',
    'name' => 'Test Client User',
    'user_id' => 999,
    'signup_method' => 'email',
    'test' => true,
]);

if ($result) {
    echo "✅ Success!\n";
    echo "   Event ID: {$result['id']}\n";
    echo "   Status: {$result['status']}\n";
    echo "\n";
    echo "🔍 View in Filament: {$baseUrl}/admin/events/{$result['id']}\n";
    echo "\n";
    exit(0);
} else {
    echo "❌ Failed to send event\n";
    exit(1);
}

