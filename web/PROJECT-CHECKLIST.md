# Website Project Setup Guide

> **Master Document** - This is the authoritative source for all Astro website
> projects. Last updated: December 2024

---

## Pre-Project Questionnaire

> Answer these **before** starting any new website project

### 1. Business & Brand

- [ ] Business name and tagline?
- [ ] Primary brand colors (hex codes)?
- [ ] Logo available? (SVG preferred)
- [ ] Brand voice/tone? (Professional, friendly, casual)
- [ ] Target audience?

### 2. Domain & Hosting

- [ ] Production domain? (e.g., `example.com.au`)
- [ ] www or non-www canonical?
- [ ] Hosting platform? (Vercel, Netlify, self-hosted)
- [ ] DNS access confirmed?
- [ ] Existing redirects to preserve?

### 3. Content & Structure

- [ ] Main pages required?
- [ ] Existing content to migrate?
- [ ] Blog/news section needed?
- [ ] Route-based pages? (e.g., city combinations)
- [ ] Key CTAs/conversions?

### 4. Technical Requirements

- [ ] Forms needed? Where do submissions go?
- [ ] Third-party integrations? (CRM, chat)
- [ ] Special functionality? (Calculators, maps)
- [ ] Client CMS needed?
- [ ] SEO priorities?

### 5. Analytics & Monitoring

- [ ] Brain Analytics integration?
- [ ] Existing GA to preserve?
- [ ] Heartbeat needed? (**Disable for static sites**)
- [ ] Conversion events to track?

---

## Phase 1: Repository & Project Structure

- [ ] Create GitHub repo (e.g., `WS-projectname`)
- [ ] Initialize with `webforge init --platform=astro`
- [ ] Configure `astro.config.mjs` with production URL
- [ ] Set brand colors in `tailwind.config.mjs`
- [ ] Update `README.md` with design tokens

---

## Phase 2: Favicons & PWA

> **Do this early!** Don't leave the default Astro rocket favicon.

### Generate Favicons (when logo ready)

```bash
cd public
convert -background none logo.svg -resize 180x180 apple-touch-icon.png
convert -background none logo.svg -resize 32x32 favicon-32x32.png
convert -background none logo.svg -resize 16x16 favicon-16x16.png
convert -background none logo.svg -resize 192x192 favicon-192x192.png
convert -background none logo.svg -resize 512x512 favicon-512x512.png
cp logo.svg favicon.svg
```

### Required Files

- [ ] `/public/favicon.svg`
- [ ] `/public/favicon-16x16.png`
- [ ] `/public/favicon-32x32.png`
- [ ] `/public/favicon-192x192.png`
- [ ] `/public/favicon-512x512.png`
- [ ] `/public/apple-touch-icon.png` (180x180)
- [ ] `/public/logo.svg`

### manifest.json Updates

- [ ] `name` - Full site name
- [ ] `short_name` - Short name
- [ ] `theme_color` - Brand primary

---

## Phase 3: Core Components

- [ ] `SEO.astro` - Meta tags, OG, canonical
- [ ] `Layout.astro` - Main layout
- [ ] `Header.astro` - Navigation
- [ ] `Footer.astro` - Site footer
- [ ] `Analytics.astro` - GA4/GTM
- [ ] `Schema.astro` - JSON-LD
- [ ] `Breadcrumbs.astro` - Navigation

---

## Phase 4: Environment Configuration

### .env Setup

```env
# Site
PUBLIC_SITE_URL=https://yourdomain.com.au
PUBLIC_SITE_NAME="Your Site Name"
PUBLIC_SITE_DESCRIPTION="Site description"
PUBLIC_SITE_IMAGE=/logo.png

# Brain Analytics
PUBLIC_BRAIN_URL=https://again.com.au
PUBLIC_BRAIN_KEY=brn_xxxx

# GA4 (optional)
PUBLIC_GA_ID=G-XXXXXXXXXX
```

> **Important:** ENV vars bake in at build time. Redeploy after changes.

---

## Phase 5: Brain Analytics

> **Static sites cannot send heartbeats. Disable in Brain Admin!**

### Setup

1. [ ] Create project in Brain Admin
2. [ ] Create API key
3. [ ] **Toggle OFF "Heartbeat Monitoring"**
4. [ ] Copy `brain-analytics.js` to `/public/`
5. [ ] Create `BrainAnalytics.astro` component
6. [ ] Add to layout

### Auto-Tracked Events

- Pageviews (device, browser, referrer)
- Quote/CTA button clicks
- Phone link clicks
- External link clicks
- Scroll depth (25%, 50%, 75%, 100%)
- Time on page
- Web Vitals

---

## Phase 6: llms.txt

Create `/public/llms.txt` with:

- [ ] Brand information
- [ ] Primary services
- [ ] Geographic coverage
- [ ] Key facts
- [ ] Contact information
- [ ] Citation guidelines

---

## Phase 7: Content Pages

### Required

- [ ] `index.astro` - Homepage
- [ ] `404.astro` - Error page
- [ ] `robots.txt.ts` - Dynamic robots

### Typical Additional Pages

- [ ] Quote/Contact form
- [ ] About
- [ ] Services/Products
- [ ] FAQ
- [ ] Privacy policy
- [ ] Terms & conditions

---

## Phase 8: Pre-Launch Checks

### Technical

- [ ] `npm run build` succeeds
- [ ] `npm run lint` passes
- [ ] Unique titles/descriptions per page
- [ ] Canonical URLs correct
- [ ] Favicon visible
- [ ] Sitemap generated
- [ ] Open Graph preview works

### Analytics

- [ ] Brain project configured
- [ ] Heartbeat disabled (for static)
- [ ] Test events appearing
- [ ] GA4 working (if used)

### Content

- [ ] No placeholder text remaining
- [ ] All links working
- [ ] Forms submitting correctly
- [ ] Mobile responsive

---

## Common Gotchas

| Issue                     | Solution                      |
| ------------------------- | ----------------------------- |
| ENV vars not working      | Use `PUBLIC_` prefix          |
| Dev ignores ENV changes   | Restart `npm run dev`         |
| Prettier fails on scripts | Use `set:html` pattern        |
| www vs non-www issues     | Set canonical in SEO.astro    |
| Favicon not showing       | Check `/public/` directory    |
| health.missed incidents   | Disable heartbeat for static! |

---

## Quick Commands

```bash
npm run dev        # Dev server (localhost:4321)
npm run build      # Production build
npm run preview    # Preview build
npm run lint       # ESLint
npm run format     # Prettier
```

---

## Updating This Checklist

To get the latest version of this checklist:

```bash
webforge docs:update
```

---

_Master source: `brain-client/web/PROJECT-CHECKLIST.md`_
