# RESTAPI-Laravel — Testing Strategy (Phase 11)

## 1. Scope

Phase 11 adds the automated test layer for the Laravel API. It is **test-only**:
no production/runtime code, Dockerfile, Makefile, composer manifest, config or CI
file is modified by this phase. Production defects found while testing are
reported to the Orchestrator (R8) and are not fixed here.

The existing baseline (`ApiCharacterizationTest` + scaffold example tests, 5
tests / 5 assertions) is the regression contract and must stay green.

## 2. Test layers and modules

| Layer | Location | What it covers |
|-------|----------|----------------|
| Unit | `customer-invoice-api/tests/Unit/ServiceAndFilterUnitTest.php` | `InvoiceService::calculateTotals`, `InvoiceItemsService::createItem/updateItem` (cemi), `ApiFilter::transform` |
| Feature / API | `tests/Feature/HealthApiTest.php` | `/api/health/live`, `/api/health/ready` |
| Feature / API | `tests/Feature/AuthApiTest.php` | `/api/user` authenticated (Sanctum) and unauthenticated |
| Feature / API | `tests/Feature/CustomerApiTest.php` | customers index/show/store/update/delete, validation, filtering, 404 |
| Feature / API | `tests/Feature/InvoiceApiTest.php` | invoices index/show/store/update/delete, validation, 404 |
| Feature / API | `tests/Feature/InvoiceItemsApiTest.php` | invoice items index/show/store, validation, 404, method-not-allowed |

Coverage areas: unit logic, HTTP routing/status, validation (422), authentication
(401/200), error handling (404/405), and edge cases (duplicate ids, empty item
list, date ordering, unknown filter params).

## 3. Test identity and status model

Every test carries a canonical ID `TC_<MODULE>_<SCENARIO>_<NUMBER>`, recorded in
a `@testdox` annotation (PHPUnit prints it with `--testdox`). Method names are
lower-case snake_case so the repository's Pint rule `php_unit_method_casing` is
satisfied; the uppercase ID lives in the annotation and in `TEST_REPORT.md`.

Each case is recorded as exactly one of: `PASS`, `FAIL`, `BLOCKED`, `NOT RUN`,
`SKIPPED`, `N/A`.

## 4. Determinism and speed

- `RefreshDatabase` + SQLite `:memory:`; each test runs in a transaction.
- State is created through factories; no dependency on pre-existing rows.
- Payloads are fixed literals; no wall-clock, random or network dependence.
- No sleeps or retries; the suite runs in well under a second.
- Mocking is limited to unit tests, where `InvoiceItemsRepositoryInterface` is
  replaced with a Mockery double to assert `cemi` is computed without a database.
  Feature tests exercise the real HTTP kernel, validation, Eloquent and SQLite.

## 5. Environments

1. **Canonical Docker test stage** — `docker build --target test` / `make test`,
   PHP 8.5.12, PHPUnit 13.4.6, SQLite `:memory:`. This is the required CI path.
2. **Isolated diagnostic container** — the same `deps` image with the app copied
   in; used only to isolate test-level failures from a production parse blocker
   (see `TEST_REPORT.md`). The repository working tree is never modified.

## 6. Regression policy

The 5 baseline tests are run on their own (`--filter 'ApiCharacterizationTest|ExampleTest'`)
and must remain 5/5. A failing test is never deleted, weakened, or skipped to
make the suite green; failures caused by production code are reported as
out-of-scope findings.

## 7. Coverage

The test image has no PCOV or Xdebug and the Makefile runs PHPUnit with
`--no-coverage`, so line/branch coverage is **not measured**. This is recorded
as a known limitation, not estimated.

## 8. Execution

```powershell
# canonical
make test            # docker build --target test (PHPUnit inside the image)
# targeted baseline
docker run --rm <test-image> php vendor/phpunit/phpunit/phpunit --no-coverage --filter 'ApiCharacterizationTest|ExampleTest'
# style (test files only)
vendor/bin/pint --test tests
```

## 9. Known limitations

- SQLite (tests) differs from MySQL (production): no MySQL-specific SQL, JSON,
  concurrency or transaction-isolation behaviour is exercised.
- No browser/E2E layer; no rate-limit (60/min) test; no security/penetration
  tests (Phase 9 owns the audit).
- No performance/load testing (Phase 12 scope).
- Coverage is not measured (no coverage driver in the image).
- The `GET /` welcome view is covered by the scaffold example test only.