<?php

/**
 * Test Integration Script
 * 
 * Run this script to test the Brain client integration:
 *   php brain-client/test-integration.php
 * 
 * Make sure to set BRAIN_BASE_URL and BRAIN_API_KEY environment variables.
 */

require __DIR__ . '/../vendor/autoload.php';

use Brain\Client\BrainEventClient;

// Load environment (adjust path if needed)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$baseUrl = $_ENV['BRAIN_BASE_URL'] ?? 'http://localhost:8000';
$apiKey = $_ENV['BRAIN_API_KEY'] ?? '';

if (empty($apiKey)) {
    echo "❌ Error: BRAIN_API_KEY not set in .env\n";
    echo "   Get an API key from: {$baseUrl}/admin/api-keys\n";
    exit(1);
}

echo "🧠 Testing Brain Event Client\n";
echo "   Base URL: {$baseUrl}\n";
echo "   API Key: " . substr($apiKey, 0, 10) . "...\n\n";

$client = new BrainEventClient($baseUrl, $apiKey);

// Test 1: User signup event
echo "📤 Sending user.signup event...\n";
$result = $client->send('user.signup', [
    'email' => 'test@example.com',
    'name' => 'Test User',
    'user_id' => 123,
    'signup_method' => 'email',
]);

if ($result) {
    echo "✅ Event sent successfully!\n";
    echo "   Event ID: {$result['id']}\n";
    echo "   Status: {$result['status']}\n";
    echo "\n";
    echo "🔍 View in Filament: {$baseUrl}/admin/events/{$result['id']}\n";
} else {
    echo "❌ Failed to send event. Check logs for details.\n";
    exit(1);
}

// Test 2: Order completed event
echo "📤 Sending order.completed event...\n";
$result2 = $client->send('order.completed', [
    'order_id' => 'ORD-12345',
    'amount' => 99.99,
    'currency' => 'USD',
    'email' => 'customer@example.com',
    'items' => ['SKU-001', 'SKU-002'],
], new DateTime('2025-12-13 10:30:00'));

if ($result2) {
    echo "✅ Event sent successfully!\n";
    echo "   Event ID: {$result2['id']}\n";
    echo "   Status: {$result2['status']}\n";
    echo "\n";
    echo "🔍 View in Filament: {$baseUrl}/admin/events/{$result2['id']}\n";
} else {
    echo "❌ Failed to send event.\n";
}

echo "\n✨ Integration test complete!\n";

