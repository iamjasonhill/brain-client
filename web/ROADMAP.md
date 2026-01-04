# Brain Web Analytics - Roadmap

## Current Features (v1.0.0) ✅

- `web.pageview` - URL, title, referrer, device, browser, screen, session, UTMs
- `web.quote_clicked` - CTA button clicks
- `web.phone_clicked` - Phone link clicks
- `web.external_link` - Outbound link clicks
- `web.scroll_depth` - 25%, 50%, 75%, 100% milestones
- `web.faq_opened` - Details/FAQ expansion
- `web.performance` - TTFB, DOM ready, load time, LCP
- `web.time_on_page` - Duration before leaving

---

## Future Enhancements

### Priority 1: Easy Wins

| Feature           | Event                    | Notes                  |
| ----------------- | ------------------------ | ---------------------- |
| Email clicks      | `web.email_clicked`      | Track `mailto:` links  |
| CLS tracking      | Add to `web.performance` | Core Web Vital         |
| FID/INP tracking  | Add to `web.performance` | Interactivity metric   |
| JS error tracking | `web.error`              | Catch unhandled errors |

### Priority 2: Engagement

| Feature          | Event                  | Notes                      |
| ---------------- | ---------------------- | -------------------------- |
| Form field focus | `web.form_field_focus` | Which fields get attention |
| Form abandonment | `web.form_abandoned`   | Started but didn't submit  |
| Search queries   | `web.search`           | If site search exists      |
| Video plays      | `web.video_played`     | Manual tracking available  |

### Priority 3: Advanced

| Feature      | Event              | Notes                      |
| ------------ | ------------------ | -------------------------- |
| Rage clicks  | `web.rage_click`   | Frustration detection      |
| Copy events  | `web.text_copied`  | What users copy            |
| Print events | `web.page_printed` | Track printing             |
| Heatmaps     | N/A                | Would need separate system |

---

## Dashboard Enhancements

- [ ] Conversion funnel visualization
- [ ] A/B test tracking
- [ ] Real-time visitors view
- [ ] Export to CSV
- [ ] Scheduled email reports
- [ ] Goal tracking / conversion attribution
