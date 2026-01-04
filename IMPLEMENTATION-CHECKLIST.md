# Brain Nucleus Implementation Checklist

After installing the Brain client package, use this checklist to implement the
required monitoring features.

> **Note:** Heartbeat (`health.ping`) is handled automatically by the package.
> You don't need to implement it manually.

---

## ✅ What's Already Done (Automatic)

The `brain-nucleus/client` package automatically provides:

| Feature                        | Status                            |
| ------------------------------ | --------------------------------- |
| **Heartbeat (`health.ping`)**  | ✅ Auto-scheduled every 5 minutes |
| **BrainEventClient singleton** | ✅ Ready for dependency injection |
| **`brain:heartbeat` command**  | ✅ Available for manual testing   |

You do **not** need to create a `SendHealthCheck.php` command or schedule
anything for heartbeats.

---

## ⚠️ Required Implementations

You **must** implement these two features manually:

### 1. Error Exception Handling (`error.exception`)

**Purpose:** Capture unhandled exceptions and send them to Brain for alerting.

**File:** `bootstrap/app.php` (Laravel 11+) or `app/Exceptions/Handler.php`
(Laravel 10)

**Laravel 11+ Implementation:**

```php
// bootstrap/app.php
use Brain\Client\BrainEventClient;
use Illuminate\Foundation\Configuration\Exceptions;

return Application::configure(basePath: dirname(__DIR__))
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (Throwable $e) {
            // Skip if Brain not configured
            $client = app(BrainEventClient::class);
            if (!$client->isConfigured()) {
                return;
            }

            // Skip common non-error exceptions
            if ($e instanceof \Illuminate\Auth\AuthenticationException ||
                $e instanceof \Illuminate\Validation\ValidationException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return;
            }

            $client->send('error.exception', [
                'message' => $e->getMessage(),
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], [
                'severity' => 'error',
                'fingerprint' => sprintf(
                    'error.exception:%s:%s:%d',
                    class_basename($e),
                    basename($e->getFile()),
                    $e->getLine()
                ),
            ]);
        });
    })
    ->create();
```

**Laravel 10 Implementation:**

```php
// app/Exceptions/Handler.php
use Brain\Client\BrainEventClient;

public function register(): void
{
    $this->reportable(function (Throwable $e) {
        $client = app(BrainEventClient::class);
        if (!$client->isConfigured()) {
            return;
        }

        // Skip common non-error exceptions
        if ($e instanceof \Illuminate\Auth\AuthenticationException ||
            $e instanceof \Illuminate\Validation\ValidationException ||
            $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return;
        }

        $client->send('error.exception', [
            'message' => $e->getMessage(),
            'exception_class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ], [
            'severity' => 'error',
            'fingerprint' => sprintf(
                'error.exception:%s:%s:%d',
                class_basename($e),
                basename($e->getFile()),
                $e->getLine()
            ),
        ]);
    });
}
```

**Verification:**

```bash
# Create a test route that throws an exception
# Route::get('/test-error', fn() => throw new \Exception('Test error'));
# Visit the route, then check Brain admin for error.exception event
```

---

### 2. Queue Failed Job Handling (`queue.failed`)

**Purpose:** Capture failed queue jobs and send them to Brain for alerting.

**File:** `app/Providers/AppServiceProvider.php`

**Implementation:**

```php
use Brain\Client\BrainEventClient;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Event;

public function boot(): void
{
    Event::listen(JobFailed::class, function (JobFailed $event) {
        $client = app(BrainEventClient::class);
        if (!$client->isConfigured()) {
            return;
        }

        $jobName = $event->job->resolveName();

        $client->send('queue.failed', [
            'job' => $jobName,
            'connection' => $event->connectionName,
            'queue' => $event->job->getQueue(),
            'exception' => $event->exception->getMessage(),
        ], [
            'severity' => 'error',
            'fingerprint' => 'queue.failed:' . class_basename($jobName),
            'message' => "Queue job failed: {$jobName}",
        ]);
    });
}
```

**Verification:**

```bash
# Create a test job that fails
php artisan make:job TestFailingJob

# In the job's handle() method: throw new \Exception('Test failure');
# Dispatch: dispatch(new \App\Jobs\TestFailingJob());
# Check Brain admin for queue.failed event
```

---

## 📋 Checklist

Use this checklist when integrating a new project:

### Required

- [ ] Package installed via Composer
- [ ] Environment variables set (`BRAIN_BASE_URL`, `BRAIN_API_KEY`)
- [ ] Heartbeat verified (run `php artisan brain:heartbeat`)
- [ ] Error exception handling implemented
- [ ] Error exception tested (trigger test exception, verify in Brain)
- [ ] Queue failed handling implemented
- [ ] Queue failed tested (trigger failed job, verify in Brain)

### Optional (Recommended)

- [ ] Payment failed handling (`payment.failed` events)
- [ ] Third-party API monitoring (`integration.down` events)
- [ ] Deploy tracking (`deploy.completed` events)

---

## 📝 Fingerprint Best Practices

**Fingerprints determine how events are grouped into incidents.** Follow these
rules:

### ✅ Good Fingerprints (Stable, Low-Cardinality)

- `error.exception:ValidationException:QuoteController.php:45`
- `queue.failed:QuoteFinaliseJob`
- `payment.failed:stripe`

### ❌ Bad Fingerprints (Unique Per Occurrence)

- `quote-12345-failed` (includes ID)
- `user-john@example.com-error` (includes email)
- `error-2025-12-13-10-30-45` (includes timestamp)

**Rule:** Fingerprints should identify the _type_ of issue, not the specific
_instance_.

---

## 📚 Additional Resources

- [INTEGRATION-GUIDE.md](INTEGRATION-GUIDE.md) - Advanced code patterns
- [OPS-EVENT-SPEC.md](OPS-EVENT-SPEC.md) - Complete event specification
