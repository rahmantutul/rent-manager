# Codex Build Playbook: Rent & Building Manager

Oct 23, 2026 · @Rahman Tutul

## How to use this playbook

The project is split into 14 phases, and each phase is one prompt you paste into Codex, one checklist you tick yourself, and one gate you must pass before the next. Codex only ever sees one phase at a time, so it cannot wander into later work.

### The loop for every phase

1. **Branch.** Run `git checkout -b phase-N` from the last finished phase.
2. **Paste the prompt** for that phase into Codex. Do not edit its scope.
3. **Read the report.** Codex must end with the Phase Report from AGENTS.md: files changed, commands run, test results, assumptions, and anything it skipped.
4. **Run the checks yourself.** Run the commands under "You run", then click through the "You check" list in the browser. Do not trust the report alone.
5. **Pass or fix.** If anything fails, paste the fix prompt from the last section. Never start the next phase with a known failure.
6. **Merge and tag.** Run `git checkout main && git merge phase-N && git tag phase-N-done`. A tag gives you a safe point to return to.
7. **Tick the tracker** and move on.

### Five rules that keep Codex from getting lost

- **One phase per session.** Start a fresh Codex session for each phase, so old context cannot leak in. The repo files carry the memory: `AGENTS.md`, `docs/DATA_MODEL.md`, `PROGRESS.md` and `docs/DECISIONS.md`.
- **No scope creep.** Every prompt lists what is out of scope. If Codex builds a later feature early, reject it with the reset prompt.
- **Tests come with the code.** A phase that adds logic is not finished until its tests pass. The money phases (5 to 7) are the most strictly tested.
- **Questions beat guesses.** When something is unclear, Codex writes it into `docs/DECISIONS.md` and asks, instead of inventing behaviour.
- **You are the gate.** Codex says it is done; you decide it is done.

## Before Phase 1: one-time setup

Codex needs four files in the repo before it writes any code, because they are how it remembers the project between sessions. This takes about 20 minutes.

1. Create an empty repo named `rent-manager`, run `git init`, and use `main` as the branch.
2. Copy your Cheque Studio source into `reference/cheque-studio/` and add `reference/` to `.gitignore`. Codex reads it to port the installer, auth, layouts and white-label settings. If you skip this, Phase 1 builds them fresh instead.
3. Export the [data model document](https://claude.ai/artifact/9gi7QaADU3LiQrPvPuJ3hP) to Markdown and save it as `docs/DATA_MODEL.md`. To drop a table you decided not to build, delete it from this file now, before Phase 2.
4. Save the AGENTS.md below at the repo root. Codex reads this file at the start of every session; if your Codex setup uses a different instructions file, paste the same text at the top of each phase prompt.
5. Create `PROGRESS.md` with the heading "Progress" and `docs/DECISIONS.md` with the heading "Decisions and assumptions". Leave both empty.
6. Install PHP 8.2 or newer, Composer, Node 20 or newer, and MySQL or MariaDB locally.
7. Commit: `git add . && git commit -m "Project guardrails"`.

### AGENTS.md

```markdown
# AGENTS.md: Rent & Building Manager

## Product
A self-hosted landlord and building manager sold on CodeCanyon: properties, units, tenants, leases, recurring rent invoices, payments, expenses, maintenance, reports, and a tenant portal. It is universal: no country-specific tax, currency or legal logic. Everything of that kind is owner-editable data.

## Stack and hosting limits
- PHP 8.2 or newer, Laravel 12, Blade, Alpine.js. Match the front-end approach in reference/cheque-studio.
- Production database is MySQL 5.7+ or MariaDB 10.3+. Tests run on in-memory SQLite, except tests marked with the group `mysql`.
- It must run on cheap shared hosting: no Docker, no Redis, no permanent queue worker. Queue driver is `database` or `sync`. The only scheduler hook is one cron line running `php artisan schedule:run` every minute.
- Buyers never run Node. Compiled front-end assets are committed or shipped.

## Source of truth
- `docs/DATA_MODEL.md` defines every table and billing rule. Table and column names must match it exactly. Never rename, add or drop a column without writing the reason in `docs/DECISIONS.md` first.
- `PROGRESS.md` records finished phases. `docs/DECISIONS.md` records assumptions.
- Never edit anything under `reference/`.

## Non-negotiable rules
1. Money is a signed bigint in minor units, handled only through `App\Support\Money`. Never use float for money.
2. Posted invoices, payments, allocations and deposit rows are never edited or deleted. Fix mistakes with a void, a credit note or a reversing row.
3. Every business table has `owner_id`, applied through a `BelongsToOwner` trait with a global scope. Every feature has a test proving one owner cannot see another's data.
4. Authorise with policies and `@can`. Validate with Form Requests.
5. No hardcoded URLs (use `route()`, `url()`, config) and no hardcoded user-facing text (use translation keys in `lang/en`).
6. No `dd()`, `dump()`, `console.log`, PHP notices, warnings or deprecations.
7. Escape all output, keep CSRF on every form, guard mass assignment, and store uploads on a private disk served through a permission check.
8. Add a package only if it is needed and works on shared hosting. Record why in `docs/DECISIONS.md`.
9. Run `./vendor/bin/pint` and `php artisan test` before finishing. Do not finish with failing tests.
10. Do only the phase you were given. Do not build later features. If you need something from a later phase, add the smallest stub and say so in the report.
11. If anything is ambiguous, write the question in `docs/DECISIONS.md` and ask. Do not guess about money, permissions or data deletion.
12. Never edit a migration that belongs to a finished phase. Add a new migration instead.

## Phase Report (end every phase with exactly this)
1. Summary in three lines or fewer.
2. Files created or changed, grouped by purpose.
3. Commands run and their results, including the test count and pass/fail.
4. Assumptions and decisions made (also saved in `docs/DECISIONS.md`).
5. Anything not done or skipped, and why.
6. How to test by hand: URLs, logins, and click-by-click steps.
Then update `PROGRESS.md` with the phase name, the date and the items finished.
```

## Phase tracker

The phases run in this order because each one needs the tables or rules of an earlier one; do not skip ahead. Size is relative effort for Codex plus your testing: S is a short session, L is a long one that may need two rounds of fixes. Change the status as you go.

| # | Phase | What you get | Needs | Size | Status |
| --- | --- | --- | --- | --- | --- |
| 1 | Foundation | Installer, login, roles, layout, white-label settings, owner scoping | None | L | Not started |
| 2 | Properties, buildings, units | Create and manage the physical structure, with floor and flat numbers | 1 | M | Not started |
| 3 | Money core and lookups | Money class, document numbering, charge types, tax rates, late-fee rules | 1 | M | Not started |
| 4 | Tenants and leases | Tenant records, leases, recurring charges, one active lease per unit | 2, 3 | L | Not started |
| 5 | Invoice engine | Automatic recurring invoices with pro-rating, safe to re-run | 4 | L | Not started |
| 6 | Payments and allocation | Payments, partial payments, credit notes, voids, deposits, balance rebuild | 5 | L | Not started |
| 7 | Late fees and scheduler | Daily jobs, late-fee invoices, the one-line cron setup | 6 | S | Not started |
| 8 | Billing screens and PDFs | Invoice and payment screens, receipts, tenant statements as PDF | 6, 7 | L | Not started |
| 9 | Expenses, vendors, maintenance | Cost tracking and repair requests in the admin panel | 2, 4, 8 | M | Not started |
| 10 | Dashboard and reports | Collections, arrears, income versus expenses, vacancy, CSV and PDF export | 8, 9 | L | Not started |
| 11 | Tenant portal | Tenant login with invoices, statements and maintenance requests | 8, 9 | M | Not started |
| 12 | Notifications | Email templates, invoice and overdue reminders, delivery log | 7, 11 | M | Not started |
| 13 | Hardening | Audit log, permission and isolation tests, performance, MySQL run | All | L | Not started |
| 14 | Demo and packaging | Demo data, documentation, listing text and screenshots | 13 | M | Not started |

## Phase 1: Foundation

This phase produces an empty but working product shell: install it, log in by role, and change the branding. No landlord features exist yet, so there is little to get wrong and a lot to get right.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 1 only.

Goal: a working Laravel 12 application shell with installer, authentication, roles, layout and white-label settings. No property, tenant or billing features yet.

Do:
1. Create the Laravel 12 project in the repo root. Keep AGENTS.md, docs/, PROGRESS.md and reference/ untouched. Set up PHPUnit with in-memory SQLite, Laravel Pint, and a `composer test` script.
2. Port from reference/cheque-studio: the web installer (requirements check, database and mail setup, creating the first account, writing .env, locking itself after install), multi-role authentication, the base layouts (admin layout with sidebar and top bar, guest layout) and the white-label settings screen. If that folder is missing, build these fresh and say so in the report. Remove everything cheque-related and read the product name from `config('app.product_name')`.
3. Create migrations exactly as in docs/DATA_MODEL.md for `owners`, `users` and `settings`. Create `users.tenant_id` as a nullable unsigned bigint without a foreign key; Phase 4 adds the constraint.
4. Roles are the enum in the data model: super_admin, owner, manager, accountant, tenant. Add a small role helper and gates; real policies come with each feature.
5. Add the `BelongsToOwner` trait, an `OwnerScope` global scope and a current-owner resolver. The installer creates one `owners` row (name, currency_code, minor_units, timezone, locale) and the first user with role owner.
6. Route groups: `/admin` for owner, manager and accountant, and `/portal` for tenants (a placeholder page). Login redirects by role. A tenant must never reach `/admin`.
7. Settings screen: product name, logo upload, primary colour, company details, currency code, minor units, timezone, locale and date format. Store them in `owners` and `settings` as the data model says.
8. Set up `lang/en` with translation keys and a locale switcher driven by the owner or user locale.
9. A placeholder admin dashboard page.
10. Tests: the installer cannot run twice; guests are redirected to login; each role lands in the right area; a tenant gets 403 on `/admin`; owner scoping isolates two owners' records; saving settings works.

Out of scope: properties, units, tenants, leases, billing, reports, notifications and any tenant portal features.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `composer install && php artisan migrate:fresh`
- `composer test`
- `./vendor/bin/pint --test`
- `grep -ri cheque app resources routes lang database config` (it should print nothing)

### You check

- [ ] With no `.env` and an empty database, the installer opens and its requirements page lists the PHP version and writable folders.
- [ ] Installing on MySQL succeeds, you land on the login page, and `/install` is blocked afterwards.
- [ ] The owner can log in to `/admin`, and `/admin` redirects to login after logout.
- [ ] A tenant-role user created in `php artisan tinker` lands on `/portal` and gets 403 on `/admin`.
- [ ] Changing the product name and logo updates the sidebar, the login page and the browser title.
- [ ] Saving currency JPY with 0 minor units works with no error.
- [ ] The `owners`, `users` and `settings` columns match `docs/DATA_MODEL.md`.

**Gate:** a fresh install on MySQL works end to end, tests pass, and no cheque wording is left. Then merge, tag `phase-1-done` and start Phase 2.

## Phase 2: Properties, buildings and units

After this phase you can enter your real 10-storey building, with floor numbers and flat numbers, in a few minutes. It is also the first test that owner scoping and roles work on real screens.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 2 only.

Goal: manage the physical structure: properties, buildings and units.

Do:
1. Create migrations exactly as in docs/DATA_MODEL.md for `properties`, `buildings` and `units`, including soft deletes, the unique key on `building_id` plus `unit_number`, and the index on `building_id` plus `status`.
2. Create the models with the `BelongsToOwner` trait, relationships, casts and factories.
3. Creating a property automatically creates one default building with the same name, so a single-building property needs no extra step. Admins can add more buildings to a property.
4. Admin screens: a property list (search, unit count, vacant count), a property page with its buildings, add and edit forms for buildings, and a unit list per building grouped by floor with filters for status, floor and type.
5. Add a bulk unit generator for a building: floor range from and to, number of units per floor, a numbering pattern (floor plus letter such as 3A, or floor times 100 plus a number such as 301), unit type and default rent. If any generated number already exists in that building, create nothing and show the list of clashes. Do the creation in one transaction.
6. Show floors with a helper: 0 is "Ground floor", negative numbers are "Basement 1", "Basement 2", and positive numbers are "Floor N". All labels go through translation keys.
7. Unit status can be edited only between vacant, maintenance and reserved here. Occupied is set automatically by leases in Phase 4.
8. Policies: owner and manager can create, edit and delete; accountant is read-only; tenant has no access.
9. Money fields use `App\Support\Money` conventions: store minor units, display with the owner's currency and minor_units. If Money does not exist yet, add the smallest helper and note it in the report; Phase 3 completes it.
10. Tests: CRUD for all three, bulk generation counts and numbers, clash handling creating nothing, soft delete, role permissions, and owner isolation including guessed URLs.

Out of scope: tenants, leases, billing, maintenance requests, reports.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `php artisan migrate:fresh`
- `composer test`
- `./vendor/bin/pint --test`

### You check

- [ ] Creating a property also creates a default building with the same name.
- [ ] Add a second building, "Block B", to the same property.
- [ ] In your real building, bulk-create floors 1 to 10 with 4 flats per floor and the floor-plus-letter pattern. You get exactly 40 units, and floor 3 shows 3A to 3D.
- [ ] Running the same bulk create again creates nothing and lists the clashes.
- [ ] Floor 0 shows as "Ground floor" and a negative floor shows as "Basement".
- [ ] Editing a unit's rent, type and floor saves correctly, and the filters by floor and status work.
- [ ] An accountant can view everything but sees no add or edit buttons, and gets 403 on the create URLs.
- [ ] A second owner created in `php artisan tinker` cannot open the first owner's property URLs.
- [ ] A soft-deleted unit disappears from the list but still exists in the database.
- [ ] Column names in the three tables match `docs/DATA_MODEL.md`.

**Gate:** your own building is entered correctly, role and owner isolation checks pass, and tests are green. Merge, tag `phase-2-done`, then start Phase 3.

## Phase 3: Money core and lookups

This phase builds the pieces every later money feature leans on: exact money arithmetic, document numbers that never repeat, and the owner-editable charge types, tax rates and late-fee rules. It has no big screens, so the tests carry the weight.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 3 only.

Goal: the Money class, document numbering, and the lookup tables with their settings screens.

Do:
1. Build `App\Support\Money` (or complete the helper started in Phase 2). It wraps an integer amount in minor units plus the currency code and minor_units. It must: parse user input such as "1,200.50" into minor units using string handling and never float; return a plain decimal string; format for display with the owner's currency and locale; add, subtract and compare; and multiply by a ratio or a percentage with half-up rounding using integer arithmetic only. Throw an exception if an intermediate value would overflow PHP's integer range.
2. Formatting must work when the PHP `intl` extension is missing. Use `NumberFormatter` when available and a tested manual fallback otherwise.
3. Add a Blade helper `money($minor)` and a form helper that converts typed amounts to minor units, both using the current owner's currency settings.
4. Add a small "money preview" box on the currency settings page: type an amount and see the stored minor units and the formatted result.
5. Create migrations exactly as in docs/DATA_MODEL.md for `charge_types`, `tax_rates`, `late_fee_rules` and `numbering_sequences`, with models, factories and the `BelongsToOwner` trait.
6. Build `NumberingService::next($ownerId, $docType)`. It runs in a transaction, locks the sequence row with `lockForUpdate()`, supports the tokens `{prefix}`, `{YYYY}`, `{YY}`, `{MM}` and `{seq:N}` (zero padded), and applies `reset_policy` (never, yearly, monthly) using the owner's timezone. Default formats: INV-{YYYY}-{seq:5}, CN-{YYYY}-{seq:5}, RCT-{YYYY}-{seq:5}, LSE-{YYYY}-{seq:4}.
7. Build an idempotent `OwnerBootstrap` service, also exposed as `php artisan owner:bootstrap {owner}`. It creates the numbering rows and the system charge types Rent, Late fee and Security deposit, marked `is_system`. The installer calls it for the first owner.
8. Settings screens for charge types, tax rates, late-fee rules and numbering. System charge types cannot be deleted and their kind cannot change. Show a live preview of the next number for each document type. The next number can never be set lower than one already used. Records that are in use are deactivated, never deleted.
9. Tests: a table of Money cases (see the expected values below), the fallback formatter, numbering formats and resets, system-row protection, validation (a tax rate must be between 0 and 100), and owner isolation.

Expected Money values the tests must include:
- parse "1200.50" with 2 decimals gives 120050
- parse "100000000" with 0 decimals gives 100000000
- 1000 multiplied by 10/30 gives 333
- 1999 multiplied by 7.5% gives 150
- 5 multiplied by 50% gives 3

Out of scope: leases, invoices, payments and anything that uses these tables.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `php artisan migrate:fresh` and install a fresh owner, then `php artisan owner:bootstrap 1` a second time (it must change nothing)
- `composer test`
- `./vendor/bin/pint --test`

### You check

- [ ] Settings lists Rent, Late fee and Security deposit as system charge types that cannot be deleted.
- [ ] You can add "Service charge" and "Parking" charge types.
- [ ] A tax rate of 150 is rejected, and "VAT 5" saves.
- [ ] The numbering page previews INV-2026-00001, and changing the prefix updates the preview.
- [ ] The money preview shows `1,200.50` as stored `120050` for USD, `100000000` as `100000000` for a 0-decimal currency, and three decimals for a 3-decimal currency.
- [ ] A test proves formatting works without `intl`.
- [ ] The five expected Money values above appear in the test file and pass.
- [ ] Running the bootstrap twice creates no duplicate rows.

**Gate:** all Money and numbering tests pass, and you have read the Money test table and agree with every number. Merge, tag `phase-3-done`, then start Phase 4.

## Phase 4: Tenants and leases

After this phase you can put a real tenant into a real flat on a lease with rent and extra charges. Nothing is billed yet; this phase only has to get the lease lifecycle and its rules right.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 4 only.

Goal: tenants, leases, lease charges and co-tenants, with a correct lease lifecycle. No invoices yet.

Do:
1. Create migrations exactly as in docs/DATA_MODEL.md for `tenants`, `leases` (including `active_unit_id` with its unique index), `lease_charges` and `lease_tenants`. Add a follow-up migration that puts the foreign key on `users.tenant_id`. Add the foreign keys from leases and lease_charges to units, tenants, late_fee_rules, charge_types and tax_rates.
2. Create models, factories and relationships. `tenants.id_document_number` uses an encrypted cast.
3. Build the polymorphic `attachments` table exactly as in the data model, plus an `AttachmentService`. Files go on a private disk and are served only through a controller that checks permission. Use it for tenant ID documents and lease documents.
4. Tenant screens: list with search and status filter, create and edit with all columns, a detail page showing leases, and document upload.
5. Lease screens: a create form with unit (only units with no active lease), primary tenant, optional co-tenants, start date, optional end date (empty means open-ended), billing cycle, billing day (1 to 28, or 0 for the last day), due-after days, grace days, auto-invoice, pro-rate first period, security deposit, late-fee rule and terms. The lease number comes from `NumberingService`.
6. Rent is the first lease charge: charge type Rent, amount pre-filled from the unit's `default_rent`, frequency every_cycle, `starts_on` equal to the lease start. Admins can add more recurring charges (service charge, parking and so on) with a frequency and an optional tax rate.
7. Lifecycle actions: Activate, Change rent, and Terminate.
   - Activate: only from draft. It checks that the unit has no active lease, sets `active_unit_id` to the unit id, sets the unit to occupied, and sets `next_invoice_date` to the lease start date. Handle the unique-key violation gracefully with a clear message.
   - Change rent: ends the current rent charge on the day before the new date and creates a new rent charge starting on the new date. Show both in a history list. Never edit the old amount in place.
   - Terminate: records `move_out_date`, sets `end_date` to it, sets status to terminated, clears `active_unit_id`, and sets the unit back to vacant. Do NOT clear `next_invoice_date` if it is on or before the end date, because Phase 5 bills the final partial period.
8. Policies: owner and manager manage; accountant is read-only; tenants have no access here.
9. Write every assumption you make about billing dates into docs/DECISIONS.md.
10. Tests: tenant CRUD, encrypted ID number (the raw database value is not the plain text), private file access, the activate rules (including the double-active-lease attempt), open-ended lease, change rent history, termination effects on unit and lease, lease numbering, charge validation (positive amount), role permissions and owner isolation.

Out of scope: invoices, payments, late fees, tenant portal logins, reminders.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `php artisan migrate:fresh`
- `composer test`
- `./vendor/bin/pint --test`
- In `php artisan tinker`: `DB::table('tenants')->value('id_document_number')` must print scrambled text, not the number you entered.

### You check

- [ ] Create a tenant with an ID number, then upload an ID document.
- [ ] The uploaded file cannot be opened by guessing its storage path; it opens only when logged in with permission.
- [ ] Create an open-ended lease for a vacant flat with rent 1,200 and a monthly service charge 100 with tax, then add a co-tenant.
- [ ] Activating the lease turns the unit to occupied and shows the tenant on the unit list.
- [ ] A second lease for the same unit cannot be activated, and the message is clear.
- [ ] Change rent from a chosen date: the old charge ends the day before, the new one starts on the date, and both appear in the history.
- [ ] Terminating with a move-out date sets the unit to vacant and the lease to terminated with that end date.
- [ ] An accountant is read-only, and a second owner cannot reach the first owner's tenants or leases.
- [ ] `docs/DECISIONS.md` lists the date assumptions Codex made, and you agree with them.

**Gate:** the lease lifecycle behaves as described and you have read and accepted the assumptions in DECISIONS.md. Merge, tag `phase-4-done`, then start Phase 5.

## Phase 5: Invoice engine

This is the heart of the product. The prompt spells out the date and rounding rules so Codex does not invent them, and gives worked examples you can verify with a calculator.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 5 only.

Goal: automatic recurring invoices that are correct, pro-rated and safe to run twice. Payments come in Phase 6.

Do:
1. Create migrations exactly as in docs/DATA_MODEL.md for `invoices` and `invoice_lines`, with models, factories and the unique index on `recurring_key`.
2. Build `php artisan rent:generate {--date=} {--lease=} {--dry-run}`. `--date` overrides today (for testing). Today is always computed in the owner's timezone.
3. Period rules:
   - Cycle length: monthly is 1 month, quarterly 3, semiannual 6, yearly 12.
   - Invoices are issued in advance. A period's invoice is created when `next_invoice_date` is on or before today.
   - The anchor day is `billing_day` (1 to 28; 0 means the last day of the month). Full periods start on an anchor day.
   - If `prorate_first_period` is on and the lease start day is not an anchor day, the first period runs from the start date to the day before the next anchor date, and is pro-rated. If it is off, the first period is a full cycle starting on the start date and later periods continue from there.
   - The last period ends on the lease `end_date` and is pro-rated. After it, `next_invoice_date` becomes null. Open-ended leases keep billing.
   - Pro-rating: line subtotal = round half up (charge amount x days in use / days in the full cycle period), in integer arithmetic.
4. Charge rules:
   - `every_cycle` is billed on every invoice and is pro-rated as above.
   - `once` is billed on the first invoice that overlaps `starts_on` and never again.
   - `monthly`, `quarterly` and `yearly` are never pro-rated. They are billed once for each anniversary (`starts_on` plus n times the frequency, clamped to the month end) that falls inside the invoice period; quantity is the number of anniversaries and unit price is the charge amount.
   - Charges with `starts_on` after the period, or `ends_on` before it, are skipped. A charge that starts or ends inside a period is pro-rated by days.
5. Tax rules: tax rates are copied onto each line (`tax_rate_value`). Exclusive tax = round half up (line subtotal x rate / 100). Inclusive: the charge amount is the gross; tax = gross minus round half up (gross x 100 / (100 + rate)); the line subtotal is the net; the line total is the gross.
6. Invoice creation: status `issued`, source `recurring`, `recurring_key` = `lease:{lease_id}:{period_start}`, `issue_date` = today, `due_date` = period start plus `due_after_days` (never before the issue date). Copy tenant, unit and currency onto the invoice. Totals are sums of the lines; never round the total separately. Take the number from `NumberingService` inside the same transaction.
7. Catch-up: if the command was missed for several periods, create each missing invoice in order, with a cap of 36 periods per lease per run. Each lease runs in its own transaction, and one failing lease must not stop the others; log the error.
8. After creating an invoice, move `next_invoice_date` to the next period start and fire an `InvoiceCreated` event with no listeners yet (Phase 6 listens to it).
9. Once a lease has an invoice, lock its start date, billing cycle and billing day from editing.
10. Add a minimal read-only invoice list and detail page (a plain table, no styling work) so the owner can inspect results. Phase 8 replaces them.
11. Tests, with these exact cases (currency with 2 decimals, rent 1,200.00 unless stated):
   - Monthly, billing day 1, start 2026-01-15, pro-rate on: first invoice covers 2026-01-15 to 2026-01-31 = 17 days of 31 = 658.06. The next covers February in full = 1,200.00.
   - Rent 1,000.00, start 2026-02-10, billing day 1: 19 days of 28 = 678.57.
   - Start 2026-01-01, end 2026-03-10: January and February are 1,200.00 each, March covers 10 of 31 days = 387.10, and `next_invoice_date` is then null.
   - Exclusive tax: 1,000.00 at 5% gives tax 50.00 and total 1,050.00. Inclusive: 1,050.00 at 5% gives net 1,000.00, tax 50.00 and total 1,050.00.
   - Running the command twice on the same date creates one invoice. A date three months ahead creates the missing invoices in order.
   - A yearly charge on a monthly lease appears on one invoice a year only. A once charge appears once. A monthly extra charge on a quarterly lease has quantity 3.
   - Billing day 0 (last day), a leap-year February, and a lease whose owner timezone is UTC+6 near midnight.
   - One failing lease does not stop the others.

Out of scope: payments, credit notes, late fees, invoice PDFs, emails and the full invoice screens.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `php artisan migrate:fresh` and set up one lease with the first example
- `php artisan rent:generate --date=2026-01-15`, then the same command again
- `php artisan rent:generate --date=2026-02-01`
- `php artisan rent:generate --dry-run --date=2026-06-01`
- `composer test` and `./vendor/bin/pint --test`

### You check

- [ ] The first command creates one invoice with total 658.06. The second run says nothing new was created.
- [ ] The February command creates an invoice for 1,200.00 covering 2026-02-01 to 2026-02-28.
- [ ] The dry run lists the invoices it would create and creates none.
- [ ] An invoice's lines add up exactly to its total, with tax lines correct to the cent.
- [ ] Invoice numbers are in sequence with the format you set in Phase 3, with no gaps.
- [ ] Terminate a lease mid-month and generate again: the final period is pro-rated and no further invoices appear.
- [ ] The lease start date and billing day can no longer be edited once an invoice exists.
- [ ] Every worked example in the test file matches your own calculator result.
- [ ] `docs/DECISIONS.md` records any rule Codex had to interpret.

**Gate:** all worked examples pass and match your own arithmetic. If any number is off by even one cent, fix it before moving on, because every later phase builds on these totals. Merge, tag `phase-5-done`, then start Phase 6.

## Phase 6: Payments, allocation, credit notes and deposits

This phase makes the books balance: partial payments, overpayments, voids, credit notes and the security deposit ledger. Like Phase 5 it is judged by exact numbers, so the prompt lists the scenarios the tests must contain.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 6 only.

Goal: payments, allocations, credit notes, voids, the deposit ledger and balance rebuilding, all correct to the cent. Screens may be plain but must work; Phase 8 polishes them and adds receipt PDFs.

Do:
1. Create migrations exactly as in docs/DATA_MODEL.md for `payments`, `payment_allocations` and `deposit_transactions`, with models and factories. The `deposit_transactions` table must include a nullable `receipt_number` varchar(40) column. If docs/DATA_MODEL.md does not list it yet, add it there and record the change in docs/DECISIONS.md.
2. Put every money action in its own service class, run inside a database transaction, using `lockForUpdate()` on the payment and invoice rows involved:
   - RecordPayment: amount above zero, tenant, optional lease, method, reference. The payment number comes from `NumberingService`. Allocation modes: auto (oldest due date first across the tenant's issued unpaid invoices), manual (an amount per invoice), or none. Update the cached `amount_paid`, `balance_due` and `payment_status` on invoices and `unallocated_amount` on the payment.
   - Allocation can never exceed the payment's unallocated amount or the invoice's balance due.
   - ChangeAllocation: never edit a row. Stamp the old row with `voided_at` and insert a new one.
   - VoidPayment: a reason is required. Set status void, stamp the live allocations with `voided_at`, and recalculate the affected invoices.
   - CreateCreditNote: an invoice row with `invoice_type` credit_note, negative amounts, `credit_for_invoice_id`, a number from the credit_note sequence, created already paid with zero balance so it never shows in arrears. The original invoice's balance = total minus live allocations minus the credit notes tied to it. In version 1 a credit note may not exceed the original's current balance due; to correct a fully paid invoice, void the payment first. Record this rule in docs/DECISIONS.md.
   - An invoice that has live allocations cannot be voided; offer a credit note instead.
   - ApplyTenantCredit: a listener on the `InvoiceCreated` event that applies the tenant's unallocated payment amounts, oldest first, to the new invoice up to its balance.
3. Deposits, in a `DepositService`: receive (a `deposit_transactions` row of type received with its own receipt number from the RCT sequence; it creates NO payments row, so a deposit never becomes tenant credit); apply to an invoice (a deduction row plus a payment with method `deposit`, allocated to that invoice); refund; forfeit; adjust. Held balance = received minus deductions, refunds and forfeits, plus adjustments. Reject any action that would make the held balance negative.
4. Tenant balance = sum of balance due on issued invoices minus sum of unallocated amounts on posted payments.
5. `php artisan billing:rebuild-balances {--owner=} {--check}` recomputes every cached amount from allocations and credit notes. With `--check` it only reports drift and changes nothing.
6. Plain working screens: record payment on the tenant page; a payments list; void payment with a reason; create credit note on the invoice page; a deposit panel on the lease page.
7. Permissions: owner, manager and accountant can record payments. Only owner and accountant can void payments, create credit notes and refund deposits.
8. Tests with these exact scenarios (2-decimal currency):
   - Invoice 1,200.00; pay 700.00 gives partial with balance 500.00; pay 500.00 gives paid.
   - Pay 1,500.00 on a 1,200.00 invoice: paid, with 300.00 unallocated. The next generated invoice for 1,200.00 receives that 300.00 automatically and has balance 900.00.
   - Pay 3,600.00 with no open invoices: it stays unallocated, and each of the next three generated invoices absorbs 1,200.00 and ends paid.
   - Auto allocation goes to the oldest due date first across two invoices; manual allocation works; allocation above the limits is rejected.
   - Voiding a payment returns the invoice to unpaid or partial and restores the amounts.
   - Changing an allocation leaves the old row stamped and adds a new one.
   - A credit note of 200.00 on an unpaid 1,200.00 invoice leaves balance 1,000.00; a credit note above the balance is rejected; an invoice with live allocations cannot be voided.
   - Deposit: receive 2,400.00; apply 400.00 to an invoice, leaving held 2,000.00 and the invoice reduced by 400.00; refund 2,000.00 leaves held 0; refunding more than held is rejected.
   - Corrupt the cached columns by direct database update; `--check` reports drift and `billing:rebuild-balances` repairs it.
   - Permissions per role, and owner isolation.
   - Add a test in the group `mysql` that two simultaneous allocations to one payment cannot over-allocate. It is skipped on SQLite and run in Phase 13.

Out of scope: receipt PDFs, emails, late fees, online payment gateways, cash refunds of tenant credit.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `php artisan migrate:fresh`, create a lease, and run `php artisan rent:generate`
- `composer test` and `./vendor/bin/pint --test`
- `php artisan billing:rebuild-balances --check` (it should report no drift)

### You check

- [ ] Pay 700.00 of a 1,200.00 invoice in the browser: status partial, balance 500.00. Pay the rest: paid.
- [ ] Pay 1,500.00: 300.00 shows as credit on the tenant page and is used on the next invoice you generate.
- [ ] Void a payment with a reason: the invoice goes back to unpaid or partial, and the payment is marked void, not removed.
- [ ] A credit note of 200.00 reduces the invoice balance to 1,000.00 and does not appear in the arrears list.
- [ ] Receive a 2,400.00 deposit and confirm no tenant credit appears; apply 400.00 to an invoice and check both balances.
- [ ] A manager cannot void a payment; an accountant can.
- [ ] In `php artisan tinker` change an invoice's `balance_due` by hand, run the `--check` command to see it flagged, then run the rebuild to fix it.
- [ ] Every scenario above gives the numbers shown when you do it by hand.

**Gate:** every scenario matches your hand calculation, the drift check is clean, and tests pass. Merge, tag `phase-6-done`, then start Phase 7.

## Phase 7: Late fees and the scheduler

This phase adds the daily automation and a health page that tells a buyer whether their cron is running. Most of the support tickets on a product like this are about cron, so the health page is worth the effort.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 7 only.

Goal: late fees, the scheduler wiring, and a system health page.

Do:
1. Build `php artisan fees:apply {--date=} {--lease=} {--dry-run}`. Today is computed in each owner's timezone.
2. A fee is due for an issued, non-void invoice (source not `late_fee`, type `invoice`) when today is on or after `due_date + lease.grace_days`, the balance due is above zero, and the lease has a late-fee rule.
   - Flat rule: the fee is `flat_amount`. Percent rule: the fee is the percent of the invoice's balance due at that moment, rounded half up.
   - `repeat_every`: `once` creates one fee. `week` creates fee n when today is on or after the threshold plus (n-1) x 7 days. `month` uses (n-1) calendar months. Stop when the invoice is paid or void.
   - `max_amount` caps the total fees for one original invoice. Sum the earlier fees through their `recurring_key`; the last fee may be reduced to fit the cap.
   - Create each fee as a separate invoice: source `late_fee`, one line using the system Late fee charge type, no tax, `recurring_key` = `late:{invoice_id}:{n}`, issue date today, due date = issue date + the lease's `due_after_days`. Fire the `InvoiceCreated` event so tenant credit applies.
   - Never charge a fee on a fee. Running the command twice must create nothing new. Create at most 12 fees per invoice per run.
3. Add an owner setting `late_fees_enabled` (default on) with a switch on the settings screen.
4. Register the scheduler in `routes/console.php`: `rent:generate` and `fees:apply` hourly with `withoutOverlapping()`, and `billing:rebuild-balances --check` weekly writing any drift to the log only. Each command stores its last run time in `settings` under `scheduler.last_run_at`. Because the commands are idempotent, running hourly is safe.
5. Build an admin "System health" page showing: PHP version and required extensions, writable folders, database connection, queue driver, `APP_KEY` present, and the last scheduler run with a red warning if it is older than two hours. Show the exact cron line to copy, built from the real project path and PHP binary: `* * * * * cd /path/to/app && /path/to/php artisan schedule:run >> /dev/null 2>&1`.
6. Tests with these cases (2-decimal currency):
   - Invoice 1,200.00, due 2026-02-05, grace 5 days, flat fee 25.00: nothing on 2026-02-09; one 25.00 fee invoice on 2026-02-10; a re-run adds nothing.
   - Percent fee 5%: a full balance of 1,200.00 gives 60.00; if 700.00 was paid first, the balance is 500.00 and the fee is 25.00.
   - Weekly repeat with a 100.00 cap and 60.00 fees: 60.00, then 40.00, then nothing.
   - No fee once the invoice is paid, void, or has no rule, and none when `late_fees_enabled` is off.
   - No fee is charged on a late-fee invoice.
   - The health page warns when the last run is stale and is hidden from the tenant role.

Out of scope: reminder emails (Phase 12) and any new invoice screens.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `php artisan schedule:list` (both jobs appear)
- `php artisan fees:apply --dry-run --date=2026-02-10`, then without `--dry-run`, then again
- `php artisan schedule:run`
- `composer test` and `./vendor/bin/pint --test`

### You check

- [ ] With an invoice due 2026-02-05 and a 5-day grace, the date 2026-02-09 creates nothing and 2026-02-10 creates the fee.
- [ ] Running the same date a second time creates nothing.
- [ ] A weekly 60.00 fee with a 100.00 cap produces 60.00 and then 40.00.
- [ ] Paying the invoice stops further fees.
- [ ] The health page shows green after you run `php artisan schedule:run`, and red after you clear `scheduler.last_run_at`.
- [ ] The cron line on the health page uses your real folder and PHP path and works when you paste it into a terminal.
- [ ] Turning off late fees in settings stops new fees.

**Gate:** fee amounts match the examples, repeat runs are harmless, and the health page tells the truth. Merge, tag `phase-7-done`, then start Phase 8.

## Phase 8: Billing screens and PDFs

This is the first phase where the product looks finished: proper invoice and payment screens, and the printable receipts and statements that landlords hand to tenants. It also tests whether the PDF engine can print names in any script.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 8 only.

Goal: finished billing screens and PDF documents. No new tables.

Do:
1. Replace the plain invoice list and detail pages with full screens.
   - Invoice list: filters for status, payment status, overdue, tenant, property or unit and date range; search by number; a totals row for the filtered results.
   - Invoice detail: lines, tax, allocated payments, credit notes, the tenant, and a record-payment button.
   - Manual invoices: create a draft with lines (charge type, quantity, unit price, tax rate), edit it while it is a draft, then issue it. Move the pro-rating, tax and rounding logic from the Phase 5 engine into one shared `InvoiceCalculator` service and use it for both recurring and manual invoices. Do not duplicate the math.
   - Void an invoice with a reason, allowed only when it has no live allocations.
2. Payment screens: a list with filters; a detail page with allocations; and on the record-payment form a live preview (Alpine) showing how the amount will be spread across invoices before saving.
3. Tenant page: current balance and a statement tab with a date range.
4. PDFs behind a `PdfRenderer` interface so the engine can be swapped. Documents: invoice, payment receipt, credit note, deposit receipt and tenant statement. Use the owner's logo, company details, footer text and paper size (A4 or Letter, an owner setting), and format money with `Money`.
5. Statement rules: opening balance (everything before the start date), then invoices (debit), credit notes (credit) and payments at their full amount (credit), in date order with a running balance, then the closing balance. Payments made by deposit are labelled "Paid from deposit". The closing balance must equal the tenant balance function from Phase 6.
6. Non-Latin text check. Implement the renderer with barryvdh/laravel-dompdf first. Render a sample invoice containing these names: "محمد أحمد" (Arabic), "মোঃ রহমান" (Bangla), "王小明" (Chinese) and "José Ñúñez". If any are shown as boxes, reversed, or unjoined letters, evaluate mPDF as the second driver and keep whichever renders them correctly. Save the sample PDFs in `storage/app/samples/` and write the findings and your choice in docs/DECISIONS.md. Do not hide a broken result.
7. Permissions follow Phase 6. Tenants have no access to these screens yet.
8. Tests:
   - Invoice list filters and search.
   - A manual invoice has the same totals as the engine for the same inputs.
   - Draft editing works and issued invoices are locked.
   - Void rules.
   - Statement example: invoice 1,200.00 in January, payment 700.00, invoice 1,200.00 in February, credit note 200.00 on the February invoice; the closing balance is 1,500.00 and equals the tenant balance.
   - Every PDF route returns 200 with `application/pdf` and a non-empty body.
   - Permissions and owner isolation.

Out of scope: sending emails, reports, tenant portal.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `composer test` and `./vendor/bin/pint --test`
- Open the sample PDFs in `storage/app/samples/` yourself

### You check

- [ ] Filter the invoice list by overdue, tenant and date range; the totals row follows the filters.
- [ ] Create a manual invoice with two lines and a tax rate and issue it. Its totals match your calculator.
- [ ] On the payment form, type 1,500.00 and watch the live preview spread it across invoices before you save.
- [ ] Open the invoice, receipt, credit note, deposit receipt and statement PDFs. Logo, company name, numbers and amounts are right, and the paper size follows the setting.
- [ ] The statement for the example above ends at 1,500.00 and matches the tenant's balance on their page.
- [ ] Look at the sample PDF with Arabic, Bangla and Chinese names. Each name is readable and correctly joined. If not, the engine choice in DECISIONS.md must say so honestly and offer a fix.
- [ ] A tenant-role user cannot open any PDF route.

**Gate:** the PDFs look good enough to give to a real tenant, you have looked at the non-Latin sample yourself, and the statement balance matches. Merge, tag `phase-8-done`, then start Phase 9.

## Phase 9: Expenses, vendors and maintenance

This phase covers the money going out and the repairs: what you spend on each building, who you pay, and what is broken. It feeds the income-versus-expenses report in Phase 10.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 9 only.

Goal: expenses, vendors, expense categories and maintenance requests in the admin panel.

Do:
1. Create migrations exactly as in docs/DATA_MODEL.md for `vendors`, `expense_categories`, `expenses` and `maintenance_requests`, with models, factories and the `BelongsToOwner` trait. Do not build `utility_meters`, `meter_readings` or `assets`; they are version 1.1.
2. Extend the idempotent `OwnerBootstrap` to seed expense categories: Repairs, Salaries, Utilities, Insurance, Taxes, Cleaning and Other. Running it again must not duplicate them.
3. Vendor screens: list, create, edit and soft delete.
4. Expense screens: list with filters (property, building, unit, category, vendor, date range, status) and a totals row; create and view with receipt uploads through `AttachmentService`.
   - Property is required. Building and unit are optional and must belong to the chosen property.
   - Money rules: `expenses.amount` is the total paid including tax, and `tax_amount` is the tax contained in it, for information only. Once posted, only description, category, vendor and reference can be edited. To change the amount, tax or date, void the expense with a reason and enter a new one. Never delete an expense.
   - Charge to tenant: an action on an expense whose unit has an active lease. It creates a manual invoice for that lease's tenant with one line, using the shared `InvoiceCalculator` and `NumberingService`, and stores the link in `expenses.invoice_line_id`. Refuse with a clear message when the unit has no active lease or the expense is already charged.
5. Maintenance requests: create and edit with every column in the data model; status flow open, in_progress, waiting, resolved, closed; assignment to a user or a vendor; priority and category; a scheduled date; photo uploads. Set `resolved_at` when the status becomes resolved and clear it if it is reopened. The list shows priority, status, unit and an "overdue" flag when the scheduled date has passed and the request is not resolved. An "Add cost" button on a request creates an expense linked to it.
6. Show expense totals for the current year on each property page.
7. Permissions: owner, manager and accountant can create and view expenses; only owner and accountant can void an expense. Owner and manager manage maintenance requests; the accountant is read-only there. Tenants have no access yet.
8. Tests: expense create, the locked-fields rule, void, filters and totals; charge to tenant (the invoice total and tenant are right, refused without an active lease, refused twice); vendor soft delete; category seeding is idempotent; the maintenance status flow and `resolved_at` behaviour; expense linked to a request; private receipt files; role permissions; owner isolation.

Out of scope: reports and charts, the tenant portal, notifications, meters and assets.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `php artisan migrate:fresh`, run the installer's bootstrap, then `php artisan owner:bootstrap 1` again
- `composer test` and `./vendor/bin/pint --test`

### You check

- [ ] The seven default categories exist once, even after running the bootstrap twice.
- [ ] Add a vendor and an expense for 350.00 on one flat with a receipt photo; the receipt opens only when logged in.
- [ ] Try to change the amount of the posted expense: the field is locked. Void it with a reason; it stays visible as void and leaves the totals.
- [ ] Use "Charge to tenant" on a flat with an active lease: an invoice appears for that tenant with the right total. Doing it again is refused.
- [ ] On a vacant flat, "Charge to tenant" is refused with a clear message.
- [ ] Create a maintenance request, move it through the statuses, and check `resolved_at` appears and disappears as expected.
- [ ] "Add cost" on a request creates a linked expense.
- [ ] An accountant can void an expense but cannot edit a maintenance request.

**Gate:** expenses and requests work end to end and the charge-back invoice math matches the shared calculator. Merge, tag `phase-9-done`, then start Phase 10.

## Phase 10: Dashboard and reports

Reports are where an owner decides whether the product is worth buying, so the numbers must reconcile with everything built earlier. This prompt fixes the accounting definitions (cash basis, what counts as income) so Codex does not choose them silently.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 10 only.

Goal: the admin dashboard and the reports, each with filters, CSV export and (for the main ones) PDF export.

Definitions you must follow. Write them on each report page in one short line so the user can see them.
- Expense total: `expenses.amount` is the total paid including tax; `tax_amount` is the tax contained in it, for information only.
- Income (cash basis) in a period: posted, non-void payments by `received_on`, including payments with method `deposit` (that is when a deposit becomes income), plus deposit forfeits by `transaction_date`. Deposits received and deposits refunded are never income; they appear only in the deposits report.
- Arrears: issued, non-void invoices with a balance due above zero and a due date before the as-of date. Credit on account (unallocated payments) is shown separately and is not netted into arrears rows.
- Aging buckets by days past due: 1 to 30, 31 to 60, 61 to 90, over 90. Invoices not yet due are shown as current.

Do:
1. Dashboard tiles: total arrears now, collected this month, expenses this month, occupancy percent, vacant units, overdue invoices, leases ending within 60 days, and open maintenance requests. Add one chart of the last 12 months showing invoiced versus collected. Bundle the chart library locally; no CDN.
2. Reports, each filterable by property, building and date range or as-of date:
   - Rent collection: payments by period, method and property.
   - Arrears and aging: per tenant with unit, oldest unpaid due date, days overdue, balance and bucket, plus totals.
   - Income versus expenses: by month and by property, with the net result.
   - Vacancy: units by status, vacancy rate, days vacant (since the last lease ended) and potential lost rent (`default_rent` of vacant units).
   - Lease expiry: fixed-term leases ending within N days (default 60). Open-ended leases are excluded.
   - Deposits held: the held balance per lease.
3. Exports: CSV for every report (streamed, UTF-8 with a BOM so Excel opens non-Latin names correctly, amounts as plain decimal strings, and cells that start with `=`, `+`, `-` or `@` prefixed with a single quote to block formula injection). PDF for arrears and income versus expenses through `PdfRenderer`.
4. Use SQL aggregates and the existing indexes, never loops over all invoices in PHP. Enable `Model::preventLazyLoading()` in the test environment to catch N+1 queries. Add `php artisan demo:perf` that seeds about 2,000 units and 10,000 invoices into a scratch database, and report the load time of each report in the Phase Report.
5. Permissions: owner, manager and accountant can see reports. Tenants cannot.
6. Tests with these exact cases:
   - Aging on 2026-03-15: an invoice of 500.00 due 2026-03-10 falls in 1 to 30; 700.00 due 2026-02-01 in 31 to 60; 300.00 due 2025-12-01 in over 90. Totals 1,500.00.
   - Income versus expenses for January: payments 1,200.00 and 700.00 plus a 400.00 payment by deposit equal 2,300.00 of payments; a 100.00 forfeited deposit; a posted expense of 350.00 and a voided expense of 150.00 that must be ignored. Net = 2,300.00 + 100.00 - 350.00 = 2,050.00.
   - Occupancy: 40 units with 36 occupied is 90.0 percent.
   - The arrears total equals the sum of balance due on overdue invoices, and the tenant statement and balance from Phase 8 still reconcile.
   - CSV has the BOM, the right header, correct quoting, injection prefixes and non-Latin names.
   - Filters, permissions and owner isolation.

Out of scope: tenant portal, emailed reports, meters and assets.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `composer test` and `./vendor/bin/pint --test`
- `php artisan demo:perf` on a scratch database (never your real one)

### You check

- [ ] Enter the aging example by hand (or use test data) and the report shows 500.00, 700.00 and 300.00 in the right buckets with a 1,500.00 total.
- [ ] The income versus expenses report for January gives net 2,050.00 for the example, and the one-line definitions are visible on the page.
- [ ] The occupancy percentage matches a count of occupied units by hand.
- [ ] Open each CSV in Excel or LibreOffice: Arabic, Bangla and Chinese names display correctly, and a tenant named `=1+1` appears as text, not a formula.
- [ ] The arrears and income-versus-expenses PDFs look right.
- [ ] Each report loads quickly on the performance data, and the Phase Report lists the timings. Any report that is slow gets fixed now, not later.
- [ ] A tenant-role user gets 403 on every report URL.

**Gate:** every report matches your hand calculation and the performance timings are acceptable to you. Merge, tag `phase-10-done`, then start Phase 11.

## Phase 11: Tenant portal

This is the user panel: tenants log in on their phones to see invoices, download receipts and report problems. The main risk is a tenant seeing someone else's data, so the tests focus on isolation.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 11 only.

Goal: a mobile-first tenant portal under `/portal`. No new tables.

Do:
1. Portal access. On the tenant page add "Enable portal access". It requires an email, creates a `users` row (role tenant, `tenant_id`, `owner_id`, a random password), sets `portal_enabled`, and sends a password-setup link through Laravel's password broker. If mail is not configured, show the link to the admin to copy. "Disable access" sets `portal_enabled` to false and `users.is_active` to false, and a disabled user cannot log in.
2. Visibility rule. A portal user sees only leases where their tenant record is the primary tenant or a co-tenant (`lease_tenants`), and only the invoices, payments and statements belonging to those leases. Build this once, as a reusable scope and policy, and use it everywhere. Never filter by an ID taken from the URL alone.
3. Pages, using a separate mobile-first portal layout:
   - Dashboard: current balance, next due invoice, last payment, and open requests.
   - My lease: unit, dates, rent and recurring charges, and the deposit held. Read-only.
   - Invoices: list and detail, with PDF download.
   - Payments: list, with receipt PDF download.
   - Statement: a date range, with PDF download.
   - Maintenance: list, create and view. Creating requires a unit from the tenant's active leases, a category and a description, with up to 5 photos (jpg, png or webp, 5 MB each, validated by type, stored on the private disk). The request is saved with `tenant_id`, `reported_by`, and normal priority. In the admin list, mark requests from tenants and show a count badge in the sidebar.
   - Profile: edit phone, alternative phone and emergency contact; change password; choose a language. Email changes are made by an admin.
4. Never show tenants: other tenants, expenses, vendors, internal notes, or owner reports. They see only the status of a request, not who it is assigned to.
5. Rate-limit the login routes. Keep the "Pay online" button hidden behind a setting that is off by default.
6. Tests:
   - Enabling and disabling access; a disabled user is blocked; the setup link works once.
   - Tenant A requesting tenant B's invoice, payment, statement, PDF, request or photo by ID gets 404, for every portal route.
   - A co-tenant sees the shared lease's invoices; a former tenant sees only their own past leases.
   - Maintenance creation, validation (wrong file type, too many files, a unit that is not theirs), and the admin flag.
   - A tenant gets 403 on every `/admin` route and an admin can still use the admin panel.
   - The profile cannot change email.

Out of scope: online payments, notification emails, chat.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `composer test` and `./vendor/bin/pint --test`
- Open the portal in your browser at phone width (use the developer tools' device mode)

### You check

- [ ] Enable portal access for a tenant, open the setup link in a private window, set a password, and log in. The portal opens, not the admin panel.
- [ ] The dashboard balance equals the tenant's balance in the admin panel.
- [ ] Download an invoice, a receipt and a statement from the portal.
- [ ] Log in as a second tenant. Paste the first tenant's invoice, receipt and photo URLs: each returns 404.
- [ ] Submit a maintenance request with two photos from the phone-sized view. It appears in the admin list flagged as from a tenant, with the sidebar badge.
- [ ] Disable access in admin; the tenant is logged out on their next request and cannot sign in.
- [ ] A co-tenant sees the shared lease; a different tenant does not.
- [ ] No expense, vendor or owner report is reachable from the portal, even by typing the admin URL.

**Gate:** every isolation test passes and you personally failed to read another tenant's data by URL guessing. Merge, tag `phase-11-done`, then start Phase 12.

## Phase 12: Notifications and message templates

This phase makes the product talk to tenants: invoice emails, receipts, overdue reminders and lease expiry notices, all editable by the owner. It also has to work on a host with no queue worker, so sending runs through the same cron line as everything else.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 12 only.

Goal: email notifications with editable templates, reminders and a delivery log. SMS is stored but not sent in version 1.

Do:
1. Create migrations exactly as in docs/DATA_MODEL.md for `message_templates` and `notification_logs`, with models and factories.
2. Email settings screen: SMTP host, port, encryption, username, password (encrypted at rest and never shown back, only a masked value), from name, from address, and a "Send test email" button. Apply these settings at runtime through a service provider, with the `.env` mail values as the fallback.
3. Templates. Keys: invoice_issued, payment_received, overdue_reminder, lease_expiring, maintenance_update. Channel email now; an sms row may be saved but nothing is sent. Placeholders: {tenant_name}, {invoice_number}, {amount_due}, {due_date}, {unit}, {property}, {company_name}, {payment_amount}, {receipt_number}, {lease_end_date}, {request_title}, {request_status}. Replace placeholders with plain string substitution and escape the values; never compile user-written text as Blade or PHP. Seed English defaults for each owner in the idempotent `OwnerBootstrap`. Build an edit screen that lists the placeholders and shows a preview using sample data. Templates are stored per locale, and the tenant's locale picks the row with an English fallback.
4. Sending: a `SendNotification` job that writes a `notification_logs` row (queued, sent or failed with the error) for every attempt. Add `queue:work --stop-when-empty --max-time=50` to the scheduler every minute with `withoutOverlapping()`, so a host with only cron still sends. If the queue is `sync`, send immediately.
5. Triggers, each with an on/off setting under Settings (settings keys `notify.<template_key>`):
   - invoice_issued: when an invoice is issued, with the invoice PDF attached (attachment is a setting, default on).
   - payment_received: a receipt, with the receipt PDF attached.
   - overdue_reminder: a daily idempotent `reminders:send {--date=}` command. First reminder the day after the due date, then every `reminder_interval_days` (default 7), up to `reminder_max_count` (default 3). Count earlier reminders through `notification_logs`; do not add columns. Stop when the invoice is paid or void.
   - lease_expiring: to the tenant and the owner, at offsets from a setting (default 60 and 30 days before `end_date`), once per offset.
   - maintenance_update: to the tenant when the status of a request they submitted changes.
   - A manual "Email this invoice" button on the invoice page.
6. Never send the same template twice for the same related record (check the log for a sent row). If a tenant has no email, log a failed row with the reason "no recipient" and continue.
7. A delivery log screen (filter by status, template and date) with a "Retry" button for failed rows.
8. Tests using `Mail::fake()`:
   - Placeholder substitution, including hostile values: a name of `<script>alert(1)</script>` and a template containing `{{ 7*7 }}` are shown as plain text.
   - Reminders for an invoice due 2026-03-01 with interval 7 and maximum 3 are sent on 2026-03-02, 2026-03-09 and 2026-03-16 and never again; running the same date twice sends nothing new; payment or voiding stops them.
   - A lease ending 2026-05-01 with offsets 60 and 30 sends on 2026-03-02 and 2026-04-01 only.
   - A failed send is logged with the error and retry works; a missing recipient is handled; switching a trigger off sends nothing; an sms template saves but is never sent.
   - Encrypted SMTP password; permissions (owner only for email settings and templates); owner isolation.

Out of scope: SMS sending, push and WhatsApp.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `composer test` and `./vendor/bin/pint --test`
- `php artisan reminders:send --date=2026-03-02` (with the example invoice in place), then run it again
- `php artisan schedule:list` (the queue worker line is present)

### You check

- [ ] Enter real SMTP details, press "Send test email", and receive it. The password field shows only a masked value afterwards.
- [ ] Edit the invoice template, check the preview, then issue an invoice. The email arrives with the PDF attached and your wording.
- [ ] A tenant named `<b>Test</b>` shows as plain text in the received email.
- [ ] The reminder example sends on the three dates only, and a second run of the same date sends nothing.
- [ ] Pay the invoice and run the reminder date again: nothing is sent.
- [ ] A tenant with no email address produces a failed log row with the reason, and nothing breaks.
- [ ] Break the SMTP password on purpose; the log shows a failed row with the error, and "Retry" works after you fix it.
- [ ] Turning off a trigger in settings stops its emails.

**Gate:** you received the real emails, the reminder dates match the example exactly, and the log shows what happened. Merge, tag `phase-12-done`, then start Phase 13.

## Phase 13: Hardening and security

No new features here: this phase proves the product is safe to sell. It adds the audit log, runs a security review against the whole codebase, and re-runs everything on MySQL, because up to now most tests ran on SQLite.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md and PROGRESS.md first. This is Phase 13 only.

Goal: audit log, security hardening, a MySQL compatibility pass, and privacy tools. Do not add unrelated features.

Do:
1. Audit log. Create the `audit_logs` migration exactly as in docs/DATA_MODEL.md. Record create, update and delete events (with old and new values, user, IP and user agent) for invoices, payments, payment allocations, deposit transactions, leases, tenants, expenses, users (including role changes) and settings. Mask secrets, passwords and ID numbers in the stored values. The model must be insert-only. Add an owner-only audit viewer with filters for user, event, record type and date.
2. Write `docs/SECURITY_REVIEW.md`: a table of every finding with severity and whether it is fixed. Cover at least:
   - Authorization: a data-driven test that walks every registered route against every role (guest, tenant, accountant, manager, owner, super_admin) and asserts the expected status. Every route must require authentication unless it is on a short, explicit allow-list (login, password reset, installer while unlocked).
   - Mass assignment: no `$guarded = []` on any model with sensitive fields.
   - Output escaping: list every use of `{!! !!}` and justify or remove it.
   - SQL: list every `DB::raw`, `whereRaw` and `selectRaw`, and confirm no user input reaches them unbound.
   - CSRF on every form and no CSRF exceptions.
   - Uploads: validate by file content, not only by name; allow-list extensions; random file names; private disk; serve with `X-Content-Type-Options: nosniff`.
   - Login, password reset and test-email throttling; generic messages that do not reveal whether an account exists; sessions invalidated on logout and password change.
   - Security headers middleware: X-Frame-Options SAMEORIGIN, X-Content-Type-Options nosniff and Referrer-Policy. Explain in the document why a strict CSP is not set (Alpine needs inline evaluation).
   - Production defaults: the installer sets `APP_DEBUG=false` and `APP_ENV=production`; add custom 403, 404, 419, 500 and 503 pages.
   - A root `.htaccess` that routes to `public/` and blocks dotfiles, `.env`, `storage` and `vendor`, for buyers who upload the whole folder into `public_html`. Add a test that requests `/.env` through the web server config logic where possible, and explain how the owner can check it.
   - Run `composer audit` and `npm audit` and record the results.
3. MySQL pass. Add `phpunit.mysql.xml` and run the whole suite on MySQL 5.7 or 8 and, if available, MariaDB 10.3 or newer, including the `mysql` group concurrency tests from Phase 6. Fix everything that differs. All report queries must work with `ONLY_FULL_GROUP_BY` on. Do not use window functions or CTEs anywhere (MySQL 5.7 lacks them).
4. Performance. Re-run `php artisan demo:perf`, fix any N+1 queries and slow reports, add missing indexes through a new migration, and record the before and after timings in the report.
5. Privacy tools. A "Data export" action that builds a ZIP of CSVs (tenants, leases, invoices, payments, expenses). An "Anonymise tenant" action for former tenants that replaces the name, email, phone, ID data and notes with placeholder values and deletes their uploaded documents, while leaving every financial row and total intact. Make no legal claims in the interface or documents.
6. Tests: the route and role matrix; audit rows for each event type with old and new values, masked secrets, and no way to update or delete a row; upload hardening (a PHP file renamed to .jpg is rejected); XSS payloads in tenant names, notes and request text are escaped; security headers present; throttling; installer lock; anonymisation keeps totals and statements balanced; data export contents.

Out of scope: new features, UI redesign.
Finish with the Phase Report from AGENTS.md, and attach the findings table from `docs/SECURITY_REVIEW.md`.
```

### You run

- `composer test`, then `vendor/bin/phpunit -c phpunit.mysql.xml`
- `composer audit` and `npm audit`
- `grep -rn "{!!" resources/views`
- `grep -rn "DB::raw\|whereRaw\|selectRaw" app`
- `php artisan demo:perf` on a scratch database

### You check

- [ ] Read `docs/SECURITY_REVIEW.md` yourself and decide whether any unfixed item is acceptable. Unfixed high-severity items are not.
- [ ] Both test runs, SQLite and MySQL, are fully green with no skipped concurrency test on MySQL.
- [ ] Create, edit and void records, then open the audit viewer: each action shows who, when, the old and new values, and no passwords or full ID numbers.
- [ ] Try the attacks yourself: type `<script>alert(1)</script>` into a tenant name; remove the CSRF token from a form in the browser tools and submit; guess another owner's URLs; upload a PHP file renamed to `.jpg`. Each is blocked.
- [ ] Five wrong passwords in a row get throttled, and the message does not reveal whether the email exists.
- [ ] Anonymise a former tenant: their statements still balance and the totals did not change.
- [ ] Upload the build to a real shared hosting account in `public_html`, then open `/.env`, `/storage/logs/laravel.log` and `/vendor/autoload.php`. All three are denied.
- [ ] A fresh install in production mode shows custom error pages with no stack traces.

**Gate:** no open high-severity finding, green tests on MySQL, and your own attack attempts all fail. Merge, tag `phase-13-done`, then start Phase 14.

## Phase 14: Demo data, documentation and packaging

The last phase turns a working app into a sellable item: a convincing demo, documentation a stranger can follow, and a clean release zip. Do one human step first, because Envato's upload rules change and I have not verified the current ones.

### Before you paste the prompt

Read Envato's current author requirements for PHP script items (file layout, documentation, licence and third-party credits) on their Help Center, and paste the rules into `docs/ENVATO_CHECKLIST.md` in the repo. Codex follows that file for the package layout. If you skip it, the zip layout is a guess.

### Paste this to Codex

```markdown
Read AGENTS.md, docs/DATA_MODEL.md, docs/ENVATO_CHECKLIST.md and PROGRESS.md first. This is Phase 14 only.

Goal: demo data, a demo mode, documentation, an upgrade path, a release package and listing copy. Do not add product features.

Do:
1. Demo seeder: `php artisan demo:seed`, deterministic (a fixed random seed) so screenshots repeat. It builds, using the real services (never raw inserts so the numbers are consistent):
   - A 10-storey residential building with 4 flats per floor, and a second property with shops and offices.
   - About 35 tenants with realistic international names, including Arabic, Bangla and Chinese examples; a mix of open-ended and fixed-term leases; and 3 tenants with portal logins.
   - Twelve months of invoices generated by the engine, with full payments, partial payments, late payments, an overpayment, a credit note, deposits, and some overdue tenants.
   - Expense history, vendors, and maintenance requests in every status.
   After seeding, `php artisan billing:rebuild-balances --check` must report no drift. Use only generated placeholder avatars and images that you create; no stock photos.
2. Demo mode: `DEMO_MODE=true` shows a banner, blocks destructive and settings changes, uploads and outbound email, and a scheduled `demo:reset` command restores the demo data hourly. List the demo logins in the README.
3. Documentation: a self-contained, offline `documentation/index.html` (no CDN) with: requirements; installation on shared hosting (cPanel steps) and on a VPS; cron setup; email setup; first steps (property, bulk units, tenant, lease, invoices); how billing works (pro-rating, tax, late fees, credit notes, deposits) with the worked examples from the tests; the tenant portal; reports and exports; adding a translation; white-label settings; backup and restore; upgrading; troubleshooting (500 errors, cron not running, PDF fonts, mail not sending, file permissions); FAQ; changelog; support policy. Put `[SCREENSHOT: description]` placeholders where screenshots belong and list them all in `docs/SCREENSHOTS.md`. Do not invent screenshots.
4. Upgrade path: a `config/version.php`, a changelog file, and `php artisan app:upgrade` that runs migrations safely and clears caches. Explain it in the documentation.
5. Third-party credits: record every package, font and icon set with its licence (use `composer licenses` and the npm licence list). Confirm each is licensed for redistribution in a paid product, remove any that are not, and make sure nothing loads from a CDN or external font service.
6. Packaging: `scripts/package.sh` builds the release zip with `composer install --no-dev --optimize-autoloader`, compiled assets, `.env.example`, empty writable `storage` and `bootstrap/cache` folders, and the layout from docs/ENVATO_CHECKLIST.md. It must exclude `.git`, `reference/`, `node_modules`, `.env`, logs, and development files. Add `scripts/verify-package.sh` that unzips into a clean folder and fails if it finds a forbidden file, a missing documentation folder, a missing .env.example, or a leftover cheque reference.
7. Listing copy in `docs/LISTING.md`: three title options, a tagline, a short description, a long description grouped by feature, requirements, what is included, a FAQ, a v1.0.0 changelog, tags, and a screenshot plan of 12 screens with what each should show. Include an honest limitations section (for example: no online payment gateway and no SMS sending in version 1.0). Make no tax, legal or compliance claims for any country.
8. Tests: demo seed counts and no drift; invoice totals reconcile; demo mode blocks writes; `composer validate`; a script that checks all internal documentation links; the version file exists.

Out of scope: new features.
Finish with the Phase Report from AGENTS.md.
```

### You run

- `php artisan migrate:fresh && php artisan demo:seed`
- `php artisan billing:rebuild-balances --check`
- `bash scripts/package.sh && bash scripts/verify-package.sh`
- `composer test` and `./vendor/bin/pint --test`

### You check

- [ ] The demo looks real: click through the dashboard, arrears report, a tenant statement and the portal, and nothing looks fake or broken. The 10-storey building shows 40 flats.
- [ ] Demo mode blocks edits and emails, and the hourly reset brings the data back.
- [ ] Take the release zip to a real shared host, unzip it, and install it by following only the documentation.
- [ ] Give the zip and documentation to one person who has never seen the product and watch them install it. Every place they get stuck is a documentation bug.
- [ ] Capture each screenshot listed in `docs/SCREENSHOTS.md` and place it in the documentation.
- [ ] Read `docs/LISTING.md` and delete any sentence you cannot prove with a screen or a test.
- [ ] Compare the zip against `docs/ENVATO_CHECKLIST.md` line by line.
- [ ] The credits list has a licence for every font, icon and package.

**Gate:** a stranger can install and use it from the documentation alone, and the package passes your Envato checklist. Merge, tag `v1.0.0`, and submit.

## When Codex drifts: five prompts to reuse

When something goes wrong, paste one of these instead of improvising a new instruction. Replace the bracketed parts.

### 1. Start of every session (confirms it has the context)

```markdown
Read AGENTS.md, docs/DATA_MODEL.md, PROGRESS.md and docs/DECISIONS.md. Do not change any code. Reply in ten lines or fewer: what already exists, which phase is finished last, and what the next phase is. Then wait.
```

If the summary is wrong, fix the repo files before you paste the phase prompt.

### 2. Review (after Codex says a phase is done)

```markdown
Review the changes in `git diff main...phase-[N]` against AGENTS.md and the Phase [N] prompt. Do not change any code. List, as a checklist: any requirement not implemented; any rule from AGENTS.md that was broken (float money, missing owner scope, hardcoded text or URLs, missing policy or validation); any feature that was not requested; and any scenario in the prompt with no test. Say "none" for an empty category.
```

### 3. Fix (when your checks fail)

```markdown
Phase [N] has these failures:
[paste the exact failing step, the number you expected, and the number you got]
Fix only these. Do not refactor or add features. For each failure, first write or adjust a test that reproduces it, then fix the code, then run `composer test` and `./vendor/bin/pint`. Finish with the Phase Report.
```

### 4. Scope reset (when it builds things it was not asked to)

```markdown
You went beyond Phase [N]: [list what you saw]. Revert those changes and keep only what the Phase [N] prompt asked for. Run `composer test`, show `git diff --stat`, and confirm nothing from a later phase remains.
```

### 5. Data model change (when you want to change a table or rule)

```markdown
I want to change: [describe]. Do not edit code yet. List every table, column, rule, phase and test that would be affected. Wait for my approval. After approval: update docs/DATA_MODEL.md first, add a NEW migration (never edit a migration from a finished phase), update the affected tests, and record the decision in docs/DECISIONS.md.
```

### Common failures and what to do

| Symptom | Likely cause | What to do |
| --- | --- | --- |
| Tests pass but a number is wrong by hand | The test asserts the wrong value | Paste the worked example into prompt 3 and ask for a test using exactly those figures first |
| Codex invents a column or table | It did not read the data model | Run prompt 1, then prompt 5 if you want the change |
| It stops halfway and says it is done | Context ran out | Start a new session, run prompt 1, then paste: "Phase \[N\] is partly done. List what is missing against the prompt, then finish only that." |
| Earlier phases break after a new one | It edited old code broadly | Run `git diff --stat main...phase-[N]`; prompt 4 if files outside the phase changed |
| It asks too many questions | The prompt left a gap | Answer in `docs/DECISIONS.md`, then tell it to read that file and continue |
| Money is off by one cent | Rounding done in two places or with floats | Prompt 3, naming the exact input and both results, and require all math to go through `Money` |
