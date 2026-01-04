# Brain Nucleus Client for Laravel

Official client for sending events to Brain Nucleus from Laravel applications.

## 🚀 Quick Start

### 1. Install via Composer

```bash
composer require brain-nucleus/client:dev-main
```

> **Note:** First-time install? Add the repository to `composer.json`:
>
> ```json
> {
>     "repositories": [
>         {
>             "type": "vcs",
>             "url": "https://github.com/iamjasonhill/thebrain.git"
>         }
>     ]
> }
> ```

### 2. Add Environment Variables

```env
BRAIN_BASE_URL=https://again.com.au
BRAIN_API_KEY=your-api-key-here
```

> **Need an API key?** Log into [Brain Admin](https://again.com.au/admin) → API
> Keys → New API Key

### 3. Done! 🎉

That's it. The package auto-registers via Laravel's package discovery.

---

## ✅ What's Automatic (No Code Required)

| Feature       | Description                                       |
| ------------- | ------------------------------------------------- |
| **Heartbeat** | `health.ping` sent every 5 minutes automatically  |
| **Singleton** | `BrainEventClient` ready for dependency injection |
| **Command**   | `php artisan brain:heartbeat` for manual testing  |

---

## ⚠️ What You Need to Implement

The package handles heartbeats automatically, but you must implement:

1. **Error Exception Handling** → Send `error.exception` events
2. **Queue Failed Job Handling** → Send `queue.failed` events

📋 **See [IMPLEMENTATION-CHECKLIST.md](IMPLEMENTATION-CHECKLIST.md) for complete
code examples.**

---

## 💻 Sending Events

Inject `BrainEventClient` anywhere in your application:

```php
use Brain\Client\BrainEventClient;

class QuoteController extends Controller
{
    public function store(Request $request, BrainEventClient $brain)
    {
        $quote = Quote::create($request->validated());
        
        $brain->send('quote.created', [
            'quote_id' => $quote->id,
            'email' => $quote->email,
            'amount' => $quote->total,
        ]);
        
        return response()->json($quote);
    }
}
```

### Async Events (Fire and Forget)

For non-blocking events:

```php
$brain->sendAsync('page.viewed', [
    'page' => request()->path(),
    'user_id' => auth()->id(),
]);
```

---

## ⚙️ Configuration (Optional)

Publish the config file to customise behaviour:

```bash
php artisan vendor:publish --tag=brain-config
```

**config/brain.php:**

```php
return [
    'base_url' => env('BRAIN_BASE_URL'),
    'api_key' => env('BRAIN_API_KEY'),
    
    'heartbeat' => [
        'enabled' => true,  // Set false to disable auto-heartbeat
        'interval' => 5,    // Minutes between heartbeats
    ],
    
    // Register site-specific custom events
    'events' => [
        'carrier.rate_received' => 'Rate received from transport carrier',
        'quote.manual_override' => 'Quote manually adjusted by staff',
    ],
];
```

---

## 🔄 Updating the Client

```bash
composer update brain-nucleus/client
```

---

## 📚 Documentation

| Document                                                   | Purpose                                                   |
| ---------------------------------------------------------- | --------------------------------------------------------- |
| [IMPLEMENTATION-CHECKLIST.md](IMPLEMENTATION-CHECKLIST.md) | Required implementations (error handling, queue failures) |
| [INTEGRATION-GUIDE.md](INTEGRATION-GUIDE.md)               | Advanced code patterns and examples                       |
| [OPS-EVENT-SPEC.md](OPS-EVENT-SPEC.md)                     | Event specifications and fingerprinting                   |
| [EVENTS.md](EVENTS.md)                                     | Business event definitions                                |

---

## 🔗 Quick Links

- **Brain Admin:** https://again.com.au/admin
- **Health Check:** https://again.com.au/api/v1/health
- **Repository:** https://github.com/iamjasonhill/thebrain
