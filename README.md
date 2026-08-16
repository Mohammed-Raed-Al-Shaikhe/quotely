# Quotely

Commercial Laravel 12 SaaS foundation for quotations, customer approvals, and invoice conversion.

## Included

- Workspace registration and secure session authentication
- 14-day trials and subscription enforcement foundation
- Owner/admin/member roles
- Tenant-scoped customers, products, quotations, invoices, settings, and dashboards
- Tokenized public approvals with rate limiting and immutable decisions
- Customer change-request comments, private draft revisions, fresh approval links, and immutable version history
- Integer-cent financial calculations and transactional invoice conversion
- Audit logs, soft deletion, security headers, health endpoint, and deployment checklist

## Local setup

```bash
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Open `http://127.0.0.1:8000` and sign in with:

- Email: `owner@quotely.test`
- Password: `Password123!`

Or create a new workspace at `/register`.

## Verification

```bash
php artisan test
```

See `PRODUCTION.md` for the infrastructure, Stripe, legal, security, backup, and monitoring gates that require production accounts and business decisions before accepting paying customers.
