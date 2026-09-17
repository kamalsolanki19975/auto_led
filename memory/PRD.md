# AutoAds Network — Product Requirements & Architecture

## Problem statement
A complete, production-ready Digital Out-of-Home (DOOH) advertising network platform for passenger autos. Manages the full lifecycle: Autos, Screens, Devices, SIMs, Campaigns, Advertisers, offline playback, Proof of Play, driver earnings, settlements, and profitability.

## Tech stack (as built)
- **Backend/Frontend**: Monolithic **Laravel 12** (PHP 8.2) with **Blade templates** + **Tailwind CSS (CDN)** + Alpine.js + Chart.js + Lucide icons.
  - NOTE: PRD originally requested React; during ask_human the user chose **HTML**, so a Laravel Blade monolith was built.
- **Database**: **MariaDB 10.11** (ARM64; MySQL 8 dropped due to arm64 limits). DB name `auto_ads`.
- **Runtime proxy**: Kubernetes ingress → port 3000 (frontend proxy) & 8001 (backend proxy) → both pass through to Nginx + PHP-FPM serving Laravel on :9000. `/api/*` → 8001, everything else → 3000.
  - `/app/backend/server.py` and `/app/frontend/proxy.js` are pass-throughs — DO NOT overwrite.

## Architecture map
```
/app/laravel/               # the monolith
  app/Models/               # ~55 domain models (BaseDocument not used; Eloquent + MariaDB)
  app/Http/Controllers/     # web controllers (ResourceController base) + Api/ controllers
  app/Services/             # ProofOfPlay, Settlement, Finance, Campaign, Device, Dashboard, etc.
  resources/views/          # Blade UI (layouts.app admin shell, layouts.portal portal shell)
  routes/web.php            # authenticated admin + portal routes
  routes/api.php            # /api/v1 REST + device API + /api/docs (+ /api/openapi.json)
  database/seeders/DemoDataSeeder.php  # 50 autos/devices, campaigns, playback, settlements
```

## Auth model
- Custom session auth (email + password, bcrypt) via AuthController.
- RBAC: roles ↔ permissions (many-to-many). `permission:` route middleware + `Gate::before` super-admin bypass.
- Portal users (advertiser/driver/owner/technician) redirect to their portal on login.
- API auth: Laravel Sanctum personal access tokens. Device API: custom `device.auth` middleware (rotating device token + X-Device-Uuid).

## Implemented (verified) — June 2026
- Full DB schema, ~55 models, domain services, demo seeder.
- Admin app: Dashboard, Network (autos/owners/drivers/screens/devices/sims), Advertising (advertisers/ads/approvals/campaigns/playlists/proof-of-play), Operations (installations/assets/maintenance/warranties/vendors), Finance (revenue/invoices/payments/expenses/rate-cards/earnings/settlements/profitability), Reports, Integrations (API apps/keys/logs, webhooks), Admin (users, roles & permissions, audit/email/notification logs, settings).
- **Role-specific portals**: Advertiser, Driver (with dispute-raising), Fleet Owner, Technician — dedicated `layouts.portal` shell.
- **Swagger/OpenAPI docs**: `/api/docs` (Swagger UI) + `/api/openapi.json` (OpenAPI 3.0 spec, 16 paths, user + device auth schemes).
- Device API end-to-end: authenticate → heartbeat → configuration → campaigns → content → playback-events → sync-events → status → acknowledgement.

## Bug fixes this session (fork continuation)
- Fixed `storage/logs` permissions (php-fpm/www-data could not write; masked real exceptions).
- Added missing `NotificationLog::user()` relationship (fixed /notification-logs 500).
- Fixed ambiguous `status` column in `campaigns()` many-to-many filter — in `DeviceController::showExtra` (web /devices/{id}) AND `DeviceApiController::campaigns` + `::content` (device API).
- Created missing views: `admin.user-index`, `admin.user-form`, `admin.role-index`, `admin.role-form`, `integrations.api-apps`, `integrations.api-logs`, `integrations.webhooks`, all `portal.*`, `layouts.portal`.
- Excluded non-existent `show` (users, roles) and `destroy` (roles) resource actions.
- **CRITICAL (reverse-proxy):** Fixed `https://host:80/...` redirects that broke real browsers (ERR_SSL_PROTOCOL_ERROR). Root cause: stale `APP_URL` + `trustProxies` trusting `X-Forwarded-Port=80`. Fix: `APP_URL` set to current preview host; `URL::forceScheme('https')` + `URL::forceRootUrl()` in `AppServiceProvider::boot`; tightened TrustProxies header mask (dropped X_FORWARDED_PORT).
- **CRITICAL (session):** Aligned session cookie with the HTTPS/Cloudflare edge — `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE=none` (browser was dropping the cookie between /login and /dashboard). Updated `SANCTUM_STATEFUL_DOMAINS` to current host.

## Security hardening (post-audit, June 2026)
- **SEC-001**: REST API now enforces token abilities + row-level ownership scoping (`ResourceApiController::authorize`) — portal users (advertiser/driver/owner) can only read their own records; cross-tenant show → 404; unmapped resources → 403.
- **SEC-002**: `APP_ENV=production`, `APP_DEBUG=false` — generic error pages, no stack/SQL/env leaks.
- **SEC-003**: Settlement dispute IDOR fixed (ownership check in `PortalController::raiseDispute`).
- **SEC-004**: `User` model uses explicit `$fillable` (was `$guarded=[]`).
- Hardening: `throttle:10,1` on `/api/v1/auth/login` + `/api/v1/device/authenticate`; API docs (`/api/docs`, `/api/openapi.json`) gated behind web auth + `integrations.api.view`; wildcard CORS header removed from spec.

## Ops / durability
- MariaDB datadir moved to persistent `/app/mysql-data` (was `/var/lib/mysql`, wiped on container reboot).
- `deploy/db-bootstrap.sh` runs at supervisor startup (`autoads-db-bootstrap` program, one-shot): ensures DB user + `auto_ads` DB, runs migrations, seeds only if empty. Protects against snapshot-restore data loss.

## Testing
- pytest regression suite: `/app/backend/tests/test_autoads.py` — **70/70 pass** (all 5 roles login, 46 admin routes, 4 portals, Users/Roles CRUD, Swagger, REST API, full Device API chain). ~20s runtime.
- pytest security suite: `/app/backend/tests/test_security.py` — **21 cases** covering SEC-001..004, docs gating, throttling. Combined run 91/92 (1 intermittent throttle race, test-side only). Reports: `/app/test_reports/iteration_1.json`, `iteration_2.json`.

## Integrations
- SMTP / SMS / WhatsApp are **configurable MOCK adapters** (per user request) — admin-configurable settings, no real sending.
- No live third-party keys.

## Major Upgrade — Phase 1 (Public Marketing Website) — June 2026 ✅
- Dark-themed, responsive public marketing site (`resources/views/public/*`): Home, How It Works, Advertisers, Auto Owners, Technology, Analytics, Developers/API, Pricing, About, FAQ, Contact, Network, Solutions, Legal.
- Dynamic stat counters (Alpine intersection observer) reading live DB values; SEO (sitemap.xml, robots.txt route, OG/Twitter meta); configurable branding (name/logo/tagline) via Settings → Branding.
- Website Leads CRM: public contact form → `leads` table (status pipeline) → admin `/leads` inbox + admin notification.
- Public routes namespaced `site.*` (advertiser marketing page at `/for-advertisers` to avoid `/advertisers` admin collision).

## Major Upgrade — Phase 2 (Admin Panel UI/UX Redesign) — June 2026 ✅
- Blueprint in `/app/design_guidelines.json` ("Operations Control Room": dark #070A10/#0B0F17/#111827 + amber #F59E0B, Barlow Condensed / Plus Jakarta Sans / JetBrains Mono).
- **Admin shell** (`layouts/app.blade.php`): collapsible sidebar groups (Alpine `x-collapse`), collapse-to-icon-rail toggle persisted via `localStorage aa_rail`, breadcrumb, refined global search (Ctrl/Cmd+K focus), quick-create, notifications bell, user menu w/ role badge, and a **contextual help drawer** (Module Guide + Keyboard Shortcuts Alt+H + DOOH Glossary; Esc closes). Mobile off-canvas sidebar.
- **Command Center dashboard** (`dashboard/index.blade.php`): 8 operational KPI cards + 8 financial KPI cards (icon chips, live-derived subtext), Revenue-vs-Expenses Chart.js line (amber/red themed), Tactical Alerts panel (severity + module links), Live Campaign Delivery table with gradient progress bars.
- **Shared CRUD templates** redesigned (`resources/index|show|form.blade.php`): count pill, icon search, status filter + clear, icon action buttons (view/edit/delete), rich empty states, hero detail cards w/ status badge + Edit, sectioned forms.
- **Bespoke views** brought to the new pattern: campaigns/invoices/settlements/payments list pages (search/filter/clear/row/action testids, guarded by `Route::has`), network detail pages (auto/device/driver/owner-show) got Edit buttons + heading style, leads-index heading + normalized `search-leads`/`row-leads-*` testids.
- Sidebar nav testids normalized to hyphenated slugs (`nav-autos`, `nav-campaigns`, `nav-advertisements-approvals`, …).
- **Cosmetic fixes**: deleted static `public/robots.txt` + Nginx `location = /robots.txt` now `try_files → index.php` (robots route returns **200**, not 404); Alpine collapse plugin added to public+admin layouts (FAQ accordion animates); footer social icons switched to inline SVGs (broken Lucide brand names gone); reset leftover branding test data (name→"AutoAds Network", tagline→default).
- **Tests**: `/app/backend/tests/test_phase2_ui.py` (33) + regressions — **149 passed / 1 skipped** across phase2+public+core; `test_security.py` **20 passed**. Report: `/app/test_reports/iteration_4.json`.
- **Known env limitation**: automated screenshot browser cannot log into admin (Cloudflare bot challenge on `/login`); verified via authenticated curl + pytest instead. Real users unaffected.

## Backlog / Roadmap
- **P0 (next major)**: Phase 3 — Master Data section (consolidated masters w/ global search, filters, import/export); Phase 4 — Reporting Center/MIS (100+ reports); Phase 5 — API Center (dev portal, testing console, scoped keys, webhooks); Phase 6 — Notification & CMS Center (email templates, prefs, in-app bell, website CMS).
- **P1**: Technician portal write-actions (start/complete installation, resolve ticket) directly from the portal.
- **P1**: Advertiser self-service (request campaign, upload creative for approval) from portal.
- **P2**: Dynamic settings UI polish for SMTP/SMS/WhatsApp mock adapters + "send test" flows.
- **P2**: Seed sample Playlists (currently none seeded).
- **P2**: Move Tailwind off `cdn.tailwindcss.com` to a Vite/CLI build (kills the prod-CDN console warning; carry-over).
- **P2**: Rate-limit + API-key auth path (`apikey` middleware exists) documented in Swagger.
- **P3**: Real provider wiring (Resend/SendGrid, Twilio) if/when keys are provided.

## Test credentials
See `/app/memory/test_credentials.md`.
