# Brain Business Event Definitions

## Purpose

This document defines **business events** for analytics and identity resolution
across projects. These events represent meaningful business moments (quote
created, payment received, etc.).

> **Note**: For **ops events** (health.ping, error.exception, queue.failed) used
> for alerting and monitoring, see [OPS-EVENT-SPEC.md](OPS-EVENT-SPEC.md).

Brain Nucleus standardizes event naming and structure across all projects to
ensure:

- **Consistent identity resolution**: Same event types mean the same thing
  everywhere
- **Reliable analytics**: Aggregations work across projects
- **Clear contracts**: Developers know exactly when to fire events and what data
  to include
- **Future-proofing**: Standard events enable cross-project features (e.g.,
  unified customer views)

This document defines the canonical **business event** schema for Brain v0.

## Naming Convention

**Format**: `{domain}.{action}` (dot-case, past tense)

**Rules**:

- Use **dot-case** (lowercase with dots): `quote.created`, not `QuoteCreated` or
  `quote_created`
- Use **past tense**: `quote.created`, not `quote.create` or `quote.creating`
- **Domain first**: Group by business domain (`quote`, `payment`, `provider`),
  not by technical layer (`api`, `webhook`, `queue`)
- Be **specific**: `quote.accepted` is better than `quote.updated`

**Good Examples**:

- `quote.created` ✅
- `quote.viewed` ✅
- `payment.received` ✅
- `provider.quote_requested` ✅

**Bad Examples**:

- `createQuote` ❌ (camelCase, not dot-case)
- `quote_create` ❌ (snake_case, not dot-case)
- `quote.create` ❌ (present tense, should be past)
- `api.quote_created` ❌ (technical layer, not domain)

## Core Canonical Events (v0)

### system.heartbeat

**Meaning**: Periodic health check from a client application. Used to verify
connectivity and track uptime.

**When to fire**:

- On a scheduled interval (e.g., every 5 minutes) from a background job/cron
- When the application starts up (optional)

**When NOT to fire**:

- On every HTTP request (too noisy)
- On user actions (use domain events instead)

**Minimum payload**:

```json
{
  "application": "moveroo-web",
  "version": "1.2.3"
}
```

**Entity mapping**: None (system event)

**Actor mapping**: None (system event)

**Note**: `site.health_check` is an alias for `system.heartbeat` and can be used
interchangeably. For implementation, see
[IMPLEMENTATION-CHECKLIST.md](IMPLEMENTATION-CHECKLIST.md) section 1 (health
check) and [OPS-EVENT-SPEC.md](OPS-EVENT-SPEC.md) section 5.1 (`health.ping`).

---

### quote.created

**Meaning**: A new quote/estimate has been created for a customer.

**When to fire**:

- Immediately after a quote is saved to the database
- Before sending the quote to the customer

**When NOT to fire**:

- On quote updates (use `quote.updated` if we add it later)
- On quote drafts (only fire when quote is finalized)

**Minimum payload**:

```json
{
  "quote_id": "quote_123",
  "amount": 150.00,
  "currency": "AUD"
}
```

**Entity mapping**:

- `entity_type`: `"quote"`
- `entity_id`: Quote ID (e.g., `"quote_123"`)

**Actor mapping**:

- `actor_email`: Customer email (if available)
- `actor_phone`: Customer phone (if available)

---

### quote.viewed

**Meaning**: A customer has viewed a quote (opened the quote page/email).

**When to fire**:

- When the quote detail page is loaded
- When the quote email is opened (if tracking available)

**When NOT to fire**:

- On every page refresh (add deduplication if needed)
- On quote list views (only detail views)

**Minimum payload**:

```json
{
  "quote_id": "quote_123"
}
```

**Entity mapping**:

- `entity_type`: `"quote"`
- `entity_id`: Quote ID

**Actor mapping**:

- `actor_email`: Viewer email (if authenticated)
- `actor_phone`: Viewer phone (if available)

---

### quote.accepted

**Meaning**: A customer has accepted a quote, converting it to a booking/order.

**When to fire**:

- When the customer clicks "Accept" or "Book Now"
- After the acceptance is confirmed in the database

**When NOT to fire**:

- On quote creation (even if auto-accepted)
- On quote expiration

**Minimum payload**:

```json
{
  "quote_id": "quote_123",
  "booking_id": "booking_456",
  "amount": 150.00
}
```

**Entity mapping**:

- `entity_type`: `"quote"` (primary) or `"booking"` (secondary)
- `entity_id`: Quote ID or booking ID

**Actor mapping**:

- `actor_email`: Customer email (required)
- `actor_phone`: Customer phone (if available)

---

### quote.retrieval_failed

**Meaning**: A quote retrieval from a provider portal (CEVA, PrixCar,
WeMoveCars) has failed at any stage of the process.

**When to fire**:

- When quote retrieval fails during authentication with provider portal
- When API call to provider portal fails or times out
- When parsing/processing the provider response fails
- When any other stage of quote retrieval encounters an error

**When NOT to fire**:

- On successful quote retrievals (no event needed)
- On quote creation failures (use different event type)
- On quote acceptance failures (use different event type)

**Minimum payload**:

```json
{
  "quote_id": "quote_123",
  "provider_id": 30,
  "portal_name": "CEVA",
  "failure_stage": "authentication",
  "failure_reason": "Invalid credentials"
}
```

**Full payload structure**:

```json
{
  "entity_type": "vehicle_quote",
  "entity_id": "quote_123",
  "actor_email": "customer@example.com",
  "quote_id": "quote_123",
  "reference_id": "ref_456",
  "customer_id": 789,
  "customer_email": "customer@example.com",
  "customer_name": "John Doe",
  "provider_id": 30,
  "portal_name": "CEVA",
  "portal_type": "authenticated_portal",
  "failure_stage": "api_call",
  "failure_reason": "HTTP 500 error from provider API",
  "vehicle_type": "car",
  "vehicle_make": "Toyota",
  "vehicle_model": "Camry",
  "from_locality_id": 123,
  "to_locality_id": 456,
  "error_message": "Connection timeout after 30 seconds",
  "http_status_code": 500
}
```

**Required payload fields**:

- `quote_id` (string/integer): The quote identifier
- `provider_id` (integer): Provider ID (30=CEVA, 31=PrixCar, 39=WeMoveCars)
- `portal_name` (string): The portal name (e.g., 'CEVA', 'PrixCar',
  'WeMoveCars')
- `failure_stage` (string): The stage where failure occurred (e.g.,
  'authentication', 'api_call', 'parsing', 'processing')
- `failure_reason` (string): Human-readable reason for failure

**Optional payload fields**:

- `entity_type` (string): Should be `"vehicle_quote"` for identity resolution
- `entity_id` (string): Quote ID as string for entity mapping
- `actor_email` (string): Customer email for identity resolution
- `reference_id` (string): Internal reference ID
- `customer_id` (integer): Customer identifier
- `customer_name` (string): Customer name
- `portal_type` (string): Type of portal (e.g., 'authenticated_portal')
- `vehicle_type` (string): Type of vehicle
- `vehicle_make` (string): Vehicle manufacturer
- `vehicle_model` (string): Vehicle model
- `from_locality_id` (integer): Origin location ID
- `to_locality_id` (integer): Destination location ID
- `error_message` (string): Detailed error message
- `http_status_code` (integer): HTTP status code if applicable
- Any additional context fields from `$additionalData` parameter

**Entity mapping**:

- `entity_type`: `"vehicle_quote"`
- `entity_id`: Quote ID (as string)

**Actor mapping**:

- `actor_email`: Customer email (if available)
- `actor_phone`: Customer phone (if available)

**Severity**: This event should be treated as `error` severity for alerting
purposes.

---

### booking.created

**Meaning**: A new booking/reservation has been created (may or may not be from
a quote).

**When to fire**:

- After booking is saved to database
- Before sending confirmation to customer

**When NOT to fire**:

- On booking updates
- On booking cancellations (use `booking.cancelled` if we add it)

**Minimum payload**:

```json
{
  "booking_id": "booking_456",
  "amount": 150.00,
  "currency": "AUD",
  "service_date": "2025-12-20"
}
```

**Entity mapping**:

- `entity_type`: `"booking"`
- `entity_id`: Booking ID

**Actor mapping**:

- `actor_email`: Customer email (required)
- `actor_phone`: Customer phone (if available)

---

### payment.requested

**Meaning**: A payment request has been initiated (invoice sent, payment link
created, etc.).

**When to fire**:

- When payment is requested from customer
- After payment request is sent/created

**When NOT to fire**:

- On payment retries (only fire once per payment request)
- On payment reminders (use separate event if needed)

**Minimum payload**:

```json
{
  "payment_id": "pay_789",
  "amount": 150.00,
  "currency": "AUD",
  "booking_id": "booking_456"
}
```

**Entity mapping**:

- `entity_type`: `"payment"`
- `entity_id`: Payment ID

**Actor mapping**:

- `actor_email`: Customer email (required)
- `actor_phone`: Customer phone (if available)

---

### payment.received

**Meaning**: A payment has been successfully received/confirmed.

**When to fire**:

- After payment is confirmed by payment gateway
- After payment webhook is verified and processed

**When NOT to fire**:

- On payment attempts (only on success)
- On refunds (use `payment.refunded` if we add it)

**Minimum payload**:

```json
{
  "payment_id": "pay_789",
  "amount": 150.00,
  "currency": "AUD",
  "transaction_id": "txn_abc123",
  "booking_id": "booking_456"
}
```

**Entity mapping**:

- `entity_type`: `"payment"`
- `entity_id`: Payment ID

**Actor mapping**:

- `actor_email`: Customer email (required)
- `actor_phone`: Customer phone (if available)

---

### provider.quote_requested

**Meaning**: A quote request has been sent to a service provider (e.g., moving
company, cleaner).

**When to fire**:

- After quote request is sent to provider
- When provider is notified (email/SMS/API)

**When NOT to fire**:

- On internal quote creation (use `quote.created`)
- On provider quote updates

**Minimum payload**:

```json
{
  "quote_id": "quote_123",
  "provider_id": "provider_xyz",
  "service_type": "moving"
}
```

**Entity mapping**:

- `entity_type`: `"quote"` (primary) or `"provider"` (secondary)
- `entity_id`: Quote ID or provider ID

**Actor mapping**:

- `actor_email`: Provider contact email (if available)
- `actor_phone`: Provider contact phone (if available)

---

### provider.quote_failed

**Meaning**: A provider failed to respond to a quote request or rejected it.

**When to fire**:

- When provider explicitly rejects quote request
- When quote request times out (after reasonable wait period)
- When provider API/webhook returns error

**When NOT to fire**:

- On temporary provider API errors (only on final failure)
- On quote cancellations by customer

**Minimum payload**:

```json
{
  "quote_id": "quote_123",
  "provider_id": "provider_xyz",
  "reason": "unavailable" // or "timeout", "rejected", "error"
}
```

**Entity mapping**:

- `entity_type`: `"quote"` (primary) or `"provider"` (secondary)
- `entity_id`: Quote ID or provider ID

**Actor mapping**:

- `actor_email`: Provider contact email (if available)
- `actor_phone`: Provider contact phone (if available)

---

## Standard Event Envelope

All events sent to Brain Nucleus must follow this structure:

```json
{
  "event_type": "quote.created",
  "occurred_at": "2025-12-13T10:30:00Z",
  "payload": {
    "quote_id": "quote_123",
    "amount": 150.00
  }
}
```

### Required Fields

- **`event_type`** (string): The canonical event name (e.g., `"quote.created"`)
- **`payload`** (object): Event-specific data (must be a JSON object/array)

### Optional Fields

- **`occurred_at`** (ISO 8601 datetime string): When the event actually occurred
  (defaults to ingestion time if omitted)
- **`actor_email`** (string, in payload): Customer/user email for identity
  resolution
- **`actor_phone`** (string, in payload): Customer/user phone for identity
  resolution
- **`entity_type`** (string, in payload): Type of entity this event relates to
  (e.g., `"quote"`, `"booking"`)
- **`entity_id`** (string, in payload): ID of the entity this event relates to

### Payload Guidelines

- **Keep payload safe**: Avoid unnecessary PII (Personally Identifiable
  Information) unless required for identity resolution
- **Use consistent field names**: `quote_id`, `booking_id`, `payment_id` (not
  `id`, `quoteId`, `quote_id`)
- **Include amounts with currency**: `{"amount": 150.00, "currency": "AUD"}`
- **Use ISO 8601 for dates**: `"2025-12-13T10:30:00Z"` or `"2025-12-13"`

## Examples

### cURL Example

```bash
curl -X POST https://again.com.au/api/v1/events \
  -H "X-Brain-Key: your-api-key-here" \
  -H "Content-Type: application/json" \
  -d '{
    "event_type": "quote.created",
    "occurred_at": "2025-12-13T10:30:00Z",
    "payload": {
      "quote_id": "quote_123",
      "amount": 150.00,
      "currency": "AUD",
      "actor_email": "customer@example.com",
      "entity_type": "quote",
      "entity_id": "quote_123"
    }
  }'
```

### Laravel Brain Client Example

First, copy `brain-client/BrainEventClient.php` into your Laravel app (e.g.,
`app/Services/BrainEventClient.php`).

```php
use App\Services\BrainEventClient;

// In your controller or service
$client = new BrainEventClient(
    config('services.brain.base_url'), // e.g., 'https://again.com.au'
    config('services.brain.api_key') // Your API key
);

// Send a quote.created event
$client->send('quote.created', [
    'quote_id' => $quote->id,
    'amount' => $quote->amount,
    'currency' => 'AUD',
    'actor_email' => $quote->customer_email,
    'entity_type' => 'quote',
    'entity_id' => $quote->id,
], $quote->created_at);

// Send a payment.received event
$client->send('payment.received', [
    'payment_id' => $payment->id,
    'amount' => $payment->amount,
    'currency' => 'AUD',
    'transaction_id' => $payment->gateway_transaction_id,
    'booking_id' => $payment->booking_id,
    'actor_email' => $payment->customer_email,
    'entity_type' => 'payment',
    'entity_id' => $payment->id,
]);
```

## Design Rules

1. **Events represent meaningful business moments**: Fire events for business
   actions (quote created, payment received), not technical events (HTTP
   request, database save).

2. **Avoid noisy UI events**: Don't fire events on every page view, button
   click, or scroll. Aggregate or deduplicate if needed.

3. **Prefer idempotent events**: Events should be safe to send multiple times.
   If an event can fire multiple times for the same action, add deduplication
   logic or use idempotency keys.

4. **Keep payloads focused**: Include only data relevant to the event. Don't
   dump entire models—extract only what's needed.

5. **Use entity mapping**: Always include `entity_type` and `entity_id` when the
   event relates to a specific entity (quote, booking, payment). This enables
   cross-project entity linking.

6. **Include actor information**: Always include `actor_email` or `actor_phone`
   when the event has a customer/user actor. This enables identity resolution.

## Versioning

This document defines **Brain Events v0**.

**Backward compatibility**: When we add new events or change existing event
meanings, we will:

- Bump the version (e.g., `v1`) and create a new section
- Keep old events documented for reference
- Maintain backward compatibility in the ingestion API
- Provide migration guides for breaking changes

**Current version**: v0 (December 2025)
