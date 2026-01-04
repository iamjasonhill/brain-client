# Brain Web Analytics - Event Specification

## Event Format

All events follow the Brain event envelope:

```json
{
  "event_type": "web.pageview",
  "payload": { ... }
}
```

---

## Core Events

### `web.pageview`

Sent on every page load.

```json
{
    "event_type": "web.pageview",
    "payload": {
        "url": "/sydney-melbourne/",
        "title": "Sydney to Melbourne Removalists",
        "referrer": "https://google.com/",
        "device": "mobile",
        "browser": "safari",
        "screen": "390x844",
        "language": "en-AU",
        "timezone": "Australia/Sydney",
        "session_id": "x7k92m3p",
        "is_new_session": true,
        "page_count": 1,
        "landing_page": "/sydney-melbourne/",
        "utm_source": "google",
        "utm_medium": "organic",
        "utm_campaign": "summer-2025"
    }
}
```

| Field            | Type    | Description                                    |
| ---------------- | ------- | ---------------------------------------------- |
| `url`            | string  | Page path                                      |
| `title`          | string  | Page title                                     |
| `referrer`       | string  | Referring URL                                  |
| `device`         | string  | `desktop`, `mobile`, `tablet`                  |
| `browser`        | string  | `chrome`, `safari`, `firefox`, `edge`, `other` |
| `screen`         | string  | `{width}x{height}`                             |
| `language`       | string  | Browser language                               |
| `timezone`       | string  | User timezone                                  |
| `session_id`     | string  | Anonymous session identifier                   |
| `is_new_session` | boolean | First pageview in session                      |
| `page_count`     | integer | Pages viewed in session                        |
| `landing_page`   | string  | First page of session (new sessions only)      |
| `utm_*`          | string  | UTM parameters if present                      |

---

### `web.quote_clicked`

Sent when a quote/CTA link is clicked.

```json
{
    "event_type": "web.quote_clicked",
    "payload": {
        "url": "/sydney-melbourne/",
        "button_text": "Get a Free Quote",
        "destination": "https://removalistquotes.example.com/quote",
        "session_id": "x7k92m3p"
    }
}
```

---

### `web.phone_clicked`

Sent when a `tel:` link is clicked.

```json
{
    "event_type": "web.phone_clicked",
    "payload": {
        "url": "/contact/",
        "phone": "1300123456",
        "session_id": "x7k92m3p"
    }
}
```

---

### `web.external_link`

Sent when an external link is clicked.

```json
{
    "event_type": "web.external_link",
    "payload": {
        "url": "/",
        "destination": "https://facebook.com/example",
        "session_id": "x7k92m3p"
    }
}
```

---

### `web.scroll_depth`

Sent when scroll milestones are reached (25%, 50%, 75%, 100%).

```json
{
    "event_type": "web.scroll_depth",
    "payload": {
        "url": "/backloading/",
        "depth": 75,
        "session_id": "x7k92m3p"
    }
}
```

---

### `web.faq_opened`

Sent when a `<details>` element is expanded.

```json
{
    "event_type": "web.faq_opened",
    "payload": {
        "url": "/questions/",
        "question": "How much does interstate moving cost?",
        "session_id": "x7k92m3p"
    }
}
```

---

### `web.performance`

Sent after page load with Web Vitals data.

```json
{
    "event_type": "web.performance",
    "payload": {
        "url": "/",
        "ttfb": 120,
        "dom_ready": 450,
        "load_time": 1200,
        "lcp": 800,
        "session_id": "x7k92m3p"
    }
}
```

| Field       | Type    | Description                   |
| ----------- | ------- | ----------------------------- |
| `ttfb`      | integer | Time to First Byte (ms)       |
| `dom_ready` | integer | DOM Content Loaded (ms)       |
| `load_time` | integer | Full page load (ms)           |
| `lcp`       | integer | Largest Contentful Paint (ms) |

---

### `web.time_on_page`

Sent when user leaves the page (uses sendBeacon for reliability).

```json
{
    "event_type": "web.time_on_page",
    "payload": {
        "url": "/sydney-melbourne/",
        "seconds": 45,
        "session_id": "x7k92m3p"
    }
}
```

---

## Custom Events

Use `BrainAnalytics.track()` for custom events:

```js
BrainAnalytics.track("video_played", {
    video_id: "intro-video",
    duration: 120,
});
```

This sends:

```json
{
    "event_type": "web.video_played",
    "payload": {
        "video_id": "intro-video",
        "duration": 120,
        "session_id": "x7k92m3p"
    }
}
```
