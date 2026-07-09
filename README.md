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
