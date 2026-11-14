# RESTAPI-Laravel — Test Report (Phase 11)

## 1. Environment

- Repo: `RESTAPI-Laravel`, branch `phase-11/tests` (worktree), base `master` `8d318b8`.
- Runtime in test image: PHP 8.5.12, PHPUnit 13.4.6, SQLite `:memory:`.
- Canonical command: `docker build --target test` (the `make test` target).
- Date: 2026-11-14.

## 2. Execution evidence (real)

| # | Command | Exit | Result |
|---|---------|------|--------|
| 1 | `docker build --target test -t restapi-laravel-p5-test:local .` | 1 | **FAIL** — PHPUnit step exits 2 with a fatal PHP parse error (PD-1, see §5) |
| 2 | Isolated diagnostic container: `deps` image + app copy with UTF-8 BOM stripped (repo untouched), full PHPUnit | 1 | 45 tests, 113 assertions, **7 failures** (38 pass) |
| 3 | Baseline only: `--filter 'ApiCharacterizationTest|ExampleTest'` | 0 | `OK (5 tests, 5 assertions)` |
| 4 | `vendor/bin/pint --test tests` | 0 | `PASS (11 files)` |
| 5 | BOM scan of committed blobs (`git show HEAD:<file>`) | 0 | `EF BB BF` on 3 production files (PD-1) |

Canonical run (1) cannot complete the suite because loading a Phase 9 form-request
file aborts PHP. Diagnostic run (2) strips the BOM **in a disposable container
copy only**, so the test-level results below are real and reproducible; the
repository is not modified.

## 3. Result summary

| Suite | Tests | Passed | Failed |
|-------|------:|-------:|-------:|
| Unit | 7 | 7 | 0 |
| Health | 2 | 2 | 0 |
| Auth | 2 | 2 | 0 |
| Customer | 11 | 9 | 2 |
| Invoice | 11 | 9 | 2 |
| InvoiceItems | 7 | 4 | 3 |
| Baseline (characterization + examples) | 5 | 5 | 0 |
| **Total** | **45** | **38** | **7** |

All 7 failures are production defects (R8), not test defects.

## 4. Test case inventory

### 4.1 Unit — `TC_UNIT_*`

| ID | Status |
|----|--------|
| TC_UNIT_INVOICE_SVC_001 | PASS |
| TC_UNIT_INVOICE_SVC_002 | PASS |
| TC_UNIT_INVOICEITEMS_SVC_001 | PASS |
| TC_UNIT_INVOICEITEMS_SVC_002 | PASS |
| TC_UNIT_FILTER_001 | PASS |
| TC_UNIT_FILTER_002 | PASS |
| TC_UNIT_FILTER_003 | PASS |

### 4.2 Health — `TC_HEALTH_*`

| ID | Status |
|----|--------|
| TC_HEALTH_LIVE_001 | PASS |
| TC_HEALTH_READY_001 | PASS |

### 4.3 Auth — `TC_AUTH_*`

| ID | Status |
|----|--------|
| TC_AUTH_USER_001 | PASS |
| TC_AUTH_USER_002 | PASS |

### 4.4 Customer — `TC_CUSTOMER_*`

| ID | Status | Note |
|----|--------|------|
| TC_CUSTOMER_INDEX_001 | PASS | |
| TC_CUSTOMER_INDEX_002 | PASS | includeInvoices eager load |
| TC_CUSTOMER_STORE_001 | FAIL | PD-2 MassAssignmentException (`name`) |
| TC_CUSTOMER_STORE_002 | PASS | 422 required fields |
| TC_CUSTOMER_STORE_003 | PASS | 422 type/email |
| TC_CUSTOMER_SHOW_001 | PASS | |
| TC_CUSTOMER_SHOW_002 | PASS | 404 |
| TC_CUSTOMER_UPDATE_001 | FAIL | PD-2 MassAssignmentException (`name`) |
| TC_CUSTOMER_UPDATE_002 | PASS | 422 email |
| TC_CUSTOMER_DELETE_001 | PASS | 204 |
| TC_CUSTOMER_EDGE_001 | PASS | `name[eq]` filter |

### 4.5 Invoice — `TC_INVOICE_*`

| ID | Status | Note |
|----|--------|------|
| TC_INVOICE_INDEX_001 | PASS | |
| TC_INVOICE_STORE_001 | FAIL | PD-2 MassAssignmentException (`customer_id`) |
| TC_INVOICE_STORE_002 | PASS | 422 required fields |
| TC_INVOICE_STORE_003 | PASS | 422 status/customer |
| TC_INVOICE_STORE_004 | PASS | 422 due-date ordering |
| TC_INVOICE_STORE_005 | PASS | 422 duplicate invoice_id |
| TC_INVOICE_STORE_006 | PASS | 422 empty items |
| TC_INVOICE_SHOW_001 | PASS | |
| TC_INVOICE_SHOW_002 | PASS | 404 |
| TC_INVOICE_UPDATE_001 | FAIL | PD-2 MassAssignmentException (`status`) |
| TC_INVOICE_DELETE_001 | PASS | 204 |

### 4.6 Invoice items — `TC_INVOICEITEMS_*`

| ID | Status | Note |
|----|--------|------|
| TC_INVOICEITEMS_INDEX_001 | PASS | |
| TC_INVOICEITEMS_STORE_001 | FAIL | PD-2 MassAssignmentException (`invoice_id`) |
| TC_INVOICEITEMS_STORE_002 | PASS | 422 required fields |
| TC_INVOICEITEMS_STORE_003 | PASS | 422 nonexistent invoice |
| TC_INVOICEITEMS_SHOW_001 | FAIL | PD-3 `data.item_id` is null for an existing item |
| TC_INVOICEITEMS_EDGE_001 | PASS | PUT/DELETE 405 |
| TC_INVOICEITEMS_EDGE_002 | FAIL | PD-3 missing item returns 200, not 404 |

### 4.7 Baseline (preserved, not authored in Phase 11)

| ID | Status |
|----|--------|
| ApiCharacterizationTest::customers_index | PASS |
| ApiCharacterizationTest::invoices_index | PASS |
| ApiCharacterizationTest::invoiceitems_index | PASS |
| Feature ExampleTest (`GET /`) | PASS |
| Unit ExampleTest | PASS |

## 5. Failure analysis (production defects — R8)

### PD-1 — UTF-8 BOM in Phase 9 request classes (suite blocker)

Committed blobs start with `EF BB BF`:

- `customer-invoice-api/app/Http/Requests/StoreCustomerRequest.php`
- `customer-invoice-api/app/Http/Requests/UpdateCustomerRequest.php`
- `customer-invoice-api/routes/api.php`

Effect: when the first POST/PUT to `/api/v1/customers` loads a request class, PHP
aborts with:

```
Fatal error: Namespace declaration statement has to be the very first statement
or after any declare call in the script in .../StoreCustomerRequest.php on line 3
```

The canonical Docker test stage therefore stops at `CustomerApiTest` store_001
(PHPUnit step exit 2). The BOM is a parse-level defect; no application code runs.

### PD-2 — Models missing `$fillable` (5 failures)

`Customer`, `Invoice` and `InvoiceItems` declare neither `$fillable` nor
`$guarded`, so every repository `create()`/`update()` throws:

```
Illuminate\Database\Eloquent\MassAssignmentException:
Add [name] to fillable property to allow mass assignment on [App\Models\Customer].
```

Affected endpoints: POST/PUT customers, POST/PUT invoices, POST invoice items.
All return HTTP 500 in the diagnostic run.

### PD-3 — Invoice-items route model binding (2 failures)

Route is `apiResource('invoiceitems', ...)` → parameter `{invoiceitem}`, but the
controller method parameter is `$invoiceItem`. Laravel's
`ImplicitRouteBinding::getParameterName` checks `invoiceItem` and `invoice_item`;
neither matches `invoiceitem`, so binding is skipped and an empty model is
injected. Result:

- `GET /api/v1/invoiceitems/{existing}` → 200 with `data.item_id = null`.
- `GET /api/v1/invoiceitems/999999` → 200 instead of 404.

## 6. Regression vs `docs/BASELINE.md`

`docs/BASELINE.md` is the Phase 5 document and records the then-red container
baseline. The current contract (5/5 green) was re-verified in this phase:
baseline tests pass 5/5, exit 0. No baseline test was modified in behaviour
(only Pint line-ending/EOF normalization was applied to the test files).

## 7. Coverage

Not measured: the image has no PCOV/Xdebug and the Makefile runs
`phpunit --no-coverage`. No coverage percentage is claimed.

## 8. Out-of-scope findings for the Orchestrator

1. **PD-1** (blocker): remove the UTF-8 BOM from the two Phase 9 request classes
   and `routes/api.php`. Responsible: Phase 9 (hardening).
2. **PD-2**: add `$fillable` (or equivalent guarded configuration) to
   `Customer`, `Invoice`, `InvoiceItems`; otherwise all write endpoints 500.
   Responsible: original model implementation (pre-Phase 5) / Phase 6.
3. **PD-3**: align the invoice-items route parameter and controller binding
   (e.g. `{invoiceitem}` ↔ `$invoiceItem`, or rename to `{invoice_item}` /
   `invoiceitems/{invoiceitem}` with a matching method parameter).
   Responsible: Phase 8 (v1 namespace refactor).

None of these were fixed by Phase 11.

## 9. Known limitations

- SQLite (tests) vs MySQL (runtime); no MySQL-specific behaviour exercised.
- No coverage driver; coverage not measured.
- No rate-limit, browser/E2E, security or performance tests.
- Canonical `make test`/`make verify` remains red until PD-1/PD-2/PD-3 are fixed
  by their owning phases; the test code is complete and committed on the branch.