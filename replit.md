# NorthRank / MaghrebSEO — B2B SaaS SEO Platform

A Laravel 12 full-stack SEO SaaS platform for localized SEO auditing and competitor intelligence, targeting the Maghreb market.

## Architecture

- **Backend**: Laravel 12 on port 8000 (Blade templates + JSON API + Sanctum token auth)
- **Admin Panel**: Filament v3 at `/admin` — platform super-admin (Workspaces + Users)
- **Database**: SQLite at `database/database.sqlite`
- **Multi-tenancy**: Single DB with `tenant_id` scoping on all resource tables

## Tech Stack

### Backend (Laravel)
- **PHP 8.2** + **Laravel 12**
- **Laravel Sanctum** — token-based API auth
- **Filament v3.3** — admin panel (TenantResource, UserResource, StatsOverview widget)
- **Blade templates** — server-rendered views with Tailwind CSS
- **Guzzle HTTP** — web page fetching + DataForSEO SERP API calls
- **Symfony DOMCrawler** — HTML parsing
- **DomPDF** — white-label PDF export (uses tenant logo + brand colour)
- **Maatwebsite Excel** — CSV export
- **Laravel Queues** (database driver) — async scan + rank check jobs
- **PayPal** — subscription payments (Pro, Guru, Business plans)

## Authentication Flows

### Email Verification
- `User` model implements `MustVerifyEmail`
- Registration fires `Registered` event → verification email sent automatically (logged in dev via `MAIL_MAILER=log`)
- Verification link in email → signed URL pointing to Laravel's `verification.verify` route
- Laravel verifies signed URL, marks email as verified, redirects to dashboard
- Unverified users are redirected to `/verify-email` on dashboard access
- Resend endpoint: `POST /api/auth/email/resend` (requires Sanctum auth token)
- Blade view: `resources/views/auth/verify-email.blade.php`

### Password Reset
- `POST /api/auth/forgot-password` — sends reset link via email (logged in dev)
- `POST /api/auth/reset-password` — validates token + resets password
- Reset link URL: Laravel's `password.reset` route
- Blade views: `resources/views/auth/forgot-password.blade.php`, `reset-password.blade.php`

## Stripe Integration

Stripe was previously used via the Next.js frontend. This has been removed. PayPal is now the primary payment provider.

## Admin Panel — Filament

- URL: `/admin` (served by the Laravel app on port 8000)
- Login: any user with `is_admin = true`
- Default superadmin: `admin@northrank.io` / `admin123`
- Create/promote admins: `php artisan admin:make {email}`

### Resources
| Resource | Path | Description |
|----------|------|-------------|
| Workspaces | `/admin/tenants` | CRUD: name, plan, limits, branding fields |
| Users | `/admin/users` | Edit: name, email, workspace assignment, is_admin toggle |

### Dashboard Widgets
- **StatsOverview**: Workspaces count, Total Users, Total SEO Scans, Keywords Tracked

## API Endpoints (all under `/api`)

### Auth
| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/auth/register` | Register + auto-create workspace + get token |
| POST | `/api/auth/login` | Login + get token |
| POST | `/api/auth/logout` | Revoke token |
| GET | `/api/auth/me` | Get current user + tenant |

### Workspace (Tenant)
| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/workspace` | Get current workspace (includes logo_url, branding) |
| POST | `/api/workspace` | Create workspace |
| PATCH | `/api/workspace` | Update name, agency_name, agency_website, primary_color |
| POST | `/api/workspace/logo` | Upload logo (multipart, max 2 MB, owner only) |
| DELETE | `/api/workspace/logo` | Remove logo (owner only) |

### Projects
| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/projects` | List all projects (tenant-scoped) |
| POST | `/api/projects` | Create project |
| GET | `/api/projects/{id}` | Project detail + audit history + stats |
| PATCH | `/api/projects/{id}` | Update project |
| DELETE | `/api/projects/{id}` | Soft-delete project |

### Keywords & Rank Tracking
| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/projects/{id}/keywords` | List keywords (paginated) |
| POST | `/api/projects/{id}/keywords` | Add keywords (bulk, up to 100) |
| DELETE | `/api/projects/{id}/keywords/{kwId}` | Remove keyword |
| GET | `/api/projects/{id}/keywords/summary` | All keywords + latest rank + stats |
| POST | `/api/projects/{id}/keywords/check` | Trigger async rank check (returns batch_id) |
| GET | `/api/projects/{id}/keywords/batch/{batchId}` | Poll batch job status |
| GET | `/api/projects/{id}/keywords/{kwId}/rankings` | Ranking history (7–365 days) |

### Competitors
| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/projects/{id}/competitors` | List competitors |
| POST | `/api/projects/{id}/competitors` | Add competitor (max 5) |
| DELETE | `/api/projects/{id}/competitors/{compId}` | Remove |
| GET | `/api/projects/{id}/competitors/matrix` | Side-by-side rank matrix |

### SEO Scans
| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/scans` | List scans (paginated) + stats |
| POST | `/api/scans` | Create scan (accepts optional `project_id`) |
| GET | `/api/scans/{uuid}` | Full scan results |
| GET | `/api/scans/{uuid}/status` | Poll scan status |
| DELETE | `/api/scans/{uuid}` | Soft-delete scan |
| GET | `/api/scans/{uuid}/export/pdf` | Download white-label PDF (uses tenant logo) |
| GET | `/api/scans/{uuid}/export/csv` | Download CSV |

## Database Schema

### Phase 2 (Tenancy)
- **tenants** — `id, name, slug, plan, scan_limit_per_day`
- **users** — added `tenant_id FK`, `tenant_role (owner/member)`
- **projects** — `id, tenant_id FK, name, url, description`
- **seo_scans** — added `project_id FK (nullable)`

### Phase 3 (Rank Tracking)
- **keywords** — `id, project_id, tenant_id, keyword, location_code, language_code, device, is_active, last_checked_at`
- **keyword_rankings** — `id, keyword_id, checked_at, rank, previous_rank, url, domain, title, search_volume, cpc, competition, serp_features (JSON)`
- **rank_check_batches** — `id, project_id, tenant_id, status, keywords_count, completed/failed counts`
- **project_competitors** — `id, project_id, tenant_id, name, url` (max 5 per project)
- **competitor_rankings** — `id, keyword_id, competitor_id, checked_at, rank, url, title`

### Phase 4 (Admin + White-label)
- **tenants** — added `logo_path, primary_color, agency_name, agency_website`
- **users** — added `is_admin` boolean (gates Filament panel access)
- Logo files stored at `storage/app/public/logos/{tenant_id}/` (symlinked to `public/storage`)

## White-label PDF Reports

- `ScanController::exportPdf()` loads `$tenant` for the authenticated user
- PDF blade (`resources/views/exports/scan-pdf.blade.php`) uses:
  - `$tenant->logo_base64` — embedded logo image (no external URL needed by DomPDF)
  - `$tenant->primary_color` — header + accent colour
  - `$tenant->agency_name` / `$tenant->agency_website` — branded header + footer
  - Falls back gracefully to "NorthRank" if no branding is set

## Project Structure

```
(root) — Laravel Full-Stack App
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── ScanController.php
│   │   │   ├── TenantController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── KeywordController.php
│   │   │   └── CompetitorController.php
│   │   └── (web controllers for Blade views)
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── TenantResource.php    — Workspaces CRUD (admin)
│   │   │   └── UserResource.php      — Users + is_admin toggle (admin)
│   │   └── Widgets/
│   │       └── StatsOverview.php     — 4-stat dashboard widget
│   ├── Console/Commands/
│   │   ├── MakeAdminUser.php         — php artisan admin:make {email}
│   │   ├── CheckAllKeywordRankings.php
│   │   └── CheckExpiredSubscriptionsCommand.php
│   ├── Models/
│   │   ├── Tenant.php                — logo_url + logo_base64 accessors
│   │   ├── User.php                  — FilamentUser interface (canAccessPanel)
│   │   └── ...
│   └── Providers/Filament/
│       └── AdminPanelProvider.php
├── resources/views/                  — Blade templates
├── routes/
│   ├── web.php                       — Web routes (Blade views)
│   ├── api.php                       — JSON API routes
│   └── auth.php                      — Auth routes (Breeze-style)
└── database/migrations/
```

## Workflows

- **Start application**: `php artisan serve --host=0.0.0.0 --port=8000` → port 8000 (console)
- **Queue worker** (manual): `php artisan queue:work`

## Environment Variables & Secrets

- `DB_CONNECTION=sqlite`, `DB_DATABASE=...`
- `QUEUE_CONNECTION=database`
- `DATAFORSEO_LOGIN` / `DATAFORSEO_PASSWORD` — optional (mock mode when absent)

## Development Notes

- Auth tokens in `localStorage`; 401 auto-redirects to `/login`
- Filament access gated by `users.is_admin = true` — all other users are denied
- Logo upload: `POST /api/workspace/logo` multipart, max 2 MB, owner-only
- Logo stored in `storage/app/public/logos/{tenant_id}/` — `public/storage` symlink required (`php artisan storage:link`)
- `KeywordRanking.trend` is a computed attribute: `previous_rank - rank`
- Location codes: 2504=Morocco, 2012=Algeria, 2788=Tunisia

## Competitor Intelligence

- Single SERP call extracts positions for own domain + all competitors simultaneously
- Matrix view: green=we rank higher, red=competitor ranks higher, position delta badges

## Automation — Daily Rank Check

- `php artisan ranktracker:check-all` dispatches rank check batches
- Registered in `routes/console.php` at `dailyAt('03:00')` (5am Morocco time)
- Queue processor: every minute via scheduler
