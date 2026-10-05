# Safi Tailoring Shop

A complete e-commerce platform for a custom tailoring business: a trilingual public storefront (English / Pashto / Farsi with full RTL support) and a multilingual admin panel for managing products, orders, customers, translations and settings.

Built with Laravel 13, PHP 8.3, MySQL, Tailwind CSS v4, Alpine.js, Chart.js and Vite.

## Features

### Storefront
- Catalog with ready-made products, custom tailoring and in-shop services
- Product search, category browsing, featured products on the homepage
- Cart and checkout with per-item measurement details for tailoring orders
- Guest checkout **and** registered customer accounts (order history, account details)
- Login with email or WhatsApp phone number, plus password reset
- Order tracking by order number
- Contact page with message intake
- Full SEO: per-page meta titles/descriptions, canonical URLs, `hreflang` alternates for all three languages plus `x-default`, Open Graph / Twitter cards, JSON-LD structured data, `sitemap.xml` and a dynamic `robots.txt`
- Language switcher in the header (`/en/…`, `/ps/…`, `/fa/…`); Pashto and Farsi render right-to-left

### Admin panel (`/admin`)
- Dashboard with revenue/order charts (last 14 days) and order-status breakdown
- Products CRUD with images (main + gallery), types, sizes, stock tracking
- Categories CRUD with images and multilingual names/descriptions
- Order management: status workflow (pending → confirmed → in tailoring → ready → delivered/cancelled), payment status, per-order status history, click-to-chat WhatsApp contact with the customer
- Customer/user management, role handling, and block/suspend of customer accounts
- Contact messages inbox
- Settings: shop identity, currency, delivery fees, homepage hero, social links, HesabPay credentials, WhatsApp Cloud API
- **Translation manager**: add or edit any interface string (words or longer articles) in English, Pashto and Farsi without touching code
- Language switcher in the admin header (session-based — URLs stay `/admin/…`)

### Payments — HesabPay
- Sandbox and production environments, switchable from Settings
- Webhook confirmation with HMAC-SHA256 signature verification; until a webhook secret is configured, webhook calls are rejected and payments are confirmed by redirect only

> **Security note:** a live HesabPay API key was previously exposed in this repository. Treat it as compromised and **rotate it in the HesabPay merchant dashboard** before going live. Never rely solely on redirect data to confirm a transaction — always verify via the webhook or API callback.

### Notifications — WhatsApp
- Click-to-chat links for order updates (free, no API required)
- Optional automatic customer notifications via the Meta WhatsApp Cloud API (free tier), configured in Settings

## Getting started

```bash
composer install
npm install
cp .env.example .env        # then set DB credentials
php artisan key:generate
php artisan migrate --seed  # seeds admin/customer accounts, catalog and all 695 interface translations
php artisan storage:link
npm run build
php artisan serve
```

Default seeded accounts (change the passwords before deploying):

| Role     | Email                    | Password   |
|----------|--------------------------|------------|
| Admin    | admin@safitailoring.com  | password   |
| Customer | customer@example.com     | password   |

For development assets: `npm run dev`.

## Multilingual system

- Locales are defined in `app/Support/Locales.php` (English, Pashto, Farsi with RTL flags).
- The storefront is locale-prefixed (`/{locale}/…`); the admin panel uses a session locale via the switcher.
- All interface text flows through the `t('group.key', 'English default')` helper (`app/helpers.php`), backed by the `translations` database table. Empty Pashto/Farsi values fall back to English.
- Product and category names/descriptions are stored as JSON columns translated in all three languages.
- **Editing translations:** use the admin panel's Translation manager (no code needed).
- **Adding strings in code:** write `t('group.key', 'English text')`, then regenerate the master list and seed the new keys:

```bash
php storage/app/extract_keys.php                # scans the code, rewrites database/data/translations/en.php + storage/app/keys.tsv
php artisan db:seed --class=TranslationsSeeder  # inserts new keys; existing translations are kept
```

The English/Pashto/Farsi master files live in `database/data/translations/{en,ps,fa}.php`.

## Testing

```bash
php artisan test
```

## Deployment checklist

- Rotate the HesabPay API key (see security note above) and set the webhook secret in Settings
- Change the seeded admin/customer passwords
- Configure a real database, `APP_URL` and HTTPS
- Point a domain at the server and update `APP_URL` so canonical/hreflang/SEO tags match
- Set the shop's WhatsApp number (and optionally Meta Cloud API credentials) in Settings
- Configure SMTP/mail for password-reset emails
