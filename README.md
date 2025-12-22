# Brain Nucleus Client for Laravel

Official PHP client for sending events to [Brain Nucleus](https://again.com.au) - monitoring, analytics, and incident management.

## Installation

```bash
composer require brain-nucleus/client
```

## Configuration

Add to your `.env`:

```env
BRAIN_BASE_URL=https://again.com.au
BRAIN_API_KEY=your-api-key-here
```

Add to `config/services.php`:

```php
'brain' => [
    'base_url' => env('BRAIN_BASE_URL'),
    'api_key' => env('BRAIN_API_KEY'),
],
```

## Usage

### Basic Usage

```php
use Brain\Client\BrainEventClient;

$client = new BrainEventClient(
    config('services.brain.base_url'),
    config('services.brain.api_key')
);

// Send an event
$client->send('user.signup', [
    'email' => $user->email,
    'name' => $user->name,
]);
```

### Service Provider (Recommended)

Register as a singleton in `AppServiceProvider`:

```php
use Brain\Client\BrainEventClient;

public function register(): void
{
    $this->app->singleton(BrainEventClient::class, function ($app) {
        return new BrainEventClient(
            config('services.brain.base_url'),
            config('services.brain.api_key')
        );
    });
}
```

Then inject anywhere:

```php
public function __construct(private BrainEventClient $brain) {}

public function store()
{
    $this->brain->send('quote.created', ['amount' => 1000]);
}
```

### Async Events

Fire and forget (uses Laravel queues):

```php
$client->sendAsync('analytics.page_view', ['url' => '/dashboard']);
```

## Health Checks

Send heartbeats to let Brain know your app is alive:

```php
// In a scheduled command (every 5 minutes)
$client->send('health.ping', [
    'site' => config('app.name'),
    'environment' => config('app.env'),
]);
```

## Version Tracking

Brain tracks which client version each project uses:

```php
$client->getVersion();       // Returns "1.0.0"
$client->checkVersion();     // Checks if update is available
```

## License

MIT License. See [LICENSE](LICENSE) for details.
