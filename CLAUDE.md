# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Laravel 13 (PHP 8.3+) guest-book app ("buku tamu") for an internship project (Magang Diskominfo). Staff log in to manage guest entries; visitors self-register through a public, token-gated QR link. Server-rendered Blade + Vite/Tailwind CSS v4 — no JS framework. UI copy is in Indonesian; system/validation messages are mixed Indonesian/English.

## Commands

- `composer dev` — run the full dev stack concurrently: `php artisan serve`, `queue:listen`, `pail` (live logs), and `npm run dev` (Vite). This is the primary way to work locally.
- `composer setup` — first-time bootstrap: install deps, copy `.env`, generate key, migrate, build assets.
- `composer test` — clears config then runs `php artisan test`.
- `php artisan test --filter=GuestInviteTest` — run a single test class (or `php artisan test tests/Feature/GuestInviteTest.php`).
- `./vendor/bin/pint` — format PHP code (Laravel Pint; run before considering work done).
- `php artisan pail` — tail application logs.
- `npm run build` — production asset build; `npm run dev` — Vite dev server only.

## Domain architecture

Three access tiers, wired in `routes/web.php`:

- **Public (no auth):** `/g/{invite}` self-registration form, reached by scanning a QR. The `{invite}` segment is a `GuestInvite` token (route-model binding resolves by `token`, not `id` — see `getRouteKeyName()`).
- **Guest-only:** `/login` (session auth).
- **Authenticated staff:** guest-entry CRUD (`Route::resource('guests')` minus `show`) and invite/QR management.

**Auth is intentionally minimal.** Login authenticates on **`name` + `password`** (not email — see `AuthController::login`). There is **no registration route or UI**; staff `User` rows must be created out-of-band (tinker/factory/seeder). `bootstrap/app.php` redirects authenticated users to `/guests` via `redirectUsersTo`.

**Permanent, toggleable QR invites** (`GuestInvite`) are the core concept:
- `mint()` creates a token that **never expires** and is **reusable by any number of guests** (submissions do not "burn" it).
- Usability is gated by two independent flags: `active` (a reversible on/off toggle via `activate()`/`deactivate()`) and `revoked_at` (a permanent one-way kill). `isUsable()` / `scopeUsable()` require `active === true` AND `revoked_at === null`.
- The legacy `expires_at`/`used_at` columns still exist but are **no longer used as gates** — don't reintroduce expiry/single-use logic without a deliberate design change.

**QR rendering** goes through `App\Support\QrCode` (bacon/bacon-qr-code):
- `svg()` returns an inline `HtmlString` embedded in Blade via `{!! ... !!}` — the SVG backend needs no `gd`/`imagick`.
- `png()` uses the **Imagick** backend, so it requires the `imagick` extension. The invite download route serves SVG by default and PNG on `?format=png`; keep the PNG path optional so environments without `imagick` still work.

**Rate limiting** guards abuse-prone writes via the `RateLimiter` facade (not middleware): public submissions are keyed per `token + IP` (`PublicGuestController`), and staff entry creation per `user id` (`GuestController`). Exceeding the limit throws a `ValidationException` surfaced on the form.

## Framework wiring (Laravel 13 slim skeleton)

- **No `app/Http/Kernel.php` or `app/Console/Kernel.php`.** All wiring lives in `bootstrap/app.php` via the `Application::configure()` fluent API: middleware, exception handling, routing, `redirectUsersTo`, and the `/up` health check. Register global/route middleware in the `withMiddleware()` closure, not a Kernel class. Console commands / scheduling go in `routes/console.php`.
- **Models use the `#[Fillable([...])]` PHP attribute** (Laravel 13 style) instead of a `$fillable` property. Casts are declared via the `casts()` method.
- **API responses:** exceptions render as JSON for any request matching `api/*` (configured in `bootstrap/app.php`). There is no `routes/api.php` yet — add it via `withRouting(api: ...)` if needed.
- **Database-backed infra:** sessions, cache, and queues default to the `database` driver. Default connection is **MySQL** (`web1`) for local dev, but tests run against **in-memory SQLite** (see `phpunit.xml`), so migrations must stay SQLite-compatible. The `testing` env (array cache/session/mail, sync queue) is defined entirely in `phpunit.xml` — no `.env.testing`.
- **Tests** (`tests/Feature`, `tests/Unit`) use PHPUnit with `RefreshDatabase`. Feature tests are the primary spec for invite/guest behavior — they encode the permanent+reusable+toggle contract above, so update them alongside any change to that lifecycle.
