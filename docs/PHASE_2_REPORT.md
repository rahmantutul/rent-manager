# Phase 2 Report - 2026-10-25

## 1. Summary

Added properties, buildings and units with owner isolation, soft deletes, role policies, CRUD screens and atomic bulk generation.
Phase 2 is complete on branch `phase-2`; it has not been merged or tagged.

## 2. Files created or changed

- Schema: `database/migrations/2026_10_25_000001_create_properties_table.php`.
- Models and factories: `app/Models/Property.php`, `Building.php`, `Unit.php`, `database/factories/*`.
- Authorization: `app/Policies/PropertyPolicy.php`, `BuildingPolicy.php`, `UnitPolicy.php`; controller authorization support and policy registration.
- Requests and controllers: property, building and unit requests/controllers, including bulk generation.
- Routes and UI: admin routes, property/building/unit Blade screens, navigation and styles.
- Support/docs: floor helper, English translations, `docs/DECISIONS.md`, `PROGRESS.md`.
- Tests: `tests/Feature/PhaseTwoTest.php`.

## 3. Commands and results

- `php artisan test`: 27 passed, 174 assertions. The test runner reports one risky-test warning about an output buffer in the building/unit CRUD test; all four assertions in that test pass and no test fails.
- `php vendor/bin/pint --test`: passed after formatting.
- `npm run build`: passed.
- `git diff --check`: existing trailing blank-line notices remain in the Phase 1 README and PROGRESS files; no Phase 2 source whitespace errors remain.

## 4. Assumptions and decisions

Recorded in `docs/DECISIONS.md`: timestamps, bulk numbering, floor derivation, minor-unit display placeholder, allowed statuses and installer table allowlist.

## 5. Not done or skipped

Tenants, leases, billing, maintenance, reports and later Money formatting remain out of scope. Browser click-through on the Phase 2 screens was not run against the local installation in this session; automated HTTP tests cover the routes and authorization.

## 6. How to test by hand

1. Sign in as an owner or manager and open `/admin/properties`.
2. Add a property; confirm its default building appears. Add another building.
3. Open a building's Units page, use Bulk generate for floors 1-10, four units per floor, floor-letter numbering, and confirm 40 units and 3A-3D.
4. Repeat the same generation; confirm no rows are created and clashes are listed.
5. Filter by status, floor and type. Confirm Ground floor and Basement 1 labels.
6. Sign in as accountant; confirm lists work but create/update URLs return 403. Sign in as a tenant; confirm admin routes return 403.
7. Create a second owner and try the first owner's property URL; it must return 404.
