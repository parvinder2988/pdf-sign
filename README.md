# PDF Multi Sign Laravel App

This Laravel app shows a predefined PDF, provides a link to open it, and lets a driver enter their name, email, driver number, driver run number, and signature on a signing page.

## Requirements

- PHP 8.2 or newer
- Composer
- XAMPP MySQL/MariaDB

## Setup

```bash
cd "/Applications/XAMPP/xamppfiles/htdocs/application"
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Then open:

```text
http://127.0.0.1:8000
```

Signature report:

```text
http://127.0.0.1:8000/signatures/report
```

Default report password:

```text
admin123
```

Change it in `.env` with `REPORT_PASSWORD=...`, then run `php artisan config:clear`.

The project is installed at:

```text
/Applications/XAMPP/xamppfiles/htdocs/application
```

If you use XAMPP Apache instead of `php artisan serve`, start Apache in the XAMPP manager and open:

```text
http://127.0.0.1/application/
```

## Notes

- The predefined PDF is stored at `public/pdfs/ilovepdf-merged.pdf`.
- The PDF is previewed in the browser.
- Driver name, email, driver number, driver run number, signature image, and signed PDF file paths are saved in MySQL database `pdf_sign`.
- Email OTP verification is required before the signature can be drawn and saved.
- OTP requests are saved in MySQL table `signature_otps` with the plain code, hashed code, expiry time, IP address, and optional verification timestamp.
- By default `MAIL_MAILER=log`, so OTP emails are written to `storage/logs/laravel.log` during local development.
- To send real emails, configure SMTP values in `.env` such as `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, and `MAIL_FROM_ADDRESS`.
- Signature images are stored in `storage/app/driver-signatures`.
- Signed PDFs are stored in `storage/app/signed-pdfs`.
- The exported `driver-signature.pdf` is generated in the browser.
- The report page exports all saved signatures in table format as `signature-report.pdf`.
- The report page and stored signature files are password protected.

## Heroku setup

This app includes a `Procfile` so Heroku serves Laravel from the `public/` folder.

Create and configure the Heroku app:

```bash
heroku create your-app-name
heroku config:set APP_NAME="PDF Multi Sign"
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set APP_KEY="$(php artisan --no-ansi key:generate --show)"
heroku config:set LOG_CHANNEL=errorlog
heroku config:set CACHE_STORE=file
heroku config:set SESSION_DRIVER=file
heroku config:set QUEUE_CONNECTION=sync
heroku config:set REPORT_PASSWORD="change-this-password"
heroku config:set MAIL_MAILER=log
```

Add a MySQL database add-on, then set one database URL variable:

```bash
heroku config:set DB_CONNECTION=mysql
heroku config:set DB_URL="mysql://username:password@host:3306/database_name"
```

If your MySQL add-on provides `JAWSDB_URL` or `CLEARDB_DATABASE_URL`, the app can read that automatically.

Deploy and migrate:

```bash
git push heroku main
heroku run php artisan migrate --force
heroku open
```

Important Heroku note: files saved under `storage/app`, including signature images and signed PDFs, are not permanent on Heroku's normal dyno filesystem. For production, use persistent storage such as S3-compatible object storage.

## Vercel setup

This app includes `vercel.json` and `api/index.php` for Vercel. Vercel does not run PHP as an official runtime, so the app uses Vercel's recommended community PHP runtime, `vercel-php`.

Recommended Vercel environment variables:

```bash
APP_NAME="PDF Multi Sign"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your-generated-key
APP_URL=https://your-vercel-domain.vercel.app
DB_CONNECTION=mysql
DB_URL=mysql://username:password@host:3306/database_name
CACHE_STORE=array
SESSION_DRIVER=cookie
QUEUE_CONNECTION=sync
REPORT_PASSWORD=change-this-password
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="PDF Sign"
```

Generate the app key locally:

```bash
php artisan --no-ansi key:generate --show
```

Run migrations against the production MySQL database before or after deployment:

```bash
DB_URL="mysql://username:password@host:3306/database_name" php artisan migrate --force
```

Vercel filesystem note: Vercel functions have a read-only filesystem except `/tmp`, so signature images and signed PDFs are also saved in MySQL blob columns for report/export access. Use an external MySQL database such as PlanetScale, Aiven, Railway, or another public MySQL provider.
