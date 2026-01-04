# Website Project Setup Guide

Lessons learned from the Moving Again Astro revamp, for future website projects.

---

## Pre-Project Questionnaire

> **Answer these before starting ANY new website project**

### 1. Business & Brand

- [ ] What is the business name and tagline?
- [ ] What are the primary brand colors (provide hex codes)?
- [ ] Do you have a logo? What formats (SVG preferred)?
- [ ] What is the brand voice/tone? (Professional, friendly, casual, etc.)
- [ ] Who is the target audience?

### 2. Domain & Hosting

- [ ] What is the production domain? (e.g., `example.com.au`)
- [ ] Is www preferred or non-www? (Pick one for canonical)
- [ ] Where will it be hosted? (Vercel, Netlify, self-hosted?)
- [ ] Do you have DNS access?
- [ ] Are there existing redirects to preserve?

### 3. Content & Structure

- [ ] What are the main pages required?
- [ ] Is there existing content to migrate?
- [ ] Will there be a blog or news section?
- [ ] Are there route-based pages (e.g., city combinations)?
- [ ] What CTAs/conversions matter? (Quote forms, phone clicks, etc.)

### 4. Technical Requirements

- [ ] Need forms? What fields and where do submissions go?
- [ ] Need third-party integrations? (CRM, analytics, chat?)
- [ ] Any special functionality? (Calculators, maps, search?)
- [ ] Does the client need to edit content? (CMS needed?)
- [ ] What are the SEO priorities?

### 5. Analytics & Monitoring

- [ ] Using Brain Analytics? (Recommended)
- [ ] Any existing Google Analytics to preserve/migrate?
- [ ] Need health monitoring? (Static sites: disable heartbeat)
- [ ] What events matter for conversion tracking?

---

## Setup Checklist

### Day 1: Foundation

```bash
# Create new Astro project
npx create-astro@latest ./
```

**Immediate Setup:**

- [ ] Configure `astro.config.mjs` with correct site URL
- [ ] Set up Tailwind with brand colors in `tailwind.config.mjs`
- [ ] Create `src/utils/brand.ts` with brand constants
- [ ] Set up ESLint & Prettier (copy from moving-again)
- [ ] Initialize Git with `.gitignore`

### Day 2: Core Components

**Must-Have Components:**

- [ ] `Layout.astro` - Base layout with SEO
- [ ] `SEO.astro` - Full meta tags, OG, Twitter
- [ ] `Header.astro` - Navigation with mobile menu
- [ ] `Footer.astro` - Links, contact, legal
- [ ] `Schema.astro` - Structured data (Organization, FAQPage)
- [ ] `BrainAnalytics.astro` - Privacy-first analytics

### Day 3: Assets

**Favicon Setup (Do This Early!):**

- [ ] Get logo as SVG
- [ ] Generate all favicon sizes:
  ```bash
  cd public
  convert -background none logo.svg -resize 180x180 apple-touch-icon.png
  convert -background none logo.svg -resize 32x32 favicon-32x32.png
  convert -background none logo.svg -resize 16x16 favicon-16x16.png
  convert -background none logo.svg -resize 192x192 favicon-192x192.png
  convert -background none logo.svg -resize 512x512 favicon-512x512.png
  cp logo.svg favicon.svg
  ```
- [ ] Update `manifest.json` with project name and icons
- [ ] Add theme-color meta tag with brand color

### Day 4+: Content & Pages

**Page Structure:**

- [ ] Create page templates before content
- [ ] Use content collections for repeating patterns
- [ ] Set up `[...slug].astro` for dynamic routes
- [ ] Create 404 page

---

## Lessons Learned (Moving Again)

### What Worked Well ✅

1. **Astro + Tailwind** - Fast, flexible, great DX
2. **Content Collections** - Perfect for route pages (200+ city combos)
3. **Brain Analytics** - Privacy-first, useful data, no third-party
4. **Component-first design** - Reusable, consistent UI
5. **Early SEO setup** - Canonical URLs, sitemap, Schema.org from day 1

### What We'd Do Differently 🔄

1. **Set up favicon EARLY** - Don't leave default Astro rocket!
2. **Configure ENV vars correctly** - Use `PUBLIC_` prefix for client-side
3. **Format code before commits** - Run prettier in pre-commit hook
4. **Disable heartbeat for static sites** - No server = no heartbeat
5. **Use `set:html` for inline scripts** - Prettier-compatible in Astro

### Common Gotchas ⚠️

| Issue                                    | Solution                                   |
| ---------------------------------------- | ------------------------------------------ |
| ENV vars not working in Vercel           | Must use `PUBLIC_` prefix for client-side  |
| Dev server doesn't pick up ENV changes   | Restart `npm run dev` after editing `.env` |
| Prettier failing on Astro inline scripts | Use `set:html` pattern                     |
| ESLint errors in vanilla JS              | Add `/* eslint-disable */` comments        |
| www vs non-www inconsistent              | Set canonical URL in SEO.astro             |
| Favicon not showing                      | Check it's actually in `/public/`          |

---

## Environment Variables Template

Create `.env` with:

```bash
# Site config
PUBLIC_SITE_NAME="Company Name"
PUBLIC_SITE_DESCRIPTION="Default meta description"
PUBLIC_SITE_IMAGE="/logo.png"

# Brain Analytics
PUBLIC_BRAIN_URL=https://brain.domain.com
PUBLIC_BRAIN_KEY=brn_xxxx

# Optional: GA fallback (if needed)
PUBLIC_GA_ID=G-XXXXXXXXXX
```

**Vercel Setup:**

1. Add all `PUBLIC_*` vars to Vercel project settings
2. Redeploy after adding vars (they bake in at build time)

---

## CI/CD Checklist

**GitHub Actions (recommended):**

- [ ] Lint check (`npm run lint`)
- [ ] Format check (`npm run format:check`)
- [ ] Type check (`npm run check`)
- [ ] Build test (`npm run build`)

**Pre-commit Hook (optional but recommended):**

```json
// package.json
"scripts": {
  "precommit": "npm run format && npm run lint"
}
```

---

## Brain Analytics Setup

1. Copy `brain-analytics.js` to `/public/`
2. Copy `BrainAnalytics.astro` to `/src/components/`
3. Add to `Layout.astro`: `<BrainAnalytics />`
4. Set ENV vars: `PUBLIC_BRAIN_URL` and `PUBLIC_BRAIN_KEY`
5. In Brain admin: Disable heartbeat for static sites

**Events tracked automatically:**

- Pageviews with device/browser/referrer
- Quote button clicks
- Phone link clicks
- External link clicks
- Scroll depth (25%, 50%, 75%, 100%)
- FAQ expansions
- Time on page
- Web Vitals (TTFB, LCP, etc.)

---

## File Structure Reference

```
project/
├── public/
│   ├── favicon.svg
│   ├── favicon-*.png
│   ├── apple-touch-icon.png
│   ├── manifest.json
│   ├── brain-analytics.js
│   └── logo.svg
├── src/
│   ├── components/
│   │   ├── BrainAnalytics.astro
│   │   ├── Footer.astro
│   │   ├── Header.astro
│   │   ├── Schema.astro
│   │   └── SEO.astro
│   ├── content/           # Content collections
│   ├── layouts/
│   │   └── Layout.astro
│   ├── pages/
│   │   ├── index.astro
│   │   ├── 404.astro
│   │   └── [...slug].astro
│   ├── styles/
│   │   └── global.css
│   └── utils/
│       └── brand.ts
├── .env
├── astro.config.mjs
├── eslint.config.js
├── package.json
├── tailwind.config.mjs
└── tsconfig.json
```

---

## Quick Reference Commands

```bash
# Development
npm run dev              # Start dev server
npm run build            # Production build
npm run preview          # Preview build locally

# Code Quality
npm run lint             # ESLint check
npm run format           # Prettier format
npm run format:check     # Prettier check only
npm run check            # Astro type check

# Deploy
git add -A && git commit -m "message" && git push
```

---

_Last updated: December 2024 (Moving Again revamp)_
