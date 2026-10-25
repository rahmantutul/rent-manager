# Rent & Building Manager

Laravel 12, PHP 8.2+, Blade, Alpine.js. Phase 1 contains installation, authentication, owner scoping and settings only.

## Local review

The verified development installation runs at http://127.0.0.1:8000/login.
If its local server stops, run `php artisan serve --host=127.0.0.1 --port=8000` from the project root.
Start XAMPP MySQL first. The browser verification database is `rent_manager_phase1_20261025`.

Local test accounts are `owner@rent-manager.test`, `manager@rent-manager.test`, `accountant@rent-manager.test`, `tenant@rent-manager.test` and `super_admin@rent-manager.test`.
Their temporary password is supplied in the Phase Report in chat. These accounts exist only in the local verification database, not in migrations or seeders.

## Fresh installation

1. Use a separate deployment directory and an empty MySQL/MariaDB database.
2. Run `composer install` for development, or include production vendor dependencies in the deployment.
3. Point the web server document root at `public/`. Allow PHP to write `storage/`, `bootstrap/cache/` and the project .env file.
4. Compiled `public/build/` assets are included. Buyers do not need Node.
5. Open `/install`. Review requirements, enter database credentials, company/owner details, currency and optional SMTP settings.
6. Submit. Failed database credentials do not write .env. Successful installation migrates the schema, creates the first owner, writes the lock, and redirects to login.
7. Back up .env securely. The generated key protects encrypted data.

Do not run migrate:fresh against an installed database that you want to keep. For a repeat installer check, use a separate fresh deployment/database. Removing an install lock does not permit replacing existing users.

## Permissions

| Role | Landing | Settings |
| --- | --- | --- |
| owner | /admin | Own company, private logo, currency and regional preferences |
| manager | /admin | No settings mutation |
| accountant | /admin | No settings mutation |
| tenant | /portal | Placeholder only; /admin returns 403 |
| super_admin | /super-admin | Install-wide name, logo and primary colour |

Global branding is deliberately available on the public login screen. Owner logos require an authenticated user belonging to that owner.
Only English is shipped; available languages are configured in config/product.php.
The installer creates an owner account only. Additional accounts are created through trusted server-side provisioning in Phase 1; there is no user-management screen or public registration.

## Verification

```powershell
composer install
composer test
php vendor/bin/pint --test
php artisan test tests/MySql --group=mysql
npm ci
npm run build
git diff --check
rg -ni cheque app resources routes lang database config
```

The default suite uses in-memory SQLite. The MySQL test is separate and requires a local database user able to create/drop its own randomly named test database. Configure MYSQL_TEST_HOST, MYSQL_TEST_PORT, MYSQL_TEST_USERNAME and MYSQL_TEST_PASSWORD if required; XAMPP localhost/root defaults are used otherwise. It never drops an existing database.

No SMTP delivery, public deployment or older database-version compatibility test was performed. See docs/PHASE_1_REPORT.md and docs/DECISIONS.md.

