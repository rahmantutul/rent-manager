# Progress

## Phase 1 — Foundation — 2026-10-25

Implementation and verification complete on branch `phase-1`; awaiting the user's acceptance gate. Not merged or tagged.

- Retained the existing Laravel 12 skeleton, PHPUnit in-memory SQLite setup and composer test script.
- Built installer and layouts fresh because reference/cheque-studio is absent.
- Implemented requirements, database validation before .env writes, first owner/account creation, mail configuration, stale-cache/blank-key recovery and installation locking.
- Implemented exact owners/users/settings schema, deferred tenant_id foreign key, five-role authentication, role gates, owner resolver/scope/trait and login/logout.
- Added owner settings, super-admin branding, private logo storage, English translations, locale switcher and placeholder dashboards.
- Included production Vite assets; no buyer-side Node dependency.
- Passed 23 SQLite tests (139 assertions) and 1 MariaDB integration test (10 assertions), Pint, build, migration smoke and source scan.
- Browser-verified missing .env, wrong credentials without .env creation, stale cache/blank key, MariaDB install, owner login/logout, JPY/0 settings, tenant portal/admin denial, super-admin routing, global logo/name changes and installer lock.
- Local verification uses MariaDB 10.4.32; older supported server versions and SMTP delivery remain unverified.
- Full report: docs/PHASE_1_REPORT.md. No later-phase features built.

## Phase 2 - Properties, buildings and units - 2026-10-25

Implementation complete on branch `phase-2`; awaiting the user's acceptance gate. Not merged or tagged.

- Added exact properties, buildings and units migrations with soft deletes, owner scoping, foreign keys, unique unit numbers and building/status indexes.
- Added models, factories, relationships, property default-building creation and floor-label helper.
- Added owner/manager CRUD screens, accountant read-only access, filters, grouped floor unit lists and atomic bulk generation with clash reporting.
- Passed 27 tests and 174 assertions overall; Pint and Vite build pass. One existing Laravel test runner warning reports an output-buffer risk in the building/unit CRUD test despite its assertions passing.
- Full report: docs/PHASE_2_REPORT.md.
## Phase 3 — Money core and lookups (2026-10-25)

Implemented Money, lookup tables, numbering service/bootstrap command, lookup settings routes, and Phase 3 tests. SQLite suite: 32 passed, 182 assertions, one existing output-buffer warning.

## Phase 4 - Tenants and leases - 2026-10-25

Implemented tenant records, encrypted ID numbers, private attachments, leases, recurring charges, lifecycle actions, policies, screens, and owner-isolation tests. SQLite suite: 37 passed, 203 assertions.
