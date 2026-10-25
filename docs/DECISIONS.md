# Decisions and assumptions

## Phase 1 — 2026-10-25

- The repository already contains Laravel 12, PHPUnit with SQLite in memory, and a composer test script; retain this skeleton.
- reference/cheque-studio is absent. Build the installer and layouts fresh, as permitted by the Phase 1 instructions.
- Approved by user: super_admin has a separate /super-admin placeholder and exclusive control of install-wide product name, logo and primary colour. Owners manage their own company details, logo, currency and display settings. Managers/accountants cannot change settings. No implicit super-admin bypass of owner scoping.
- Match the listed owners, users and settings columns exactly: no implicit timestamps, email_verified_at or remember_token on those business tables. Authentication will not offer remember-me in Phase 1. Laravel infrastructure tables remain separate.
- Users are the authentication identity root, so the authentication provider must resolve them before an owner context exists. User queries are not globally scoped; there is no user-management endpoint in Phase 1. Settings use BelongsToOwner; global branding uses a narrowly allowlisted explicit query. Owners are the scope root and have no owner_id in the data model.
- Owner logo files are private and accessible only through the current owner's authenticated route. The global branding logo is deliberately public through its controller so it can appear on login. No arbitrary file paths or owner IDs are accepted by the logo route.
- Product branding uses null-owner settings; company address uses an owner setting. No new schema columns are required. Global settings writes use a filesystem mutex because MySQL permits duplicate null values in composite unique indexes.
- Only English is shipped. The locale switcher lists installed translations from config/product.php. Currency precision accepts the model's 0, 2 and 3 values. This phase performs no money calculations.
- Alpine.js is added for responsive navigation and installer submit state, as required by the stack. Compiled Vite assets are included; buyers do not run Node.
- Installer database credentials are tested before .env writes. It only accepts an empty database or an incomplete migration of this app with no users; migrations never drop data. SMTP is configured but delivery is not tested in Phase 1. Session/cache use files and queues use sync.

## Phase 2 - 2026-10-25

- Property, building and unit records use the exact data-model columns plus Laravel soft-delete timestamps. They intentionally have no created_at or updated_at timestamps; soft deletes remain available for master data recovery.
- A newly created property creates its default building in the same request after the property row exists. Additional buildings can be added independently.
- Bulk numbering uses `floor_letter` (for example 3A) or `floor_number` (for example 301). Floor labels are derived from the submitted floor, so 301 is still Floor 3. Bulk generation checks every candidate before opening one transaction; any clash aborts the whole batch.
- Unit default_rent is stored as minor units. Phase 2 displays it with two decimals as a temporary placeholder because Money formatting is completed in Phase 3; no float is used for storage.
- Phase 2 permits only vacant, maintenance and reserved unit statuses in forms. Occupied is intentionally excluded until lease activation in Phase 4.
- The Phase 1 installer allowlist now includes the Phase 2 tables so an empty database with the complete application migration set can still be installed.
## Phase 3

- Number formats support the documented brace tokens and bracket aliases for compatibility.
- Owner bootstrap seeds Rent, Late fee, and Security deposit and four default numbering sequences idempotently.
- Lookup settings use a compact shared CRUD screen; system charge types cannot be deleted.

## Phase 4

- Lease activation sets `next_invoice_date` to the lease start date; invoice generation remains Phase 5.
- Billing day validation accepts 0 for month end and 1 through 28 for explicit days.
- Termination always sets the unit vacant and preserves historical charge rows.
- Tenant and lease documents use the private local disk and owner authorization.
