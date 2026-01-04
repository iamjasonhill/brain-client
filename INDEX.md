# Brain Event Client Package

Official client for sending events to Brain Nucleus from Laravel applications.

## 📚 Documentation

| Document                                                       | Purpose                                                   |
| -------------------------------------------------------------- | --------------------------------------------------------- |
| **[README.md](README.md)**                                     | **START HERE** - Quick start guide (3-step install)       |
| **[IMPLEMENTATION-CHECKLIST.md](IMPLEMENTATION-CHECKLIST.md)** | Required implementations (error handling, queue failures) |
| **[INTEGRATION-GUIDE.md](INTEGRATION-GUIDE.md)**               | Advanced code patterns and examples                       |
| **[OPS-EVENT-SPEC.md](OPS-EVENT-SPEC.md)**                     | Ops event specification (health, error, queue events)     |
| **[EVENTS.md](EVENTS.md)**                                     | Business event definitions (quotes, payments, etc.)       |

## 🚀 Quick Start

```bash
# 1. Install
composer require brain-nucleus/client:dev-main

# 2. Configure (.env)
BRAIN_BASE_URL=https://again.com.au
BRAIN_API_KEY=your-api-key

# 3. Done! Heartbeat auto-schedules.
```

## 📦 Package Contents

### Source Files

- **`src/BrainEventClient.php`** - Main client class
- **`src/BrainNucleusClientServiceProvider.php`** - Laravel service provider
- **`src/Console/Commands/SendBrainHeartbeat.php`** - Heartbeat command

### Standalone (Non-Composer)

- **`BrainEventClient-standalone.php`** - For static PHP sites (no dependencies)
- **`STATIC-PHP-INTEGRATION.md`** - Standalone integration guide

## ✅ What's Automatic

- Heartbeat (`health.ping`) every 5 minutes
- `BrainEventClient` singleton for dependency injection
- `brain:heartbeat` artisan command

## ⚠️ What You Must Implement

- Error exception handling (`error.exception`)
- Queue failed job handling (`queue.failed`)

See [IMPLEMENTATION-CHECKLIST.md](IMPLEMENTATION-CHECKLIST.md) for code
examples.

## Requirements

- Laravel 10+ (auto-discovery)
- PHP 8.1+

## License

MIT - Use freely in your Laravel applications.
