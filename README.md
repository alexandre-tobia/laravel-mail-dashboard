# Laravel Mail Dashboard

[![Latest Version on Packagist](https://img.shields.io/packagist/v/copain/laravel-mail-dashboard.svg?style=flat-square)](https://packagist.org/packages/copain/laravel-mail-dashboard)
[![GitHub Tests Action Status](https://github.com/alexandre-tobia/laravel-mail-dashboard/actions/workflows/run-tests.yml/badge.svg)](https://github.com/alexandre-tobia/laravel-mail-dashboard/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/copain/laravel-mail-dashboard.svg?style=flat-square)](https://packagist.org/packages/copain/laravel-mail-dashboard)

A mail dashboard for Laravel. The package listens to the `MessageSent` event and captures every outgoing email — **whatever mail driver you use** (`log`, `smtp`, `ses`, `array`, …) — then gives you a `/mail-dashboard` page (React + Tailwind + shadcn/ui) to browse them. No Mailpit, no external service, no database.

- 📬 Captures every sent email via Laravel's mail events — driver-agnostic
- 🗂 Stores each email as a JSON file on **any Laravel filesystem disk**: local by default, point it at your own S3/R2 bucket to keep full control of the data (required on ephemeral platforms like Laravel Cloud)
- 🖥 Rendered HTML preview (sandboxed iframe), plain text, raw MIME source and headers tabs
- 🏷 Records which Mailable or Notification class produced each email
- 📎 Attachment listing and download, inline `cid:` images rendered in the preview
- 🔍 Instant search across subject, sender, recipients, preview and source class
- 🗑 Delete one email or all of them
- 🔄 Auto-refreshes every 10 seconds, dark mode included

## Requirements

- PHP 8.4+
- Laravel 11, 12 or 13

## Installation

Install the package via composer:

```bash
composer require copain/laravel-mail-dashboard --dev
```

That's it. Send an email, then visit `/mail-dashboard` in your browser. The dashboard ships with pre-built assets — there is nothing to publish and no build step in your application.

> **Note:** capture and dashboard are only enabled when `APP_ENV=local` (or when `MAIL_DASHBOARD_ENABLED=true`). Every route returns a 404 otherwise, and nothing is stored.

## Configuration

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-mail-dashboard-config"
```

This is the contents of the published config file:

```php
return [
    // When disabled, outgoing emails are not captured and every
    // dashboard route returns a 404.
    'enabled' => env('MAIL_DASHBOARD_ENABLED', env('APP_ENV', 'production') === 'local'),

    // The URI path where the dashboard is served.
    'path' => env('MAIL_DASHBOARD_PATH', 'mail-dashboard'),

    // Middleware applied to every dashboard route.
    'middleware' => ['web'],

    // Where captured emails are stored (one JSON file each).
    'storage' => [
        'disk' => env('MAIL_DASHBOARD_DISK'),
        'path' => env('MAIL_DASHBOARD_STORAGE_PATH', 'mail-dashboard'),
    ],
];
```

### Changing the URL

Set `MAIL_DASHBOARD_PATH=mailbox` (or edit the config) and the dashboard moves to `/mailbox`.

### Authorization

Like Horizon and Telescope, the dashboard is open in the `local` environment. To allow access anywhere else — or to restrict it in local too — define a `viewMailDashboard` gate, for instance in `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewMailDashboard', function (?User $user) {
    return in_array($user?->email, [
        'you@example.com',
    ]);
});
```

When the gate is defined it has the final say in every environment. Without it, any non-local environment gets a 403.

### Choosing where your emails are stored

By default, captured emails are written to `storage/mail-dashboard` on the local filesystem. To keep control of the data — or because your platform's filesystem is ephemeral (Laravel Cloud, Vapor, containers) — point the dashboard at any disk defined in `config/filesystems.php`:

```dotenv
MAIL_DASHBOARD_DISK=s3
MAIL_DASHBOARD_STORAGE_PATH=mail-dashboard
```

Everything (capture, listing, preview, deletion) then goes through that disk, so your emails live in your own bucket.

## JSON API

The dashboard is powered by a small JSON API you can also use directly:

| Method | URI | Description |
| --- | --- | --- |
| `GET` | `/mail-dashboard/api/emails` | List all captured emails |
| `GET` | `/mail-dashboard/api/emails/{id}` | Full email (bodies, headers, raw MIME source) |
| `GET` | `/mail-dashboard/api/emails/{id}/attachments/{index}` | Download an attachment |
| `DELETE` | `/mail-dashboard/api/emails/{id}` | Remove one captured email |
| `DELETE` | `/mail-dashboard/api/emails` | Remove all captured emails |

## Frontend development

The UI lives in `resources/js` (React 19, Tailwind CSS 4, shadcn/ui) and is compiled to `resources/dist`, which is committed so consumers never need Node:

```bash
npm install
npm run build   # or: npm run dev (watch mode)
```

New shadcn components can be added with `npx shadcn@latest add <component>` — see `components.json`.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Alexandre Tobia](https://github.com/alexandre-tobia)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
