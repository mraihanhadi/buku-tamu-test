# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

Laravel 13 web application (PHP 8.3+), currently a near-fresh skeleton — only the default `welcome` route/view and the stock `User` model exist. Frontend assets are built with Vite + Tailwind CSS v4 (no JS framework). This is an internship project (Magang Diskominfo); expect the domain code under `app/`, `routes/`, and `resources/views/` to grow from scratch.

## Commands

- `composer dev` — run the full dev stack concurrently: `php artisan serve`, `queue:listen`, `pail` (live logs), and `npm run dev` (Vite). This is the primary way to work locally.
- `composer setup` — first-time bootstrap: install deps, copy `.env`, generate key, migrate, build assets.
- `composer test` — clears config then runs `php artisan test`.
- `php artisan test --filter=SomeTest` — run a single test (or `php artisan test tests/Feature/ExampleTest.php`).
- `./vendor/bin/pint` — format PHP code (Laravel Pint; run before considering work done).
- `php artisan pail` — tail application logs.
- `npm run build` — production asset build; `npm run dev` — Vite dev server only.

## Architecture notes

- **Laravel 13 slim skeleton.** There is no `app/Http/Kernel.php` or `app/Console/Kernel.php`. All framework wiring lives in `bootstrap/app.php`: middleware, exception handling, routing, and the `/up` health check are registered there via the `Application::configure()` fluent API. Register global/route middleware in the `withMiddleware()` closure, not in a Kernel class.
- **API responses:** exceptions render as JSON for any request matching `api/*` (configured in `bootstrap/app.php`). There is no `routes/api.php` yet — add it via `withRouting(api: ...)` if API routes are needed.
- **Console commands / scheduling** go in `routes/console.php`, not a Kernel.
- **Database-backed infra:** sessions, cache, and queues all default to the `database` driver (see `.env`). The default connection is **MySQL** (`web1` database) for local dev, but tests run against **in-memory SQLite** (see `phpunit.xml`), so migrations must stay SQLite-compatible.
- **Tests** use PHPUnit (`tests/Unit`, `tests/Feature`) with an isolated `testing` environment defined entirely in `phpunit.xml` (array cache/session/mail, sync queue) — no `.env.testing` needed.
