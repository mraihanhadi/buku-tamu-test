# QR Code Generation

This document explains how QR codes are generated and served in this app.

## What the QR encodes

A QR code in this app is **not** magic data — it simply encodes the public
self-registration **URL** for a `GuestInvite`:

```
https://<host>/g/<token>
```

That URL is the named route `public.guests.create` (`routes/web.php`), which
route-model-binds the `{invite}` segment to a `GuestInvite` **by its `token`**
(not `id` — see `GuestInvite::getRouteKeyName()`). So scanning the QR opens the
public guest form for that specific invite.

The token itself is a random 48-character string created once, when the invite
is minted:

```php
// app/Models/GuestInvite.php
'token' => Str::random(48),
```

The token **never changes and never expires**. Whether a scan actually works is
gated separately by the `active` flag and `revoked_at` (see
[Lifecycle](#lifecycle-what-the-qr-does-not-encode)), so the printed QR image
stays valid indefinitely.

## The renderer: `App\Support\QrCode`

All rendering goes through the `App\Support\QrCode` helper
(`app/Support/QrCode.php`), a thin wrapper around the
[`bacon/bacon-qr-code`](https://github.com/Bacon/BaconQrCode) library. It
exposes two static methods:

### `QrCode::svg(string $text, int $size = 280): HtmlString`

- Uses BaconQrCode's **SVG backend** (`SvgImageBackEnd`).
- Returns an `Illuminate\Support\HtmlString`, so it embeds directly into a Blade
  view with `{!! ... !!}`:

  ```blade
  {{-- resources/views/invites/show.blade.php --}}
  {!! \App\Support\QrCode::svg($url, 260) !!}
  ```

- **No `gd` or `imagick` extension required** — this is the default path and
  works everywhere. Being vector, it also scales cleanly for print.

### `QrCode::png(string $text, int $size = 512): string`

- Uses BaconQrCode's **Imagick backend** (`ImagickImageBackEnd`).
- Returns raw PNG binary.
- **Requires the `imagick` PHP extension.** This path is optional — the app
  works fine without `imagick` as long as you stay on SVG.

Both methods take the text to encode (here, the invite URL) and a pixel size,
and internally build a `Writer` around an `ImageRenderer` with a
`RendererStyle($size, 1)` (the `1` is the quiet-zone margin in modules).

## Where QR codes appear

### 1. On-screen (inline SVG)

The invite detail page renders the QR inline via the `svg()` helper. Nothing is
saved to disk — the SVG markup is generated on each request and printed into the
HTML. This is why viewing an invite never needs `imagick`.

### 2. Download (SVG or PNG)

`GuestInviteController@download` (route `invites.download`,
`/invites/{invite}/download`) serves the QR as a standalone downloadable file:

```php
$url = route('public.guests.create', $invite);
$png = $request->query('format') === 'png';

[$body, $type, $ext] = $png
    ? [QrCode::png($url, 512), 'image/png', 'png']
    : [(string) QrCode::svg($url, 512), 'image/svg+xml', 'svg'];
```

- **Default:** SVG (`image/svg+xml`) — vector, prints cleanly, no extra
  extensions.
- **`?format=png`:** raster PNG (`image/png`) — convenient for pasting into
  documents and chat apps, but requires `imagick` on the server.

Both are returned as an attachment named `qr-<token>.<ext>`.

## Lifecycle (what the QR does *not* encode)

The QR image only ever carries the URL. Usability is decided server-side at scan
time by `GuestInvite::isUsable()`:

```php
return $this->active && $this->revoked_at === null;
```

- **`active`** — a reversible on/off toggle (`activate()` / `deactivate()`,
  exposed via `invites.toggle`). Turning a QR off does **not** change its token,
  so an already-printed QR can be turned back on later.
- **`revoked_at`** — a permanent, one-way kill (`invites.destroy`). Once set, the
  invite is gone for good.

An invite is **reusable** — any number of guests can submit through the same QR;
a submission never "burns" it. The legacy `expires_at` / `used_at` columns still
exist but are **not** used as gates; don't reintroduce expiry/single-use logic
without a deliberate design change.

## Flow summary

```
Staff mints invite ──► GuestInvite (random 48-char token, active=true)
        │
        ▼
route('public.guests.create', $invite)  ──►  https://host/g/<token>
        │
        ▼
QrCode::svg($url)  /  QrCode::png($url)   (encodes the URL as a QR)
        │
        ├─► inline on the invite page ({!! ... !!})
        └─► download as .svg (default) or .png (?format=png)
        │
        ▼
Guest scans ──► opens /g/<token> ──► isUsable()? active && !revoked_at
                                         ├─ yes ─► registration form
                                         └─ no  ─► blocked
```

## Extending

- **Change the encoded content:** update the `$url` / `$text` passed into
  `QrCode::svg()` / `QrCode::png()`. Everything downstream is just a QR of that
  string.
- **Change size or margin:** adjust the `$size` argument, or the quiet-zone
  value in `RendererStyle($size, 1)` inside `app/Support/QrCode.php`.
- **Avoid the PNG dependency:** keep serving SVG; only the PNG path needs
  `imagick`.
