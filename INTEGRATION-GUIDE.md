# Brain Event Client - Integration Guide

Advanced code patterns and examples for integrating Brain events into your
Laravel application.

> **Getting Started?** See [README.md](README.md) for installation.
> **Implementation Checklist?** See
> [IMPLEMENTATION-CHECKLIST.md](IMPLEMENTATION-CHECKLIST.md) for required
> implementations.

---

## Sending Events

### Basic Usage

Inject `BrainEventClient` via dependency injection:

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

### Using the Facade/Container

```php
// Via container
$brain = app(BrainEventClient::class);
$brain->send('user.signup', ['email' => $user->email]);

// In a job or event listener
app(BrainEventClient::class)->send('order.shipped', [...]);
```

### Async Events (Fire and Forget)

For non-critical events that shouldn't block the request:

```php
$brain->sendAsync('page.viewed', [
    'page' => request()->path(),
    'user_id' => auth()->id(),
]);
```

---

## Common Event Patterns

### User Events

```php
// Signup
$brain->send('user.signup', [
    'email' => $user->email,
    'name' => $user->name,
    'user_id' => $user->id,
]);

// Login
$brain->send('user.login', [
    'email' => $user->email,
    'user_id' => $user->id,
    'ip_address' => request()->ip(),
]);
```

### Order Events

```php
// Order Created
$brain->send('order.created', [
    'order_id' => $order->id,
    'amount' => $order->total,
    'email' => $order->customer_email,
    'items_count' => $order->items->count(),
]);

// Order Completed (with custom timestamp)
$brain->send('order.completed', [
    'order_id' => $order->id,
    'amount' => $order->total,
], $order->completed_at);
```

### Payment Events

```php
// Payment Successful
$brain->send('payment.success', [
    'order_id' => $order->id,
    'amount' => $payment->amount,
    'gateway' => 'stripe',
]);

// Payment Failed
$brain->send('payment.failed', [
    'order_id' => $order->id,
    'gateway' => 'stripe',
    'error' => $exception->getMessage(),
], [
    'severity' => 'error',
    'fingerprint' => 'payment.failed:stripe',
]);
```

---

## Event Payload Best Practices

### Include Identity Information

Brain automatically resolves identity from email/phone:

```php
$brain->send('user.signup', [
    'email' => $user->email,        // ✅ Used for identity resolution
    'phone' => $user->phone,        // ✅ Used for identity resolution
    'user_id' => $user->id,         // ✅ Your internal ID
    'name' => $user->name,
]);
```

### Use Descriptive Event Types

Follow dot-notation pattern:

- ✅ `user.signup`
- ✅ `order.completed`
- ✅ `payment.processed`
- ❌ `signup` (too generic)
- ❌ `order_done` (inconsistent)

---

## Events with Metadata

Pass a third argument for metadata (severity, fingerprint, context):

```php
$brain->send('error.external_api', [
    'api' => 'stripe',
    'error' => $e->getMessage(),
], [
    'severity' => 'error',
    'fingerprint' => 'error.external_api:stripe',
    'message' => 'Stripe API returned an error',
    'context' => [
        'endpoint' => '/v1/charges',
        'status_code' => 500,
    ],
]);
```

---

## Checking Configuration

Verify the client is configured before sending (useful in shared code):

```php
$brain = app(BrainEventClient::class);

if ($brain->isConfigured()) {
    $brain->send('custom.event', [...]);
}
```

---

## Testing

Mock the client in tests:

```php
use Brain\Client\BrainEventClient;
use Mockery;

$brain = Mockery::mock(BrainEventClient::class);
$brain->shouldReceive('send')
    ->once()
    ->with('user.signup', Mockery::type('array'));

app()->instance(BrainEventClient::class, $brain);
```

---

## Verification

After sending events, verify they appear in Brain admin:

1. Log into Brain admin: https://again.com.au/admin
2. Navigate to **Events**
3. Filter by event type or search by email
4. Click on an event to view the full JSON payload

---

## Troubleshooting

| Issue                     | Solution                                           |
| ------------------------- | -------------------------------------------------- |
| **401 Unauthorized**      | Check `BRAIN_API_KEY` is correct                   |
| **Events not appearing**  | Verify Brain queue worker (Horizon) is running     |
| **Slow requests**         | Use `sendAsync()` for non-critical events          |
| **No heartbeat in Brain** | Run `php artisan brain:heartbeat` manually to test |

---

## Custom Event Registration

Register site-specific events in `config/brain.php`:

```php
'events' => [
    'quote.custom_action' => 'Custom action performed on quote',
    'carrier.rate_received' => 'Rate received from transport carrier',
],
```

Events defined here are synced to Brain on each heartbeat.

---

## Additional Resources

- [OPS-EVENT-SPEC.md](OPS-EVENT-SPEC.md) - Complete ops event specification
- [EVENTS.md](EVENTS.md) - Business event definitions
- [IMPLEMENTATION-CHECKLIST.md](IMPLEMENTATION-CHECKLIST.md) - Required
  implementations
