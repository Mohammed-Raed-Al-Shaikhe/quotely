# Production launch checklist

The application code provides secure tenant isolation, authentication, authorization, trials, audit logs, rate limits, security headers, and tested quote-to-invoice workflows. Complete these environment-specific steps before accepting paying customers.

## Infrastructure

- Use PHP 8.2+, PostgreSQL 16+, Redis, HTTPS, and private S3-compatible storage.
- Set `APP_ENV=production`, `APP_DEBUG=false`, a unique `APP_KEY`, and the canonical HTTPS `APP_URL`.
- Set secure database, Redis, mail, object-storage, and payment credentials through the hosting secret manager.
- Run queue workers under a process supervisor and schedule `php artisan schedule:run` every minute.
- Deploy with `composer install --no-dev --classmap-authoritative`, `php artisan migrate --force`, and `php artisan optimize`.
- Configure daily encrypted database backups and perform a restoration drill.

## Commercial configuration

- Create Stripe products/prices and install Laravel Cashier before enabling the disabled checkout buttons.
- Verify Stripe webhooks and make subscription updates idempotent.
- Configure transactional email with SPF, DKIM, and DMARC.
- Publish Terms of Service, Privacy Policy, refund policy, data-retention policy, and support contact details.
- Confirm tax, invoicing, privacy, and electronic-signature requirements with advisers in every launch jurisdiction.

## Security release gate

- Rotate all credentials used during development.
- Add password reset and email verification with the production mail provider.
- Run dependency and application security scans in CI.
- Test tenant isolation, backups, webhook replay, cancellation, failed payments, and account deletion in staging.
- Configure centralized logs, error monitoring, uptime checks, and alerts.
