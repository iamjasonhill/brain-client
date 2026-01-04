# Brain Web Analytics

Privacy-first, lightweight web analytics for your Astro/static sites. Sends data
to Brain Nucleus.

## Features

- ✅ **Pageviews** - URL, title, referrer, device, browser, screen size
- ✅ **Sessions** - Anonymous session tracking, landing page, page count
- ✅ **UTM Campaigns** - All UTM parameters captured automatically
- ✅ **Click Tracking** - Quote clicks, phone clicks, external links
- ✅ **Scroll Depth** - 25%, 50%, 75%, 100% tracking
- ✅ **Web Vitals** - LCP, TTFB, DOM ready, load time
- ✅ **Time on Page** - Accurate exit tracking with sendBeacon

## Privacy

- No cookies (uses sessionStorage)
- Respects Do Not Track
- No fingerprinting
- No PII collected
- First-party data only

## Installation (Astro)

### 1. Copy files to your project

```bash
cp brain-client/web/brain-analytics.js public/
cp brain-client/web/BrainAnalytics.astro src/components/
```

### 2. Set environment variables

```env
PUBLIC_BRAIN_URL=https://brain.yourdomain.com
PUBLIC_BRAIN_KEY=your-api-key
```

### 3. Add to your layout

```astro
---
import BrainAnalytics from '../components/BrainAnalytics.astro';
---

<html>
  <head>
    <BrainAnalytics />
  </head>
  <body>
    <slot />
  </body>
</html>
```

## Installation (Vanilla HTML)

```html
<script src="/brain-analytics.js"></script>
<script>
    BrainAnalytics.init({
        url: "https://brain.yourdomain.com",
        key: "your-api-key",
    });
</script>
```

## Configuration Options

```js
BrainAnalytics.init({
    url: "https://brain.yourdomain.com", // Required
    key: "your-api-key", // Required
    trackScrollDepth: true, // Track 25/50/75/100% scroll
    trackPerformance: true, // Track Web Vitals
    trackClicks: true, // Track link clicks
    debug: false, // Console logging
});
```

## Manual Event Tracking

```js
// Track custom events
BrainAnalytics.track("button_clicked", {
    button_id: "hero-cta",
    variant: "A",
});
```

## Events Sent

See [EVENTS.md](./EVENTS.md) for complete event specifications.

| Event               | Trigger              |
| ------------------- | -------------------- |
| `web.pageview`      | Page load            |
| `web.quote_clicked` | Quote link click     |
| `web.phone_clicked` | tel: link click      |
| `web.external_link` | External link click  |
| `web.scroll_depth`  | Scroll milestones    |
| `web.faq_opened`    | Details/summary open |
| `web.performance`   | Page load complete   |
| `web.time_on_page`  | Page exit            |

## Size

- ~3KB minified
- ~1.5KB gzipped
- No dependencies
