# Brain Ops Event Specification (v1.0)

## Purpose

This document defines the standard events that client systems should emit to the
Brain for health monitoring, error detection, and incident alerting.

**This is not a logging API.**\
Only send events that represent meaningful system signals.

---

## 1. Event Structure

Each event sent to the Brain should include:

| Field         | Required       | Description                                                                     |
| ------------- | -------------- | ------------------------------------------------------------------------------- |
| `event_type`  | ✅             | Namespaced event name (see below)                                               |
| `payload`     | ✅             | Event data (JSON object)                                                        |
| `severity`    | ⚠️ Recommended | `info`, `warning`, `error`, `critical` (auto-inferred if missing)               |
| `fingerprint` | ⚠️ Recommended | Stable identifier used for grouping (auto-generated if missing)                 |
| `message`     | Optional       | Short human-readable summary (auto-generated if missing)                        |
| `context`     | Optional       | Small JSON object with useful metadata (auto-extracted from payload if missing) |
| `occurred_at` | Optional       | When the issue occurred (ISO-8601, defaults to now)                             |
| `request_id`  | Optional       | Trace/correlation ID if available                                               |
| `trace_id`    | Optional       | Distributed trace ID if available                                               |

**Note**: While `severity` and `fingerprint` are auto-generated if not provided,
**we strongly recommend providing them explicitly** for accurate incident
grouping and alerting.

---

## 2. Naming Convention

### Format

`<domain>.<action>`

### Rules

- **lowercase**
- **dot-separated**
- **stable over time**
- **do not encode severity into the name**

### Examples

✅ **Good:**

- `health.ping`
- `error.exception`
- `queue.failed`
- `payment.failed`
- `integration.timeout`

❌ **Bad:**

- `ErrorException` (camelCase)
- `error_exception` (snake_case)
- `critical.error` (severity in name)

---

## 3. Severity Levels

| Severity   | Meaning                        | Should Alert?       |
| ---------- | ------------------------------ | ------------------- |
| `info`     | Normal operational signal      | ❌                  |
| `warning`  | Degraded but still functioning | ❌ (dashboard only) |
| `error`    | Core functionality broken      | ✅                  |
| `critical` | Site down or revenue impact    | 🚨 Immediate        |

Severity may be escalated later by the Brain based on frequency or spread.

---

## 4. Fingerprint Rules (VERY IMPORTANT)

The fingerprint determines how events are grouped into incidents.

### Good Fingerprints

- ✅ **Stable** - Same issue = same fingerprint
- ✅ **Low-cardinality** - Not unique per occurrence
- ✅ **Based on type, not instance**

### Bad Fingerprints

- ❌ IDs (quote_id, user_id)
- ❌ Emails, names, addresses
- ❌ Stack traces or messages
- ❌ Timestamps or unique identifiers

### Examples

✅ **Good:**

```
queue-failed:QuoteFinaliseJob
unhandled-exception:IntegrityConstraintViolation
payment-failed:stripe-charge
error.exception:ValidationException:app/Http/Controllers/QuoteController.php:45
```

❌ **Bad:**

```
quote-12345-failed
user-john@example.com-error
sql-error-at-line-827
error-2025-12-13-10-30-45
```

---

## 5. MVP REQUIRED EVENTS (All Clients)

### 5.1 health.ping

**Purpose:** Indicates the app is alive and scheduled tasks are running.

| Field         | Value                                        |
| ------------- | -------------------------------------------- |
| `event_type`  | `health.ping`                                |
| `severity`    | `info`                                       |
| `fingerprint` | `health.ping` (or `health.ping:{site_name}`) |
| `message`     | Optional ("ok" or site name)                 |

**When to emit:**

- Every 5 minutes via scheduler/cron

**Example:**

```json
{
  "event_type": "health.ping",
  "severity": "info",
  "fingerprint": "health.ping",
  "message": "ok",
  "payload": {
    "site": "moveroo-web",
    "version": "1.2.3"
  }
}
```

---

### 5.2 error.exception

**Purpose:** Unhandled or fatal application exception.

| Field         | Value                                                                                  |
| ------------- | -------------------------------------------------------------------------------------- |
| `event_type`  | `error.exception`                                                                      |
| `severity`    | `error` or `critical`                                                                  |
| `fingerprint` | `error.exception:{ExceptionClass}` or `error.exception:{ExceptionClass}:{file}:{line}` |
| `message`     | Exception message (short)                                                              |

**Context suggestions:**

- `route` / `endpoint`
- `job_class` (if background job)
- `exception_class`
- `file` and `line` (basename only, not full path)

**Example:**

```json
{
  "event_type": "error.exception",
  "severity": "error",
  "fingerprint": "error.exception:ValidationException:QuoteController.php:45",
  "message": "Validation failed for quote creation",
  "context": {
    "exception_class": "ValidationException",
    "file": "QuoteController.php",
    "line": 45,
    "route": "POST /api/quotes"
  },
  "payload": {
    "exception": "Validation failed",
    "errors": {...}
  }
}
```

---

### 5.3 queue.failed

**Purpose:** A background job failed permanently.

| Field         | Value                     |
| ------------- | ------------------------- |
| `event_type`  | `queue.failed`            |
| `severity`    | `error`                   |
| `fingerprint` | `queue.failed:{JobClass}` |
| `message`     | Failure reason            |

**Context suggestions:**

- `job_class`
- `queue_name`
- `attempts`

**Example:**

```json
{
  "event_type": "queue.failed",
  "severity": "error",
  "fingerprint": "queue.failed:QuoteFinaliseJob",
  "message": "Quote finalisation job failed after 3 attempts",
  "context": {
    "job_class": "QuoteFinaliseJob",
    "queue_name": "default",
    "attempts": 3
  },
  "payload": {
    "job_id": "uuid-here",
    "exception": "Database connection timeout"
  }
}
```

---

## 6. OPTIONAL (Recommended Later)

### payment.failed

Use when a payment attempt fails in a way that affects customers.

- `severity`: `error` or `critical`
- `fingerprint`: `payment.failed:{provider}`

### integration.down

Use when a third-party API is unreachable.

- `severity`: `warning` → `error` if persistent
- `fingerprint`: `integration.down:{service}`

### deploy.completed

Used to correlate incidents with releases.

- `severity`: `info`
- `fingerprint`: `deploy`
- `context`: `version`, `commit_hash`, `environment`

---

## 7. Context Guidelines

Context should:

- ✅ Be useful for diagnosis
- ✅ Be small
- ✅ Avoid PII

✅ **Good context:**

```json
{
  "route": "/quotes",
  "job_class": "QuoteFinaliseJob",
  "queue_name": "default",
  "exception_class": "ValidationException",
  "file": "QuoteController.php",
  "line": 45
}
```

❌ **Bad context:**

```json
{
  "email": "john@example.com",
  "full_address": "123 Smith St...",
  "credit_card": "****",
  "user_id": 12345,
  "quote_id": 67890
}
```

---

## 8. Client Responsibilities

Clients must ensure:

- ✅ Emitting events **never blocks** app execution
- ✅ Brain downtime **does not break** production
- ✅ Events are only sent from production (unless agreed)
- ✅ Event names + fingerprints remain **consistent**
- ✅ Use async/fire-and-forget patterns where possible

---

## 9. Brain Responsibilities (For Clarity)

The Brain will:

- ✅ Authenticate events per project
- ✅ Group events into incidents (60-minute rolling window)
- ✅ Suppress noise (one alert per new incident)
- ✅ Alert only on serious issues (`error` and `critical` severity)
- ✅ Provide incident history + project health dashboard
- ✅ Auto-generate `severity` and `fingerprint` if not provided (but accuracy
  may suffer)

**Clients do not need to manage alerting logic.**

---

## 10. Versioning

This spec is **v1.0**.\
Changes will be additive and backward-compatible.

---

## Quick Reference

### Minimum Event (Auto-Generated Fields)

```json
{
  "event_type": "error.exception",
  "payload": {
    "exception": "Something went wrong"
  }
}
```

Brain will auto-generate:

- `severity`: `error` (inferred from `error.exception`)
- `fingerprint`: `error.exception` (or from context if available)
- `message`: `error.exception` (or from payload)

### Recommended Event (Explicit Fields)

```json
{
  "event_type": "error.exception",
  "severity": "error",
  "fingerprint": "error.exception:ValidationException:QuoteController.php:45",
  "message": "Validation failed for quote creation",
  "context": {
    "exception_class": "ValidationException",
    "file": "QuoteController.php",
    "line": 45,
    "route": "POST /api/quotes"
  },
  "payload": {
    "exception": "Validation failed",
    "errors": {...}
  }
}
```

---

## Support

For questions or clarifications:

- See `README.md` for installation and setup
- See `INTEGRATION-GUIDE.md` for implementation examples
- See `ALERTING-SYSTEM.md` (in Brain repo) for system internals
