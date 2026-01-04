# Brain Nucleus Integration for Static PHP Sites

Integrate event tracking in static PHP websites. No Composer or frameworks
required.

## Quick Setup (5 minutes)

### 1. Download the Client

```bash
# From your site's includes directory
curl -O https://raw.githubusercontent.com/iamjasonhill/thebrain/main/brain-client/BrainEventClient-standalone.php
```

Or copy manually from:
https://github.com/iamjasonhill/thebrain/blob/main/brain-client/BrainEventClient-standalone.php

### 2. Get Your API Key

1. Go to [Brain Admin](https://again.com.au/admin)
2. Navigate to **API Keys** → **Create**
3. Select your project and copy the key

### 3. Configure

Edit `BrainEventClient-standalone.php` at the bottom:

```php
$brainApiKey = 'your-api-key-here';
```

Or use environment variables (preferred for production):

```php
$brainApiKey = getenv('BRAIN_API_KEY') ?: '';
```

---

## Usage

### Include in Your PHP Files

```php
<?php
require_once __DIR__ . '/includes/BrainEventClient-standalone.php';
```

### Send Events

```php
// Simple event
brain_event('page.viewed', [
    'page' => $_SERVER['REQUEST_URI'],
    'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
]);

// Form submission
brain_event('contact.submitted', [
    'email' => $_POST['email'],
    'name' => $_POST['name'],
    'subject' => $_POST['subject'] ?? 'General Inquiry',
]);

// With options (severity, fingerprint)
brain_event('error.exception', [
    'message' => $e->getMessage(),
    'file' => basename($e->getFile()),
    'line' => $e->getLine(),
], [
    'severity' => 'error',
    'fingerprint' => 'error.exception:' . basename($e->getFile()),
]);
```

---

## Common Event Examples

### Contact Form

```php
// contact.php
<?php
require_once __DIR__ . '/includes/BrainEventClient-standalone.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form...
    
    // Track submission
    brain_event('contact.submitted', [
        'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
        'name' => htmlspecialchars($_POST['name']),
        'page' => $_SERVER['REQUEST_URI'],
    ]);
}
```

### Error Tracking

```php
// In your error handler or catch blocks
set_exception_handler(function (Throwable $e) {
    brain_event('error.exception', [
        'message' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'trace' => array_slice($e->getTrace(), 0, 5),
    ], [
        'severity' => 'error',
        'fingerprint' => 'error.exception:' . get_class($e) . ':' . basename($e->getFile()),
    ]);
    
    // Show error page
    http_response_code(500);
    include 'error.html';
    exit;
});
```

### 404 Tracking

```php
// 404.php
<?php
require_once __DIR__ . '/includes/BrainEventClient-standalone.php';

brain_event('error.404', [
    'url' => $_SERVER['REQUEST_URI'],
    'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
]);
```

---

## File Structure

```
your-site/
├── includes/
│   └── BrainEventClient-standalone.php   ← Place here
├── index.php
├── contact.php
└── ...
```

---

## Options Reference

The third parameter accepts these options:

| Option        | Type            | Description                               |
| ------------- | --------------- | ----------------------------------------- |
| `severity`    | string          | `info`, `warning`, `error`, or `critical` |
| `fingerprint` | string          | Groups similar events into incidents      |
| `message`     | string          | Human-readable summary                    |
| `context`     | array           | Additional structured metadata            |
| `occurred_at` | string/DateTime | When the event happened                   |

---

## Troubleshooting

### Events not appearing?

- Check your API key is correct
- Verify the Brain URL is reachable: `curl https://again.com.au/api/v1/health`
- Check PHP error logs for cURL errors

### Need to debug?

```php
$result = brain_event('test.event', ['test' => true]);
var_dump($result); // Should show ['id' => X, 'status' => 'accepted']
```

---

## WebForge Integration Notes

When scaffolding a static PHP site with WebForge:

1. Include `BrainEventClient-standalone.php` in the `includes/` directory
2. Add `require_once` to your main include/bootstrap file
3. Pre-configure with placeholder:
   `$brainApiKey = getenv('BRAIN_API_KEY') ?: '';`
4. Add `.env` template with `BRAIN_API_KEY=` entry
5. Track contact form submissions by default

---

## Links

- **Brain Admin**: https://again.com.au/admin
- **Client Source**:
  https://github.com/iamjasonhill/thebrain/blob/main/brain-client/BrainEventClient-standalone.php
- **Full Documentation**:
  https://github.com/iamjasonhill/thebrain/tree/main/brain-client
