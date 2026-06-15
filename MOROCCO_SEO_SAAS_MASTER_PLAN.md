# 🇲🇦 MOROCCO SEO ANALYZER — COMPLETE MASTER PLAN FOR AI AGENT
> Full blueprint to build a Moroccan-first SEO SaaS platform (codename: **"ScoreMa"**)
> Version 1.0 — Ready for AI Agent execution

---

## 0. PROJECT OVERVIEW

**Product Name:** ScoreMa (or "سكور.ما")  
**Tagline:** "دخل موقعك، شوف ليناك فـ Google"  
**Category:** Local SEO SaaS — Morocco-first  
**Target Users:** PME, artisans, restaurants, pharmacies, coiffeurs, clinics, real estate — any Moroccan business wanting Google visibility  
**Primary Language:** Darija (Arabic dialect) + French | Dashboard bilingual  
**Core Differentiator:** Only SEO tool built specifically for Morocco's multilingual, local, and cultural context  
**Revenue Model:** Freemium → Subscription (99–499 DH/month)

---

## 1. TECH STACK (Full Specification)

### 1.1 Frontend
| Layer | Technology | Why |
|---|---|---|
| Framework | **Next.js 14** (App Router) | SSR for SEO, fast, React ecosystem |
| Styling | **Tailwind CSS** + **shadcn/ui** | Speed + consistent design system |
| Language | **TypeScript** | Safety, maintainability |
| State Management | **Zustand** | Lightweight, no boilerplate |
| Data Fetching | **TanStack Query (React Query)** | Caching, loading states |
| Charts & Graphs | **Recharts** + **Chart.js** | Score visualizations |
| Animation | **Framer Motion** | Smooth dashboard transitions |
| Forms | **React Hook Form** + **Zod** | Validation |
| PDF Export | **React-PDF / jsPDF** | Client-side report generation |
| i18n | **next-intl** | Arabic (RTL) + French + Darija |
| Maps | **Google Maps JS API** | Local competitor visualization |

### 1.2 Backend
| Layer | Technology | Why |
|---|---|---|
| Runtime | **Node.js** with **Bun** | Performance |
| Framework | **Hono.js** or **FastAPI (Python)** | Lightweight API; Python if ML-heavy |
| Queue / Jobs | **BullMQ** + **Redis** | Async SEO crawling jobs |
| Cron / Scheduler | **node-cron** | Weekly score recalculations |
| WebSockets | **Socket.io** | Real-time analysis progress bar |
| Email | **Resend** | Transactional emails + reports |
| WhatsApp | **WhatsApp Business API** (Meta) or **Twilio** | Report delivery by WhatsApp |

### 1.3 Database
| Layer | Technology | Why |
|---|---|---|
| Primary DB | **PostgreSQL** (via Supabase) | Relational, ACID, hosted |
| ORM | **Prisma** | Type-safe queries |
| Cache | **Redis** (Upstash) | Rate limiting, job queues, session |
| Search | **Meilisearch** | Fast Darija/French keyword search |
| File Storage | **Cloudflare R2** or **Supabase Storage** | PDFs, screenshots |

### 1.4 AI / ML Layer
| Tool | Use Case |
|---|---|
| **Claude API (Anthropic)** | Darija content generation, recommendations explanation, meta tag writing |
| **OpenAI GPT-4o** | Fallback LLM, structured JSON extraction |
| **Google Gemini** | Alternative for Arabic NLP |
| **Hugging Face** (Arabic BERT) | Keyword sentiment / local relevance scoring |
| **Browserless / Puppeteer** | Screenshot + Core Web Vitals crawl |
| **Lighthouse CI** | Automated performance scoring |

### 1.5 External APIs
| API | Purpose |
|---|---|
| **Google Search Console API** | Real keyword rankings, impressions, clicks |
| **Google My Business (GMB) API** | Business profile health check |
| **Google PageSpeed Insights API** | Performance scoring (free) |
| **Google Places API** | Competitor discovery in local area |
| **Google Custom Search API** | SERP position checking |
| **SerpAPI** | Keyword tracking + SERP snapshots |
| **DataForSEO API** | Backlinks, domain authority, bulk keyword data |
| **Ahrefs API** (optional) | Domain rating (if budget allows) |
| **Screaming Frog API** | Deep technical crawl |
| **Majestic API** | Trust flow + backlink data |
| **Common Crawl** | Free backlink data alternative |
| **Cloudflare Workers** | Edge-based rate limiting |

### 1.6 Infrastructure
| Service | Purpose |
|---|---|
| **Vercel** | Frontend deployment (Next.js optimized) |
| **Railway** or **Render** | Backend API + workers |
| **Supabase** | PostgreSQL + Auth + Realtime |
| **Cloudflare** | CDN, DDoS, DNS |
| **GitHub Actions** | CI/CD pipeline |
| **Sentry** | Error monitoring |
| **PostHog** | Product analytics (user behavior) |
| **Stripe** | Payments (international cards) |
| **CMI / PayZone / CIH Pay** | Moroccan payment gateways (carte bancaire Maroc) |

---

## 2. DATABASE SCHEMA (Full ERD)

### 2.1 Core Tables

```sql
-- Users & Auth
users (
  id UUID PRIMARY KEY,
  email VARCHAR UNIQUE NOT NULL,
  name VARCHAR,
  phone VARCHAR,          -- for WhatsApp reports
  preferred_language ENUM('darija', 'french', 'arabic'),
  avatar_url TEXT,
  subscription_tier ENUM('free', 'starter', 'pro', 'agency'),
  stripe_customer_id VARCHAR,
  whatsapp_opt_in BOOLEAN DEFAULT false,
  created_at TIMESTAMPTZ,
  updated_at TIMESTAMPTZ
)

-- Websites being analyzed
websites (
  id UUID PRIMARY KEY,
  user_id UUID REFERENCES users(id),
  domain VARCHAR NOT NULL,
  verified BOOLEAN DEFAULT false,
  verification_token VARCHAR,
  business_type ENUM('restaurant', 'pharmacy', 'coiffeur', 'clinic', 'ecommerce', 'real_estate', 'hotel', 'other'),
  city VARCHAR,                   -- e.g. "Casablanca", "Marrakech"
  google_place_id VARCHAR,        -- linked GMB listing
  google_search_console_token TEXT,  -- OAuth token
  google_my_business_token TEXT,
  created_at TIMESTAMPTZ
)

-- SEO Analysis Snapshots
analyses (
  id UUID PRIMARY KEY,
  website_id UUID REFERENCES websites(id),
  triggered_by ENUM('manual', 'scheduled', 'api'),
  status ENUM('queued', 'running', 'completed', 'failed'),
  score_total INTEGER,            -- /100
  score_technical INTEGER,        -- /30
  score_onpage INTEGER,           -- /30
  score_local INTEGER,            -- /20
  score_mobile INTEGER,           -- /10
  score_speed INTEGER,            -- /10
  raw_data JSONB,                 -- full Lighthouse + crawl JSON
  created_at TIMESTAMPTZ,
  completed_at TIMESTAMPTZ
)

-- Individual Issues found per analysis
issues (
  id UUID PRIMARY KEY,
  analysis_id UUID REFERENCES analyses(id),
  category ENUM('technical', 'onpage', 'local', 'mobile', 'speed', 'content'),
  severity ENUM('critical', 'high', 'medium', 'low'),
  issue_type VARCHAR,             -- e.g. 'missing_meta_description'
  title_fr VARCHAR,
  title_dar VARCHAR,              -- Darija
  description_fr TEXT,
  description_dar TEXT,
  fix_steps_fr TEXT,
  fix_steps_dar TEXT,
  affected_url TEXT,
  auto_fixable BOOLEAN DEFAULT false,
  fixed_at TIMESTAMPTZ
)

-- Keyword Tracking
tracked_keywords (
  id UUID PRIMARY KEY,
  website_id UUID REFERENCES websites(id),
  keyword VARCHAR,
  language ENUM('darija', 'french', 'arabic', 'english'),
  city VARCHAR,
  current_rank INTEGER,
  previous_rank INTEGER,
  search_volume_estimate INTEGER,
  difficulty_score INTEGER,       -- 0-100
  created_at TIMESTAMPTZ
)

keyword_rank_history (
  id UUID PRIMARY KEY,
  tracked_keyword_id UUID REFERENCES tracked_keywords(id),
  rank INTEGER,
  serp_features JSONB,            -- featured snippet, local pack, etc.
  recorded_at TIMESTAMPTZ
)

-- Competitors
competitors (
  id UUID PRIMARY KEY,
  website_id UUID REFERENCES websites(id),
  competitor_domain VARCHAR,
  competitor_name VARCHAR,
  google_place_id VARCHAR,
  maps_rating DECIMAL,
  maps_review_count INTEGER,
  estimated_score INTEGER,
  last_checked_at TIMESTAMPTZ
)

-- AI Generated Content
ai_content (
  id UUID PRIMARY KEY,
  website_id UUID REFERENCES websites(id),
  content_type ENUM('meta_title', 'meta_description', 'google_post', 'blog_post', 'review_reply', 'faq', 'business_description'),
  language ENUM('darija', 'french', 'arabic'),
  prompt_used TEXT,
  generated_content TEXT,
  used BOOLEAN DEFAULT false,
  created_at TIMESTAMPTZ
)

-- Reports
reports (
  id UUID PRIMARY KEY,
  analysis_id UUID REFERENCES analyses(id),
  website_id UUID REFERENCES websites(id),
  pdf_url TEXT,
  sent_via_email BOOLEAN DEFAULT false,
  sent_via_whatsapp BOOLEAN DEFAULT false,
  recipient_email VARCHAR,
  created_at TIMESTAMPTZ
)

-- Subscriptions & Billing
subscriptions (
  id UUID PRIMARY KEY,
  user_id UUID REFERENCES users(id),
  tier ENUM('starter', 'pro', 'agency'),
  status ENUM('active', 'past_due', 'cancelled', 'trialing'),
  stripe_subscription_id VARCHAR,
  current_period_start TIMESTAMPTZ,
  current_period_end TIMESTAMPTZ,
  sites_limit INTEGER,
  analyses_per_month INTEGER
)

-- Notifications / Alerts
alerts (
  id UUID PRIMARY KEY,
  website_id UUID REFERENCES websites(id),
  user_id UUID REFERENCES users(id),
  alert_type ENUM('score_drop', 'new_negative_review', 'rank_drop', 'competitor_change', 'technical_error'),
  threshold INTEGER,              -- e.g. alert if score drops by 10
  channel ENUM('email', 'whatsapp', 'both'),
  active BOOLEAN DEFAULT true
)

-- Google Business Profile data
gmb_profiles (
  id UUID PRIMARY KEY,
  website_id UUID REFERENCES websites(id),
  google_place_id VARCHAR UNIQUE,
  business_name VARCHAR,
  rating DECIMAL,
  review_count INTEGER,
  is_verified BOOLEAN,
  has_photos BOOLEAN,
  has_posts BOOLEAN,
  categories JSONB,
  opening_hours JSONB,
  attributes JSONB,
  profile_completeness_score INTEGER,  -- /100
  last_synced_at TIMESTAMPTZ
)

-- White Label (for Agency tier)
white_label_configs (
  id UUID PRIMARY KEY,
  user_id UUID REFERENCES users(id),
  agency_name VARCHAR,
  logo_url TEXT,
  primary_color VARCHAR,
  custom_domain VARCHAR,
  report_footer_text TEXT
)
```

---

## 3. FULL WEBSITE STRUCTURE (All Pages)

### 3.1 Public Pages
```
/                           → Landing Page (Hero + Demo)
/analyse                    → Free Audit Tool (no auth required for 1 use)
/resultats/[analysisId]     → Public shareable results page
/tarifs                     → Pricing page
/blog                       → SEO blog (Darija + French articles)
/blog/[slug]                → Blog post
/guides                     → Free SEO guides (lead magnet)
/guides/[slug]              → Specific guide
/comparaison/semrush        → vs SEMrush comparison page
/comparaison/ubersuggest    → vs Ubersuggest
/secteurs/restaurant        → Landing: SEO pour les restaurants au Maroc
/secteurs/pharmacie         → Landing: SEO pour les pharmacies
/secteurs/coiffeur          → Landing: SEO pour coiffeurs & salons
/secteurs/clinique          → Landing: SEO pour cliniques
/secteurs/immobilier        → Landing: SEO immobilier Maroc
/villes/casablanca          → SEO à Casablanca (local SEO city pages)
/villes/marrakech           → SEO à Marrakech
/villes/rabat               → etc.
/villes/tanger
/villes/fes
/villes/agadir
/about                      → About / story
/contact                    → Contact form
/politique-de-confidentialite
/conditions-utilisation
/sitemap.xml
/robots.txt
```

### 3.2 Auth Pages
```
/auth/connexion             → Login (email + Google OAuth)
/auth/inscription           → Register
/auth/mot-de-passe-oublie   → Forgot password
/auth/verification          → Email verification
```

### 3.3 Dashboard (Authenticated)
```
/dashboard                  → Overview: all sites, scores, alerts
/dashboard/site/[id]        → Single site dashboard
/dashboard/site/[id]/analyse → Run new analysis
/dashboard/site/[id]/resultats/[analysisId] → Detailed results
/dashboard/site/[id]/mots-cles  → Keyword tracker
/dashboard/site/[id]/concurrents → Competitors
/dashboard/site/[id]/gmb    → Google Business Profile manager
/dashboard/site/[id]/contenu → AI Content Generator
/dashboard/site/[id]/rapports → Reports history
/dashboard/site/[id]/alertes → Alert settings
/dashboard/site/[id]/historique → Score history chart
/dashboard/ajouter-site     → Add new website
/dashboard/parametres       → Account settings
/dashboard/abonnement       → Subscription management
/dashboard/facturation      → Billing & invoices
/dashboard/api              → API keys (for agency users)
/dashboard/marque-blanche   → White label settings (agency)
/dashboard/equipe           → Team members (agency)
```

### 3.4 Admin Panel (Internal)
```
/admin                      → Admin overview
/admin/utilisateurs         → All users management
/admin/analyses             → Queue status, failed jobs
/admin/revenus              → MRR, churn, metrics
/admin/blog                 → Blog CMS
/admin/parametres           → System settings
```

---

## 4. FEATURE SPECIFICATIONS (Detailed)

### 4.1 🔍 SEO Analyzer Engine
**How it works step by step:**

1. User submits URL → Job created in BullMQ queue
2. Puppeteer/Playwright crawls the URL (headless browser)
3. Parallel API calls:
   - Google PageSpeed Insights API → Performance scores
   - Custom crawler → HTML parsing (titles, meta, headings, images, links)
   - Google Places API → Find GMB listing
   - DataForSEO / SerpAPI → SERP position check
   - Majestic/Ahrefs → Backlink data
4. All data normalized → Score calculation algorithm runs
5. AI (Claude) generates Darija/French explanations per issue
6. Results stored in DB → WebSocket event pushes to frontend
7. PDF generated in background

**Score Calculation Algorithm:**
```
Technical (30 pts):
  - HTTPS enabled: 3
  - No broken links: 4
  - XML Sitemap exists: 3
  - Robots.txt valid: 2
  - Canonical tags: 2
  - Structured data / Schema: 4
  - No duplicate content: 3
  - Mobile-friendly (viewport): 3
  - No crawl errors: 3
  - hreflang tags (fr/ar): 3

On-Page (30 pts):
  - Title tag (present, length, keyword): 5
  - Meta description (present, length, CTA): 5
  - H1 exists and unique: 4
  - Keyword in URL: 2
  - Alt text on images: 4
  - Internal linking: 3
  - Content length: 3
  - Content in Arabic/French: 4

Local SEO (20 pts):
  - GMB profile claimed: 5
  - GMB profile completeness: 4
  - NAP consistency: 4
  - Local schema markup: 4
  - Google Maps embed on site: 1
  - Local citations: 2

Mobile (10 pts):
  - Responsive design: 4
  - Touch targets size: 2
  - No horizontal scroll: 2
  - Font size readable: 2

Speed (10 pts):
  - LCP < 2.5s: 3
  - FID/INP < 200ms: 2
  - CLS < 0.1: 2
  - TTFB < 800ms: 2
  - Image optimization: 1
```

---

### 4.2 🗺️ Google Business Profile (GMB) Manager
- Connect via OAuth → read GMB data
- Profile completeness score breakdown
- AI-generated Google Posts (in Darija + French)
- Review management: see all reviews, AI draft reply suggestions
- Q&A suggestions
- Photo analysis: detect if photos are missing
- Alert when new review arrives
- Bulk post scheduling (for Ramadan, Eid, etc.)
- Monthly GMB report comparing vs competitors

---

### 4.3 🤖 AI Content Generator
Content types:
- **Meta Title** (Darija + French, SEO-optimized)
- **Meta Description** (with CTA adapted to Moroccan culture)
- **Google My Business Posts** (events, offers, updates)
- **FAQ Section** (for local questions, "كيفاش توصل ليهم?")
- **Blog Post Outline** + **Full Article**
- **Review Reply Templates** (professional Darija/French)
- **Product/Service Descriptions**
- **Schema Markup JSON-LD** (LocalBusiness, Restaurant, etc.)
- **Social Media Captions** (Facebook/Instagram Maroc)
- **WhatsApp Business welcome messages**
- **Ramadan/Eid special offers copy**

Implementation: Stream from Claude API, user can edit, regenerate, copy, or "Apply" directly.

---

### 4.4 📊 Rank Tracker (Keyword Tracker)
- User adds keywords to track (e.g., "coiffeur casa centre", "pharmacie garde rabat")
- Daily automated SERP check via SerpAPI (geo=Morocco, lang=fr/ar)
- Trend chart: rank over 30/90 days
- Color coding: green (improved), red (dropped), grey (no change)
- Local Pack tracking separately (Maps vs. organic)
- Keyword suggestions: AI suggests 10-20 new keywords based on business type
- Search volume estimates (DataForSEO)
- Export to Excel/CSV

---

### 4.5 👥 Competitor Intelligence
- Auto-detected from Google Maps (nearest 5 businesses of same type)
- Metrics per competitor:
  - GMB rating + review count
  - Estimated website score
  - Top keywords they rank for
  - Backlink count estimate
  - Social media presence (detected)
  - Response rate to reviews
- "Gap Analysis": keywords they rank for that you don't
- Alerts if competitor changes significantly

---

### 4.6 📈 Historical Tracking & Reporting
- Score tracked automatically every 7 days (background job)
- Line chart: score over 6 months
- Issue resolution tracking: shows which fixes improved score
- Monthly email report (PDF) sent automatically
- WhatsApp message: brief summary with emoji (e.g., "📈 Score ديالك زاد بـ 8 نقاط هاد الشهر!")
- Custom date range comparison (this month vs last month)
- Export: PDF, Excel, JSON

---

### 4.7 🔔 Alert System
Alert triggers:
- Score drops by X points (configurable)
- New 1-star or 2-star review on Google
- Keyword drops below position X
- Website goes down (uptime monitoring)
- SSL certificate expiring (within 30 days)
- Competitor gets a big score jump
- Google algorithm update detected (via news feed)
- New backlink gained/lost

Delivery: Email + WhatsApp (user's choice)

---

### 4.8 🏢 Multi-Location Manager
For businesses with branches (pharmacy chains, restaurant franchises):
- Add up to 10 locations (Agency plan)
- Centralized dashboard showing all locations' scores
- Per-city ranking (Casablanca vs Marrakech branch)
- Bulk actions: run analysis on all, export combined report
- Franchise SEO score leaderboard

---

### 4.9 📱 Website Scanner Chrome Extension
- Lightweight Chrome/Firefox extension
- Show SEO score badge on any website (competitor sites too!)
- Quick scan of current page: title, meta, H1, speed
- "Add to dashboard" button
- Show local competitors from sidebar

---

### 4.10 🧪 A/B Testing Suggestions
- Suggest two versions of meta title / description
- Track which version drives more clicks (via GSC integration)
- AI recommends winner after 2 weeks

---

### 4.11 🌐 Website Builder Integration (Future Phase)
- WordPress plugin: install plugin, get automatic fixes
- Shopify app: for Moroccan ecommerce
- Wix/Webflow integration via API
- Auto-implement meta tags, schema, sitemap via plugin

---

### 4.12 📞 Lead Capture Tool (for Agency Mode)
Agency users can:
- Generate a white-label audit link (e.g., `audit.agence-xyz.ma/analyse`)
- Send to prospects → they get analysis → agency gets lead notification
- Custom branding on report (logo, color, contact info)
- CRM-light: manage prospects, mark as "sent proposal", "won", "lost"

---

### 4.13 🎓 SEO Academy (Learning)
- Free mini-courses in Darija/French: "كيفاش تحسن موقعك فـ 30 يوم"
- Video tutorials embedded
- Downloadable checklists (SEO Checklist Maroc PDF)
- Case studies: local businesses that improved with local SEO
- Blog with Moroccan SEO tips

---

### 4.14 📱 Mobile App (Phase 2)
- React Native / Expo
- Dashboard overview on mobile
- Receive alerts as push notifications
- Quick audit scan
- WhatsApp share button for reports

---

## 5. USER FLOWS

### Flow 1: New Visitor → Free Audit → Conversion
```
Landing Page
    ↓
Enter domain + click "Analyse Gratuit"
    ↓
(No auth needed for first audit)
Progress bar: Crawling... Checking GMB... Calculating score... (15-30 sec)
    ↓
Results page: Score shown, top 3 issues, blurred premium sections
    ↓
CTA: "Voir l'analyse complète" → Sign Up Modal
    ↓
Sign up (email or Google)
    ↓
Full results unlocked
    ↓
"Ajouter ce site à mon dashboard?" → Yes
    ↓
Dashboard onboarding (connect GSC, connect GMB)
    ↓
Upsell: "Activer le tracking hebdomadaire" → Pricing page
```

### Flow 2: Returning User → Dashboard → Weekly Review
```
Login
    ↓
Dashboard: all sites overview, score cards, alerts badge
    ↓
Click site → Site dashboard
    ↓
Check score trend chart (improved/dropped?)
    ↓
View new issues detected this week
    ↓
Click issue → See Darija explanation + fix steps
    ↓
Click "Generate Fix with AI" → AI writes content
    ↓
Copy/Apply → Mark as fixed
    ↓
Download monthly PDF report
```

### Flow 3: Agency User → White Label → Client Report
```
Agency dashboard → Manage clients
    ↓
Add client site → Run analysis
    ↓
Customize report (logo, brand colors)
    ↓
One-click: Send PDF via email + WhatsApp to client
    ↓
Client receives branded report
    ↓
Agency sees "viewed" status
```

---

## 6. LANDING PAGE CONTENT STRATEGY

### Hero Section
**Headline (Darija):**  
"شوف فين كتبان فـ Google — وكيفاش تتصدر"

**Subheadline (French):**  
"L'unique outil SEO conçu pour le marché marocain. Audit gratuit en 30 secondes."

**Social Proof under hero:**  
"+2,400 entreprises marocaines ont déjà analysé leur site"

### Sections Order:
1. Hero (URL input + CTA)
2. Live demo / animated score preview
3. "Pourquoi ScoreMa?" — problems with generic tools (SEMrush doesn't understand Darija)
4. Features breakdown (Local Score, GMB, AI Content, Tracking)
5. Sector-specific examples (restaurant, pharmacy, coiffeur)
6. How it works (3 steps)
7. Pricing
8. Testimonials (Moroccan business owners)
9. FAQ
10. Final CTA + footer

---

## 7. PRICING PLANS (Detailed)

| Feature | Gratuit | Starter (99 DH/m) | Pro (249 DH/m) | Agence (499 DH/m) |
|---|---|---|---|---|
| Audits / mois | 1 | 10 | 50 | Illimité |
| Sites | 0 (no save) | 1 | 3 | 15 |
| Keyword tracking | ✗ | 10 mots-clés | 50 | 500 |
| GMB Manager | ✗ | ✓ | ✓ | ✓ |
| AI Content | ✗ | 10 générations | 100 | Illimité |
| Historical tracking | ✗ | 3 mois | 12 mois | 24 mois |
| PDF Reports | Basique | ✓ | ✓ | Marque blanche |
| WhatsApp Reports | ✗ | ✗ | ✓ | ✓ |
| Competitor Analysis | ✗ | 3 concurrents | 10 | 30 |
| Alertes | ✗ | Email | Email + WhatsApp | Tout |
| API Access | ✗ | ✗ | ✗ | ✓ |
| Team members | 1 | 1 | 3 | 10 |
| Support | Email | Email | Priority | Dédiée |
| Annual discount | - | -20% | -20% | -25% |

**Moroccan Payment Options:**
- Carte bancaire Maroc (CMI/CIH Pay)
- PayPal
- Virement bancaire (manual for agency)
- Cash via Barid (manual option for small clients)

---

## 8. MOROCCAN-SPECIFIC FEATURES (Unique to ScoreMa)

### 8.1 Ramadan & Eid Mode
- Auto-detect upcoming Moroccan holidays
- Suggest GMB posts, offers, and schedule content
- Special SEO opportunities during Ramadan (peak search times shift)

### 8.2 Darija Keyword Intelligence
- Custom Darija keyword database (e.g., "طبيب الأسنان كازا", "كويزر بلدية", "ريستو حلال مراكش")
- Transliteration support (French-spelled Darija like "koiffeur", "farmassi")
- Code-switching detection (Darija + French mixed)

### 8.3 Local Citation Builder
- Check if business is listed in: Yaki.ma, Jumialist.ma, Telephonemaroc.ma, Soug.ma, etc.
- One-click listing submission suggestions
- NAP (Name, Address, Phone) consistency checker across Moroccan directories

### 8.4 WhatsApp-First Reports
- Since WhatsApp is Morocco's primary communication channel
- Weekly summary message with emoji scorecard
- Button to view full dashboard from WhatsApp link
- Opt-in to receive alerts on WhatsApp

### 8.5 Moroccan Business Calendar Integration
- Al-Omra season (search spike for religious content)
- Rentrée scolaire (back to school businesses)
- Moussem events (local festivals — local business SEO spike)
- World Cup/CAF events (restaurants, cafes SEO)

### 8.6 Bilingual (FR/AR/Darija) Content Generator
- All meta tags generated in both French and Arabic automatically
- hreflang tag generator for fr-MA and ar-MA
- RTL layout testing checker

### 8.7 Moroccan Competitor Map
- Interactive Google Maps view showing your position vs competitors
- Color coded pins: green (you're winning), red (they're beating you)
- Click competitor → see their score estimate

---

## 9. MARKETING STRATEGY

### 9.1 SEO Content (Programmatic)
- City pages: /villes/casablanca, /villes/marrakech, etc.
- Sector pages: /secteurs/restaurant, /secteurs/pharmacie, etc.
- Tool pages: /outils/generateur-meta-tags, /outils/verificateur-gmb
- Blog: publish 4 articles/month in Darija + French

### 9.2 Paid Ads
- Facebook/Instagram targeting: business owners by city + business type
- Ad copy in Darija: "واش موقعك كيبان فـ Google؟ جرب مجاناً"
- Retargeting: users who ran free audit but didn't convert
- Google Ads: "référencement local maroc", "SEO maroc PME"

### 9.3 Partnership Channel
- Partner with Moroccan web agencies: they white-label ScoreMa
- Partner with accounting firms (they have PME clients)
- Partner with telecom operators (Maroc Telecom, Orange Maroc)
- Chamber of Commerce partnerships (CGEM, CCI Casablanca)

### 9.4 Viral Loop
- Free shareable audit link: "Voir mon score SEO" → shared on WhatsApp groups
- Referral program: get 1 free month per referred paid user
- "Powered by ScoreMa" badge on white-label reports

---

## 10. API (For Agency Tier)

### Endpoints
```
POST /api/v1/analyse          → Trigger new analysis
GET  /api/v1/analyse/:id      → Get analysis results
GET  /api/v1/sites            → List all sites
POST /api/v1/sites            → Add site
GET  /api/v1/sites/:id/score  → Get latest score
GET  /api/v1/keywords/:siteId → Get keyword rankings
POST /api/v1/content/generate → Generate AI content
GET  /api/v1/reports/:siteId  → Get reports list
POST /api/v1/reports/send     → Send report via email/WhatsApp
GET  /api/v1/competitors/:siteId → Get competitor data
```

### Rate Limits (per plan)
- Agency: 1000 req/hour
- API key authentication (JWT + API key)
- Webhook support for async results

---

## 11. SECURITY & COMPLIANCE

- GDPR + Moroccan Law 09-08 (data protection) compliant
- Data stored in EU/Morocco region
- Row Level Security (RLS) in Supabase
- OAuth 2.0 for Google integrations
- All tokens encrypted at rest (AES-256)
- API keys hashed before storage
- Rate limiting on all public endpoints
- Bot detection on free audit page (prevent abuse)
- Input sanitization (prevent XSS, injection)
- HTTPS enforced everywhere

---

## 12. PERFORMANCE TARGETS

| Metric | Target |
|---|---|
| Landing page LCP | < 1.5s |
| Free audit completion time | < 30 seconds |
| Dashboard load time | < 1s |
| PDF generation | < 10 seconds |
| API response (cached) | < 100ms |
| Uptime | 99.9% |
| Crawler success rate | > 95% |

---

## 13. DEVELOPMENT PHASES (Roadmap)

### Phase 1 — MVP (Months 1-2)
- [ ] Landing page + free audit form
- [ ] Basic crawler: PageSpeed, meta tags, title, H1
- [ ] Score algorithm (simplified, 5 categories)
- [ ] Basic results page (no auth required)
- [ ] Auth (email + Google OAuth)
- [ ] Dashboard: 1 site, basic score history
- [ ] GMB check (basic: is it claimed?)
- [ ] Basic PDF report
- [ ] Stripe payment + 2 plans (Free + Starter)

### Phase 2 — Core SaaS (Months 3-4)
- [ ] Full crawler (50+ checks)
- [ ] AI Darija recommendations (Claude integration)
- [ ] Keyword tracker (SerpAPI)
- [ ] Competitor discovery (Google Places API)
- [ ] Email reports (Resend)
- [ ] Alerts system (email)
- [ ] GSC integration
- [ ] Multi-site management
- [ ] Pro plan

### Phase 3 — Advanced (Months 5-6)
- [ ] GMB OAuth + full manager
- [ ] AI Content Generator (all types)
- [ ] WhatsApp reports (Twilio/Meta)
- [ ] White label (Agency tier)
- [ ] Chrome extension
- [ ] Agency API
- [ ] City + sector landing pages (programmatic SEO)
- [ ] Moroccan payment gateways (CMI)

### Phase 4 — Growth (Months 7+)
- [ ] Mobile app (React Native)
- [ ] WordPress + Shopify plugins
- [ ] A/B testing feature
- [ ] SEO Academy
- [ ] Advanced Darija NLP
- [ ] Franchise/multi-location dashboard
- [ ] Referral program
- [ ] Partner portal (agencies)

---

## 14. KEY METRICS TO TRACK (Product Analytics)

| Metric | Tool | Target (Month 6) |
|---|---|---|
| Free audits per day | PostHog | 100+ |
| Free → Paid conversion | PostHog | 8-12% |
| MRR | Stripe | 50,000 DH |
| Churn rate | Custom | < 5%/month |
| NPS | Typeform | > 50 |
| Average session duration | PostHog | > 5 min |
| Reports generated | DB | 1000+/month |
| WhatsApp opt-in rate | DB | > 30% |

---

## 15. EXAMPLE AI PROMPTS (For Claude Integration)

### Issue Explanation (Darija)
```
You are an SEO expert. Explain the following SEO issue in simple Darija (Moroccan Arabic dialect, written in Arabic script). 
Keep it under 3 sentences. Use simple vocabulary that a non-technical business owner would understand.
Issue: {issue_type}
Business type: {business_type}
City: {city}
```

### Meta Title Generator
```
Generate an SEO-optimized meta title in French for a {business_type} located in {city}, Morocco.
Requirements:
- Max 60 characters
- Include the main keyword naturally
- Include the city name
- Be compelling and clear
- Do NOT use generic phrases like "Bienvenue"
Business name: {name}
Main service: {service}
```

### Google Post Generator (Darija)
```
Write a Google My Business post in Darija (Moroccan dialect) for a {business_type}.
Topic: {topic} (e.g., Ramadan offer, new service, event)
Tone: friendly, professional
Length: 150-200 characters
Include: a clear call to action
```

---

## 16. FOLDER STRUCTURE (Code)

```
scorema/
├── apps/
│   ├── web/                    (Next.js frontend)
│   │   ├── app/
│   │   │   ├── (public)/       (landing, blog, etc.)
│   │   │   ├── (auth)/         (login, register)
│   │   │   ├── dashboard/      (all dashboard pages)
│   │   │   └── api/            (Next.js API routes)
│   │   ├── components/
│   │   │   ├── ui/             (shadcn components)
│   │   │   ├── dashboard/      (dashboard-specific)
│   │   │   ├── charts/         (score charts, trends)
│   │   │   └── landing/        (landing page sections)
│   │   ├── lib/
│   │   │   ├── auth.ts
│   │   │   ├── api.ts
│   │   │   └── utils.ts
│   │   └── messages/           (i18n: fr.json, ar.json, dar.json)
│   │
│   └── api/                    (Hono.js backend)
│       ├── routes/
│       │   ├── analyse.ts
│       │   ├── sites.ts
│       │   ├── keywords.ts
│       │   ├── competitors.ts
│       │   ├── content.ts
│       │   ├── reports.ts
│       │   └── webhooks.ts
│       ├── workers/
│       │   ├── analyser.worker.ts      (main crawl job)
│       │   ├── score.worker.ts         (score calculator)
│       │   ├── report.worker.ts        (PDF generation)
│       │   ├── keyword.worker.ts       (rank tracking)
│       │   └── alert.worker.ts         (alert checker)
│       ├── services/
│       │   ├── google/
│       │   │   ├── pagespeed.ts
│       │   │   ├── places.ts
│       │   │   ├── mybusiness.ts
│       │   │   └── searchconsole.ts
│       │   ├── crawler/
│       │   │   ├── html-parser.ts
│       │   │   ├── lighthouse.ts
│       │   │   └── screenshot.ts
│       │   ├── ai/
│       │   │   ├── claude.ts
│       │   │   └── content-generator.ts
│       │   └── notifications/
│       │       ├── email.ts
│       │       └── whatsapp.ts
│       └── db/
│           ├── schema.prisma
│           └── migrations/
│
├── packages/
│   ├── score-engine/           (shared scoring logic)
│   ├── types/                  (shared TypeScript types)
│   └── darija-utils/           (Darija NLP helpers)
│
├── extension/                  (Chrome extension)
├── mobile/                     (React Native - Phase 2)
└── docs/                       (API docs, guides)
```

---

## 17. ENVIRONMENT VARIABLES NEEDED

```env
# Auth
NEXTAUTH_SECRET=
NEXTAUTH_URL=
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=

# Database
DATABASE_URL=
DIRECT_URL=
REDIS_URL=

# AI
ANTHROPIC_API_KEY=
OPENAI_API_KEY=

# Google APIs
GOOGLE_PAGESPEED_API_KEY=
GOOGLE_PLACES_API_KEY=
GOOGLE_MY_BUSINESS_CLIENT_ID=
GOOGLE_MY_BUSINESS_CLIENT_SECRET=
GOOGLE_SEARCH_CONSOLE_CLIENT_ID=

# SEO Data
SERP_API_KEY=
DATAFORSEO_LOGIN=
DATAFORSEO_PASSWORD=

# Email
RESEND_API_KEY=

# WhatsApp
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_WHATSAPP_NUMBER=

# Payments
STRIPE_SECRET_KEY=
STRIPE_WEBHOOK_SECRET=
CMI_MERCHANT_ID=
CMI_SECRET_KEY=

# Storage
CLOUDFLARE_R2_ACCESS_KEY=
CLOUDFLARE_R2_SECRET_KEY=
CLOUDFLARE_R2_BUCKET=

# Monitoring
SENTRY_DSN=
POSTHOG_API_KEY=
```

---

## 18. NOTES FOR AI AGENT EXECUTING THIS PLAN

1. **Start with Phase 1 MVP only** — don't overbuild
2. **Landing page is priority #1** — it's the sales engine
3. **Free audit must work without login** — critical for conversion
4. **Mobile-first design** — most Moroccan users are on phones
5. **RTL support from day 1** — don't add it as afterthought
6. **Use BullMQ queues** — crawls will fail if done synchronously
7. **Score algorithm must be deterministic** — same input = same score
8. **PDF generation should be async** — don't block the UI
9. **Darija text should be reviewed by native speaker** before launch
10. **Never store raw Google API tokens unencrypted**
11. **Add WhatsApp opt-in from the start** — huge channel in Morocco
12. **Build the comparison pages early** — high SEO traffic opportunity
13. **City + sector pages = programmatic SEO goldmine** — automate these

---

*End of Master Plan — ScoreMa v1.0*
*Total estimated dev time (solo full-stack): 3-4 months to Phase 2*
*With AI-assisted development: 6-8 weeks to MVP*
