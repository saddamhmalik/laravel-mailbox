# Laravel Mailbox

A modern email testing and preview tool for Laravel — like MailHog or Mailpit, but deeply integrated into your application.

![Mailbox Inbox](docs/screenshots/inbox.png)
![Email Preview](docs/screenshots/preview.png)

## Features

- Custom Symfony Mailer transport (not event-based capture)
- Beautiful Livewire inbox UI with dark mode
- HTML, plaintext, raw MIME, and headers preview
- Search and filtering
- Attachment metadata
- Powerful testing assertions and Pest helpers
- SQLite / database / array / cache storage drivers
- Zero-config local development workflow

## Requirements

- PHP 8.2+
- Laravel 12+ (forward compatible with Laravel 13)
- Livewire 3

**Repository:** [github.com/saddamhmalik/laravel-mailbox](https://github.com/saddamhmalik/laravel-mailbox)

## Installation

Laravel Mailbox is a **development and testing** tool. Install it as a dev dependency and use the `mailbox` mail driver only in `local` or `testing` environments.

### 1. Install the package

#### From GitHub (use this until the package is on Packagist)

Composer does not install from GitHub automatically. Add the [repository](https://github.com/saddamhmalik/laravel-mailbox) to your **Laravel application’s** `composer.json`, then require the package:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/saddamhmalik/laravel-mailbox"
        }
    ],
    "require-dev": {
        "laravel-mailbox/mailbox": "^1.0"
    }
}
```

```bash
composer update laravel-mailbox/mailbox --dev
```

If you have not published a version tag yet, use the `main` branch instead:

```json
"laravel-mailbox/mailbox": "dev-main"
```

Release tags on GitHub (recommended):

```bash
git tag v1.0.0
git push origin v1.0.0
```

#### From Packagist (after publishing)

Once the package is submitted to [Packagist](https://packagist.org) and linked to this repo, you can install without a `repositories` entry:

```bash
composer require laravel-mailbox/mailbox --dev
```

The service provider and `Mailbox` facade are registered automatically via Laravel package discovery in both cases.

### 2. Run the installer

```bash
php artisan mailbox:install
```

This command publishes:

| Tag | Output |
|-----|--------|
| `mailbox-config` | `config/mailbox.php` |
| `mailbox-migrations` | `database/migrations/*_create_mailbox_emails_table.php` |
| `mailbox-assets` | `public/vendor/mailbox/mailbox.css` and `mailbox.js` |

You will be prompted to run migrations. To run them non-interactively:

```bash
php artisan mailbox:install --migrate
```

Other options:

```bash
php artisan mailbox:install --no-assets   # Skip publishing CSS/JS
php artisan mailbox:install --force       # Overwrite existing published files
```

If you skipped migrations during install, run them manually:

```bash
php artisan migrate
```

### 3. Configure mail capture

Add the mailbox mailer to `config/mail.php`:

```php
'mailers' => [
    // ...
    'mailbox' => [
        'transport' => 'mailbox',
    ],
],
```

Set the default mailer in `.env` for **local development only**:

```env
MAIL_MAILER=mailbox
```

In production, keep your real mailer (`smtp`, `ses`, `postmark`, etc.) and do **not** set `MAIL_MAILER=mailbox`.

### 4. Optional environment variables

```env
# Inbox URL path (default: mailbox → /mailbox)
MAILBOX_ROUTE_PREFIX=mailbox

# Restrict UI to local & testing (default: true)
MAILBOX_LOCAL_ONLY=true

# Disable routes and UI entirely
MAILBOX_ENABLED=true

# Storage: database (default), sqlite, array, cache
MAILBOX_STORAGE_DRIVER=database
MAILBOX_MAX_EMAILS=1000
```

### 5. Open the inbox

```bash
php artisan mailbox:open
```

Or visit the inbox in your browser:

```text
{APP_URL}/mailbox
```

Examples:

- `http://localhost:8000/mailbox` (default `php artisan serve`)
- `https://my-app.test/mailbox` (Laravel Herd / Valet)

Named route: `mailbox.index` — use `route('mailbox.index')` in your app.

### 6. Send a test email

Any mail sent while `MAIL_MAILER=mailbox` is active will appear in the inbox:

```php
use Illuminate\Support\Facades\Mail;

Mail::raw('Hello from Mailbox!', function ($message) {
    $message->to('demo@example.com')
        ->subject('Test Email');
});
```

Refresh `/mailbox` or wait for the UI poll interval (default 3 seconds).

### Assets

Published assets are served from `public/vendor/mailbox/`. If you did not publish assets, the package serves them from `/mailbox/assets/mailbox.css` and `/mailbox/assets/mailbox.js`.

Set `MAILBOX_USE_VITE=true` in `.env` if you build your own assets with Vite.

### PHPUnit / Pest

For automated tests, use the `array` driver so emails stay in memory. See [docs/TESTING.md](docs/TESTING.md).

```xml
<env name="MAIL_MAILER" value="mailbox"/>
<env name="MAILBOX_STORAGE_DRIVER" value="array"/>
<env name="MAILBOX_LOCAL_ONLY" value="false"/>
```

## Configuration

Publish config with:

```bash
php artisan vendor:publish --tag=mailbox-config
```

Key options in `config/mailbox.php`:

| Option | Description |
|--------|-------------|
| `enabled` | Enable/disable the package |
| `route.prefix` | URL prefix (default: `mailbox`) |
| `route.middleware` | Route middleware stack |
| `storage.driver` | `database`, `sqlite`, `array`, `cache` |
| `storage.max_emails` | Auto-prune oldest beyond this count |
| `local_only` | Restrict UI to local/testing environments |
| `authorization` | Callable gate for custom auth |

### Authorization

```php
// AppServiceProvider.php
config()->set('mailbox.authorization', fn ($request) => $request->user()?->isAdmin() ?? false);
```

## Testing

```php
use Illuminate\Support\Facades\Mail;
use LaravelMailbox\Facades\Mailbox;

Mail::to('user@example.com')->send(new WelcomeMail());

Mailbox::assertSent();
Mailbox::assertSentCount(1);
Mailbox::assertSent(fn ($email) => $email->hasTo('user@example.com'));

Mailbox::assertNothingSent();
```

### Pest helpers

```php
Mail::to('john@example.com')->send(new WelcomeMail());

expectMailbox()
    ->to('john@example.com')
    ->withSubject('Welcome')
    ->assert();
```

Use the `array` storage driver in `phpunit.xml` for faster isolated tests:

```xml
<env name="MAILBOX_STORAGE_DRIVER" value="array"/>
```

## Artisan Commands

| Command | Description |
|---------|-------------|
| `mailbox:install` | Publish config & migrations |
| `mailbox:clear` | Clear all captured emails |
| `mailbox:prune` | Remove emails older than N days |
| `mailbox:open` | Open inbox in browser |

## Architecture

```
Transport (Symfony) → Parser → Capture Service → Storage → Repository → UI / Testing API
```

The package uses a custom `MailboxTransport` that intercepts messages in `doSend()`, normalizes them into DTOs, and persists via pluggable storage drivers.

## Security

- UI is **local-only** by default (`MAILBOX_LOCAL_ONLY=true`)
- Custom authorization callback supported
- Middleware stack is fully configurable

Never expose the mailbox UI publicly in production without proper authentication.

## Roadmap

- SMTP server mode
- Multi-project inboxes
- Webhook testing
- Email analytics
- API access

## Package development

Clone the repository:

```bash
git clone https://github.com/saddamhmalik/laravel-mailbox.git
cd laravel-mailbox
composer install
composer setup
composer serve
```

See [docs/WORKBENCH.md](docs/WORKBENCH.md) for the local demo app.

## License

MIT © Laravel Mailbox
