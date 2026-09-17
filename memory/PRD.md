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

## Integrations
- SMTP / SMS / WhatsApp are **configurable MOCK adapters** (per user request) — admin-configurable settings, no real sending.
- No live third-party keys.

## Backlog / Roadmap
- **P1**: Technician portal write-actions (start/complete installation, resolve ticket) directly from the portal.
- **P1**: Advertiser self-service (request campaign, upload creative for approval) from portal.
- **P2**: Dynamic settings UI polish for SMTP/SMS/WhatsApp mock adapters + "send test" flows.
- **P2**: Seed sample Playlists (currently none seeded).
- **P2**: Rate-limit + API-key auth path (`apikey` middleware exists) documented in Swagger.
- **P3**: Real provider wiring (Resend/SendGrid, Twilio) if/when keys are provided.

## Test credentials
See `/app/memory/test_credentials.md`.
