# Rent & Building Manager: Data Model

Oct 22, 2026 · @Rahman Tutul

## Design principles

The schema is built so balances are always provable from rows, and nothing is tied to one country. Seven rules apply to every table below.

1. **Money is an integer in the smallest unit** (`bigInteger`, never float or decimal). Each owner sets `currency_code` and `minor_units` (0 for JPY or VND, 2 for USD, 3 for KWD).
2. **Posted financial rows are never edited or deleted.** A mistake is fixed by a new row: a void, a credit note, or a reversal. Balances are calculated from allocations; a cached `balance_due` on an invoice is allowed but can always be rebuilt.
3. **Every business table carries `owner_id`** from v1, enforced by a global scope. v1 sells as single-owner, and multi-owner SaaS mode later needs no rewrite.
4. **Nothing is hardcoded by country.** Charge types, tax rates, late-fee rules, numbering formats, billing cycles and currencies are data the owner edits.
5. **Master data uses soft deletes** (properties, units, tenants). Financial rows use a `status` column instead.
6. **Dates are `date`, moments are UTC `timestamp`.** The owner's time zone lives in settings and drives due dates and reminders.
7. **Shared-hosting safe.** Plain MySQL 5.7+ types, no stored procedures, and a scheduled command that is idempotent, so running it twice never duplicates an invoice.

## Entity map

&#91;embedded content: table relationships · 14 entities, 1 hub\]

Properties, buildings and units sit on the left, tenancy in the middle and money on the right. Expenses also link to properties and units, and the sections below follow the same route: property, tenancy, money, then expenses and maintenance.

## Core tables: owners, users, properties, units

The physical hierarchy is property, then building, then unit. A single house still gets one building, created automatically, so no screen needs a nullable path. Floors are a number on the unit, not a table, which keeps a 10-storey building to one join.

### owners

The data scope. Single-owner installs have exactly one row.

| Column | Type | Notes |
| --- | --- | --- |
| id | bigint unsigned PK |  |
| name | varchar(150) | Business or landlord name, shown on documents |
| email, phone | varchar | Contact on invoices |
| currency\_code | char(3) | ISO 4217, e.g. USD |
| minor\_units | tinyint | Decimal places of the currency (0, 2 or 3) |
| timezone | varchar(64) | Drives due dates and reminders |
| locale | varchar(10) | Default language |
| date\_format | varchar(20) | Display only |
| logo\_path | varchar(255) null | White-label branding |
| status | enum | active, suspended |

### users

| Column | Type | Notes |
| --- | --- | --- |
| id | bigint unsigned PK |  |
| owner\_id | FK owners, null | Null only for a super admin |
| tenant\_id | FK tenants, null | Set only for tenant-portal logins |
| name, email, password |  | Email unique |
| role | enum | super\_admin, owner, manager, accountant, tenant |
| permissions | json null | Optional per-user overrides |
| locale | varchar(10) null | Overrides the owner default |
| is\_active | boolean |  |
| last\_login\_at | timestamp null |  |

### properties

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id |  |  |
| name | varchar(150) |  |
| code | varchar(30) | Unique per owner, used in numbering |
| type | enum | residential, commercial, mixed |
| address\_line1, address\_line2, city, state, postal\_code | varchar |  |
| country\_code | char(2) |  |
| manager\_user\_id | FK users, null | Default manager |
| notes | text null |  |
| deleted\_at | timestamp null | Soft delete |

### buildings

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id |  |  |
| property\_id | FK properties |  |
| name | varchar(100) | "Block A", or the property name for single buildings |
| floors\_count | smallint null | Display only |
| deleted\_at | timestamp null |  |

### units

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id |  |  |
| building\_id | FK buildings |  |
| unit\_number | varchar(30) | Unique per building, e.g. "3B" |
| floor\_level | smallint | 0 ground, negative for basements |
| unit\_type | enum | apartment, shop, office, parking, storage, other |
| bedrooms, bathrooms | tinyint null |  |
| area, area\_unit | decimal(10,2) null, enum | sqft or sqm |
| default\_rent | bigint | Minor units, pre-fills new leases |
| status | enum | vacant, occupied, maintenance, reserved; a cache, rebuilt from the active lease |
| features | json null | Free-form amenities |
| notes | text null |  |
| deleted\_at | timestamp null |  |

## Tenants and leases

A lease ties one tenant to one unit and owns the billing schedule. Rent is not a column on the lease: it is a recurring charge row, so a rent increase is a new row with a start date and the old rate stays in history.

### tenants

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id |  |  |
| type | enum | individual, company |
| name | varchar(150) | Person, or contact person for a company |
| company\_name | varchar(150) null |  |
| email, phone, alt\_phone | varchar | Email unique per owner when set |
| id\_document\_type | varchar(40) null | Passport, national ID, and so on |
| id\_document\_number | text null | Encrypted cast |
| date\_of\_birth | date null |  |
| emergency\_contact\_name, emergency\_contact\_phone | varchar null |  |
| previous\_address | text null |  |
| status | enum | prospect, active, former |
| portal\_enabled | boolean | Allows a tenant login |
| notes | text null |  |
| deleted\_at | timestamp null |  |

### leases

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id |  |  |
| lease\_number | varchar(40) | Unique per owner, from the numbering table |
| unit\_id | FK units |  |
| tenant\_id | FK tenants | Primary tenant |
| start\_date | date |  |
| end\_date | date null | Null means open-ended, with no fixed end |
| move\_out\_date | date null | Actual exit |
| status | enum | draft, active, ended, terminated |
| active\_unit\_id | bigint null | Equals unit\_id only while active; a unique index on it blocks two active leases on one unit |
| billing\_cycle | enum | monthly, quarterly, semiannual, yearly |
| billing\_day | tinyint | Day of month the invoice is issued; 28 is the maximum, 0 means last day |
| due\_after\_days | smallint | Days from issue to due date |
| grace\_days | smallint | Days after due before a late fee applies |
| next\_invoice\_date | date null | The scheduler pointer; moved forward when an invoice is created |
| auto\_invoice | boolean | Off means invoices are created by hand |
| prorate\_first\_period | boolean | Pro-rate the first partial period |
| security\_deposit | bigint | Minor units, the agreed amount |
| late\_fee\_rule\_id | FK late\_fee\_rules null |  |
| notice\_period\_days | smallint null |  |
| termination\_reason | text null |  |
| terms | text null | Printable lease notes |

### lease\_charges

Every recurring amount on a lease, including rent.

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id |  |  |
| lease\_id | FK leases |  |
| charge\_type\_id | FK charge\_types | Rent, service charge, parking, and so on |
| description | varchar(150) null | Overrides the type name on the invoice |
| amount | bigint | Minor units |
| frequency | enum | every\_cycle, monthly, quarterly, yearly, once |
| starts\_on | date |  |
| ends\_on | date null | Null means until the lease ends |
| tax\_rate\_id | FK tax\_rates null |  |
| is\_active | boolean |  |

### lease\_tenants

Co-tenants who share responsibility and can use the portal. The primary tenant on the lease row is not repeated here.

| Column | Type | Notes |
| --- | --- | --- |
| lease\_id, tenant\_id | composite PK |  |
| share\_percent | decimal(5,2) null | For reporting only; invoices still go to the primary tenant |

## Billing tables

Billing is the part buyers will test hardest, so it is the most strictly modelled. An invoice has lines, a payment can be split across invoices through allocations, and the security deposit sits in its own ledger because it is money held, not income.

### charge\_types, tax\_rates, late\_fee\_rules

Owner-editable lookup data. Seeded with rent and late fee as system rows that cannot be deleted.

| Table | Columns | Notes |
| --- | --- | --- |
| charge\_types | id, owner\_id, name, kind (rent, service, utility, deposit, late\_fee, other), default\_tax\_rate\_id null, is\_system, is\_active, sort\_order | Kind drives reports and system behaviour |
| tax\_rates | id, owner\_id, name, rate decimal(6,3) as a percent, is\_inclusive, is\_active | Rate is copied onto each invoice line, so later changes never rewrite history |
| late\_fee\_rules | id, owner\_id, name, type (flat, percent\_of\_balance), flat\_amount bigint null, percent decimal(6,3) null, repeat\_every (once, week, month), max\_amount bigint null | Referenced by a lease; grace days live on the lease |

### invoices

Also holds credit notes, so a correction is a negative document tied to the original.

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id |  |  |
| invoice\_number | varchar(40) | Unique per owner |
| invoice\_type | enum | invoice, credit\_note |
| source | enum | recurring, manual, late\_fee, deposit |
| recurring\_key | varchar(80) null | Unique; format `lease:{id}:{period_start}`. This makes the scheduler idempotent |
| lease\_id, tenant\_id, unit\_id | FKs | Tenant and unit are copied so history survives lease changes |
| credit\_for\_invoice\_id | FK invoices null | Set on credit notes |
| period\_start, period\_end | date null |  |
| issue\_date, due\_date | date |  |
| status | enum | draft, issued, void |
| payment\_status | enum | unpaid, partial, paid; a cache rebuilt from allocations |
| subtotal, tax\_total, total | bigint | Minor units; credit notes are negative |
| amount\_paid, balance\_due | bigint | Cached from allocations |
| currency\_code | char(3) | Copied from the owner at creation |
| notes | text null |  |
| sent\_at, voided\_at | timestamp null |  |
| void\_reason | varchar(255) null |  |
| created\_by | FK users null | Null when the scheduler made it |

Overdue is not stored. It is `status = issued`, `payment_status` not paid, and `due_date` before today.

### invoice\_lines

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id, invoice\_id |  |  |
| charge\_type\_id, lease\_charge\_id | FK null | Which charge produced the line |
| description | varchar(200) |  |
| quantity | decimal(10,2) | 1 for rent; units consumed for metered utilities |
| unit\_price | bigint | Minor units |
| line\_subtotal | bigint | quantity times unit\_price, rounded once |
| tax\_rate\_id | FK null |  |
| tax\_rate\_value | decimal(6,3) | Snapshot of the percent used |
| tax\_amount, line\_total | bigint |  |
| sort\_order | smallint |  |

### payments

Money received. The receipt number is the payment number.

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id |  |  |
| payment\_number | varchar(40) | Unique per owner |
| tenant\_id | FK tenants |  |
| lease\_id | FK leases null |  |
| received\_on | date |  |
| method | enum | cash, bank\_transfer, cheque, card, online, wallet, deposit, other |
| reference | varchar(120) null | Cheque, transfer or gateway reference |
| amount | bigint | Minor units, always positive |
| unallocated\_amount | bigint | Cached. Anything above zero is tenant credit on account |
| gateway, gateway\_txn\_id | varchar null | For online payments, added later |
| status | enum | posted, void |
| voided\_at, void\_reason |  |  |
| received\_by | FK users null |  |
| notes | text null |  |

### payment\_allocations

Which payment paid which invoice, and how much. Append-only: a change voids the old row and inserts a new one.

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id |  |  |
| payment\_id, invoice\_id | FKs | Indexed together, not unique |
| amount | bigint | Positive |
| voided\_at | timestamp null | Set when the payment is voided; such rows are ignored in balances |
| created\_at | timestamp |  |

### deposit\_transactions

The security deposit ledger. Held balance is received minus deductions, refunds and forfeits.

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id, lease\_id |  |  |
| type | enum | received, deduction, refund, forfeited, adjustment |
| amount | bigint | Always positive; the type gives the sign |
| transaction\_date | date |  |
| payment\_id | FK null | Set on a deduction row: the payment with method deposit that settled an invoice |
| invoice\_id | FK null | Set when a deduction paid an invoice |
| description | varchar(200) null |  |
| receipt\_number | varchar(40) null | Set on a received deposit, from the receipt number sequence |
| created\_by | FK users null |  |

## Expenses, maintenance and utilities

Expenses feed the income-versus-expense report, and a maintenance request can produce an expense and, when a tenant caused the damage, a charge on their next invoice.

### expenses

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id |  |  |
| property\_id | FK properties | Required, so every expense reports somewhere |
| building\_id, unit\_id | FK null | Narrow the scope when known |
| category\_id | FK expense\_categories |  |
| vendor\_id | FK vendors null |  |
| maintenance\_request\_id | FK null | Links the cost to a request |
| expense\_date | date |  |
| amount, tax\_amount | bigint | Minor units. amount is the total paid including tax; tax\_amount is the tax contained in it, for information only |
| payment\_method | enum | cash, bank\_transfer, cheque, card, other |
| reference | varchar(120) null |  |
| description | varchar(255) |  |
| invoice\_line\_id | FK invoice\_lines null | Set when the cost was charged back to a tenant |
| status | enum | posted, void |
| created\_by | FK users null |  |

Receipts are stored in the attachments table, not as columns.

### maintenance\_requests

| Column | Type | Notes |
| --- | --- | --- |
| id, owner\_id, unit\_id |  |  |
| tenant\_id | FK null | Null when staff report it |
| reported\_by | FK users null |  |
| title | varchar(150) |  |
| description | text null |  |
| category | varchar(60) null | Plumbing, electrical, and so on |
| priority | enum | low, normal, high, urgent |
| status | enum | open, in\_progress, waiting, resolved, closed |
| assigned\_user\_id, vendor\_id | FK null |  |
| reported\_at | timestamp |  |
| scheduled\_for | date null |  |
| resolved\_at | timestamp null |  |
| estimated\_cost | bigint null |  |
| resolution\_notes | text null |  |

### Smaller tables

| Table | Columns | Notes |
| --- | --- | --- |
| vendors | id, owner\_id, name, service\_type, contact\_name, email, phone, address, tax\_number, notes, is\_active, deleted\_at | Contractors and suppliers |
| expense\_categories | id, owner\_id, name, parent\_id null, is\_active | Seeded with repairs, salaries, utilities, insurance, taxes |
| utility\_meters | id, owner\_id, building\_id, unit\_id null, utility\_type, name, unit\_label, rate decimal(12,4), charge\_type\_id | A null unit means a shared meter. The rate is a decimal in major units because prices per kWh or m3 are fractional; the invoice line total is rounded once to minor units |
| meter\_readings | id, owner\_id, meter\_id, reading\_date, reading decimal(14,3), previous\_reading decimal(14,3), consumption decimal(14,3), invoice\_line\_id null, recorded\_by | Billed once; the line link prevents double billing |
| assets | id, owner\_id, property\_id, building\_id null, unit\_id null, name, category, serial\_number, purchase\_date, purchase\_cost bigint, warranty\_until, next\_service\_date, status, notes | Lifts, generators, air conditioners; covers the asset-management requests buyers made on a competitor |

## Supporting tables

Six tables carry settings, document numbers, messages, files and the audit trail. Laravel's own tables (jobs, failed\_jobs, sessions, cache, password resets, migrations) are not repeated here, and your installer's tables carry over from Cheque Studio unchanged.

| Table | Columns | Notes |
| --- | --- | --- |
| settings | id, owner\_id null, key varchar(100), value text, unique on owner\_id and key | A null owner means an install-wide value such as white-label name and logo. Owner rows hold invoice footer text, reminder timing, late-fee switch, and similar options |
| numbering\_sequences | id, owner\_id, doc\_type (invoice, credit\_note, payment, lease), prefix, format varchar(60), next\_number bigint, reset\_policy (never, yearly, monthly), last\_reset\_on | Format example: `{prefix}{YYYY}-{seq:5}`. The next number is taken inside a transaction with a row lock, so numbers never repeat or skip |
| message\_templates | id, owner\_id, key (invoice\_issued, payment\_received, overdue\_reminder, lease\_expiring, maintenance\_update), channel (email, sms), locale, subject null, body | Placeholders such as `{tenant_name}` and `{amount_due}` |
| notification\_logs | id, owner\_id, tenant\_id null, user\_id null, channel, template\_key, recipient, subject null, status (queued, sent, failed), error null, related\_type, related\_id, sent\_at null | Lets an owner see what was sent and retry failures |
| attachments | id, owner\_id, attachable\_type, attachable\_id, disk, path, original\_name, mime, size, uploaded\_by, created\_at | Polymorphic: ID documents on tenants, signed leases, expense receipts, maintenance photos. Files stay on a private disk and are served through a permission check |
| audit\_logs | id, owner\_id null, user\_id null, event, auditable\_type, auditable\_id, old\_values json null, new\_values json null, ip, user\_agent, created\_at | Insert-only, with no updated\_at. Written for every change to invoices, payments, leases, deposits and settings |

## Billing rules

These rules turn the tables into behaviour. Each one is deterministic and covered by a test, because a wrong balance is the quickest way to a bad review.

### Recurring invoice generation

A daily scheduled command, `rent:generate`, runs these steps for each lease.

1. Select active leases with `auto_invoice` on and `next_invoice_date` on or before today in the owner's time zone.
2. Work out the period: it starts at `next_invoice_date` and runs one billing cycle.
3. Collect the lease charges that overlap the period. Charges set to `once` are skipped if already billed.
4. Pro-rate partial periods (first period, last period, or a mid-period rent change) once per line, with the formula below.
5. Calculate tax per line, and sum the lines for the invoice total. The total is never rounded separately, so lines and total always agree.
6. Insert the invoice with `recurring_key = lease:{id}:{period_start}`. The unique index makes a second run fail harmlessly, so the command can be re-run after a crash.
7. Move `next_invoice_date` forward by one cycle, or clear it once the lease end date has passed.
8. Apply any unallocated tenant credit to the new invoice, oldest credit first, then queue the notification.

```latex
line\_amount = round\left(charge\_amount \times \frac{days\_in\_use}{days\_in\_period}\right)
```

Rounding is half up, to the currency's minor units.

### Payments and allocation

- An allocation can never exceed the payment's unallocated amount or the invoice's balance due. Both caches are updated in the same transaction as the allocation.
- Allocation is automatic by default, oldest due date first, and can be set by hand per invoice.
- Overpayment stays on the payment as `unallocated_amount`, shown as tenant credit. Cash refunds of credit are left for a later version.
- Voiding a payment marks it void and stamps its allocations with `voided_at`; the affected invoices go back to unpaid or partial.
- An invoice with live allocations cannot be voided. Issue a credit note instead.

### Late fees

A daily command, `fees:apply`, finds issued invoices past `due_date + grace_days` with a balance above zero on a lease that has a late-fee rule. It creates a separate small invoice with source `late_fee` and `recurring_key = late:{invoice_id}:{n}`, so a fee is never created twice, and invoices whose source is late\_fee are skipped, so a fee is never charged on a fee. Repeating rules stop at the rule's `max_amount` for that invoice.

### Security deposits

A deposit is a liability while it is held, so receiving or refunding one is never income; it becomes income only when it is applied to an invoice or forfeited. Receiving a deposit creates a deposit\_transactions row with its own receipt number and never a payments row, so it cannot turn into tenant credit. A deposit has its own held-balance figure. Applying a deposit to an invoice creates a payment with method `deposit` and a linked deduction row, so the normal allocation logic still applies.

### Balances and rebuilds

- Tenant balance is the sum of balance due on issued invoices, minus unallocated credit on posted payments.
- Cached amounts (`amount_paid`, `balance_due`, `payment_status`, `unallocated_amount`) are rebuildable from allocations by `billing:rebuild-balances`. This gives support a safe fix if a cache ever drifts.

### Leases

- Open-ended leases bill until terminated. A lease with an end date bills its last period pro-rated and stops.
- A rent increase ends the old rent charge on a date and adds a new charge the day after. A period that spans the change is split by days.
- Termination or move-out records the actual date, sets the lease end date to it so billing stops, sets the unit's status back to vacant, and surfaces the deposit settlement.

## Indexes and integrity rules

The database enforces what it can, and the application enforces the rest, because shared hosts run everything from MySQL 5.7 to MariaDB.

| Table | Index or constraint | Why |
| --- | --- | --- |
| every table | Index on `owner_id`; composite with the usual filter on busy tables | Owner scoping stays fast |
| invoices | Unique `owner_id`, `invoice_number` | No duplicate numbers |
| invoices | Unique `recurring_key` | Makes the scheduler safe to re-run |
| invoices | Index `owner_id`, `status`, `payment_status`, `due_date` | The arrears and overdue report |
| invoices | Index `lease_id`, `period_start` | Finds a lease's billing history |
| payments | Unique `owner_id`, `payment_number`; index `tenant_id`, `received_on` | Receipts and tenant statements |
| payment\_allocations | Non-unique index `payment_id`, `invoice_id`; index `invoice_id` | Fast allocation lookups and balance rebuilds |
| leases | Unique `owner_id`, `lease_number` |  |
| leases | Unique `active_unit_id` | A unit cannot have two active leases; the column is null when the lease is not active |
| leases | Index `status`, `next_invoice_date` | The scheduler's daily query |
| units | Unique `building_id`, `unit_number`; index `building_id`, `status` | Vacancy views |
| tenants | Unique `owner_id`, `email` | Nulls repeat freely in MySQL, so tenants without an email are fine |
| numbering\_sequences, settings | Unique `owner_id`, `doc_type`; unique `owner_id`, `key` | One row per document type and per setting |
| meter\_readings | Unique `meter_id`, `reading_date` | No duplicate reading for a day |

### Rules outside indexes

- **Foreign keys on financial tables use RESTRICT.** Invoices, lines, payments, allocations and deposit rows cannot be deleted from under their parents. Master data is soft-deleted instead.
- **Row locks.** Allocation and number generation run in a transaction with `lockForUpdate()`, so two cashiers cannot over-allocate a payment or take the same number.
- **Amount checks in the application** (positive amounts, allocation limits, no negative balances from voids). Add `CHECK` constraints where the server supports them, but never rely on them.
- **Encrypted columns:** `tenants.id_document_number`, SMTP and gateway secrets in settings. The installer should warn that losing `APP_KEY` makes them unreadable.
- **Character set** `utf8mb4` with `utf8mb4_unicode_ci`, so names in any script store correctly.
- **Signed `bigint` for money** everywhere, since credit notes are negative.

## Scenario check

I walked 12 real situations through the schema. Eight work as designed, three exposed gaps that are now fixed in the sections above, and one is deliberately left for a later version.

| Scenario | How the schema handles it | Result |
| --- | --- | --- |
| Tenant pays 700 of a 1,200 invoice, then 500 | Two payments, each allocated to the invoice; payment status goes partial, then paid | Works |
| Tenant pays 1,500 against 1,200 | 300 stays as `unallocated_amount` and is applied to the next invoice when it is created | Works |
| Tenant pays three months in advance | Same credit mechanism; each new invoice absorbs credit in step 8 of generation | Works |
| Rent rises in the middle of a billing period | Old charge gets an end date, new charge starts the next day; the period gets two pro-rated lines | Works |
| Open-ended lease | `end_date` is null, so the scheduler keeps billing until the lease is terminated | Works |
| Bounced cheque recorded as paid | Void the payment; its allocations get `voided_at` and the invoice returns to unpaid | Works |
| Two cashiers allocate the same payment at once | Row lock plus the allocation limit checks | Works |
| Currency with no decimals and huge amounts (100,000,000 a month) | `bigint` and `minor_units = 0` | Works |
| Late fee on an overdue invoice | A separate late-fee invoice with its own recurring key. **Gap:** the rule could charge a fee on that fee | Fixed: late-fee invoices are skipped by the fee command |
| Tenant leaves early | Termination sets status and move-out date. **Gap:** nothing stopped invoicing | Fixed: termination also sets `end_date`, so the last period is pro-rated and billing stops |
| Adding 50 to a payment already allocated 100 to the same invoice | **Gap:** a unique key on payment and invoice conflicts with append-only rows | Fixed: that unique key is removed; a change voids the old row and inserts a new one |
| One electricity meter shared by several flats | The meter can belong to the building, but nothing splits its cost | Later: add a share table by area or equal split in v1.1 |

## Migration order and scope

Version 1 is 27 tables. Build the billing engine and its tests (steps 1 to 8) before any screens, since every report depends on it.

### Migration order

1. `owners`, `users`
2. `settings`, `numbering_sequences`, `message_templates`
3. `properties`, `buildings`, `units`
4. `tenants`, then a follow-up migration adds the `users.tenant_id` foreign key
5. `charge_types`, `tax_rates`, `late_fee_rules`
6. `leases`, `lease_charges`, `lease_tenants`
7. `invoices`, `invoice_lines`
8. `payments`, `payment_allocations`, `deposit_transactions`
9. `vendors`, `expense_categories`, `expenses`, `maintenance_requests`
10. `attachments`, `audit_logs`, `notification_logs`
11. After launch: `utility_meters`, `meter_readings`, `assets`

### What is in version 1

| Area | Version 1 | Later |
| --- | --- | --- |
| Properties, buildings, units, tenants, leases, recurring charges | Yes |  |
| Invoices, credit notes, payments, allocations, late fees, deposits | Yes |  |
| Expenses, vendors, categories | Yes | Recurring expenses such as salaries (v1.1) |
| Maintenance requests, including from the tenant portal | Yes |  |
| Numbering, settings, templates, attachments, audit log, notification log | Yes |  |
| Metered utilities and assets |  | v1.1 |
| Online payment gateways | Columns exist, unused | v1.1 |
| Splitting a shared meter across units |  | v1.1 |
| Cash refunds of tenant credit |  | Later |
| Multi-owner sign-up and plans | `owner_id` is in place from day one | Later |
| Several currencies per owner |  | Later |
