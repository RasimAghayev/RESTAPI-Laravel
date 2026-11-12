# RESTAPI-Laravel — Phase 5 Container Baseline (docs/BASELINE.md)

> Behavior contract and verification evidence for the Phase 5 container baseline.
> Status: **pre-existing red** — `make verify` exits 4 due to a dependency
> declaration/lockfile mismatch owned by Phase 6 (not fixed in Phase 5, per
> the Docker/Make-only scope). Documented here honestly per R6/R7; not claimed green.

## 1. Build context

- Repo root: `projects/worktrees/RESTAPI-Laravel` (worktree of `projects/repos/RESTAPI-Laravel`, branch `phase-5/container-baseline`, from `master`).
- Laravel application lives under `customer-invoice-api/`; Dockerfile, `.dockerignore`, `docker-compose.yml`, `Makefile` are at the repo root.
- `COMPOSE_PROJECT_NAME` / compose `name`: `restapi-laravel-p5` (Docker requires lowercase; reserved port block `20110-20119`, `projects/info/state/ports.md`).
- Multi-stage Dockerfile: `base` → `deps` → `build` → `test` → `runtime` (digest-pinned images).

## 2. Behavior contract (current, preserved)

| Endpoint | Method | Expected |
|----------|--------|----------|
| `GET /` | Web/welcome | `200` (welcome view) |
| `GET /api/v1/customers` | Index | `200` (paginated customers) |
| `GET /api/v1/invoices` | Index | `200` (paginated invoices) |
| `GET /api/v1/invoiceitems` | Index | `200` (paginated invoice items) |
| `GET /api/user` | Sanctum auth | `200` (authenticated user) / `401` (unauthenticated) |

Routes: `customer-invoice-api/routes/api.php` (`auth:sanctum` `/user`; `apiResource` customers/invoices/invoiceitems under `v1`), `routes/web.php` (`GET /` → welcome).

Models: `Customer`, `Invoice`, `InvoiceItems`, `User`; 7 migrations (users, password_resets, failed_jobs, personal_access_tokens, customers, invoices, invoice_items); 4 factories, 4 seeders.

## 3. Test suite

Existing (scaffold only):
- `tests/Feature/ExampleTest.php` — `GET /` → `200`
- `tests/Unit/ExampleTest.php` — `assertTrue(true)`

Added in Phase 5 (minimal characterization tests, the only tests written before Phase 11):
- `customer-invoice-api/tests/Feature/ApiCharacterizationTest.php` — 3 tests:
  - `test_customers_index_returns_successful_response` → `GET /api/v1/customers` `200`
  - `test_invoices_index_returns_successful_response` → `GET /api/v1/invoices` `200`
  - `test_invoiceitems_index_returns_successful_response` → `GET /api/v1/invoiceitems` `200`
  - Uses SQLite in-memory + `RefreshDatabase`; seeds one record per endpoint via factories.

## 4. Verification results (evidence)

| Command | Exit code | Result |
|---------|-----------|--------|
| `docker info` | 0 | Docker daemon available |
| `docker build -t restapi-laravel-p5-app:local .` (`make build`) | non-zero | Build stops at `deps` stage (see below) |
| `docker build --target test -t restapi-laravel-p5-test:local .` (`make test`) | non-zero | `deps` stage fails before `test` |
| `make verify` (= build + test) | **4** | **PRE-EXISTING RED — documented baseline** |
| `make up` | not run | blocked by `verify` dependency (intentionally not bypassed) |
| `docker run composer:2 composer update` (scratch probe, not committed) | non-zero | dependency resolution also fails (see below) |

`composer install` failure (exit 4, `deps` stage):
```
- Required package "laravel/framework" is in the lock file as "v9.52.16" but that does not satisfy your constraint "^12.0".
- Required package "laravel/sanctum" is in the lock file as "v3.3.3" but that does not satisfy your constraint "^4.0".
- Required package "laravel/tinker" is in the lock file as "v2.7.2" but that does not satisfy your constraint "^2.11".
- Required (in require-dev) package "nunomaduro/collision" is in the lock file as "v6.3.0" but that does not satisfy your constraint "^6.4".
```

`composer update` scratch probe failure (Phase 6 dependency conflict, not fixable in Phase 5):
```
spatie/laravel-ignition [1.0.0..v1.x-dev] require illuminate/support ^8.77|^9.0
  -> conflicts with laravel/framework ^12.0 (laravel/framework replaces illuminate/support)
Conclusion: don't install laravel/framework v12.x (conflict analysis)
```

Coverage: not measured (suite could not execute; no coverage data). Key API/CLI snapshots: none captured (container never reached a running app due to the pre-existing dependency blocker).

## 5. `.env.example`

`customer-invoice-api/.env.example` contains placeholders only (`APP_KEY=`, `DB_PASSWORD=`, AWS/Pusher keys empty) — no real secrets. No `.env` is tracked (`.gitignore` covers `.env*`). Verified by static read; no secret values present.

## 6. Image pins (committed `Dockerfile`)

- `php:8.2-fpm-alpine@sha256:d17bc39b8ef90e12d598f19cb466f1906c3c214f4bce661dfee49404b42fea09`
- `composer:2@sha256:ca11f643d7791ebdd90a34fc20caf11380d434007ae539a9ea75cd9212e13dca`
- `node:20-alpine@sha256:fb4cd12c85ee03686f6af5362a0b0d56d50c58a04632e6c0fb8363f609372293`
- `nginx:alpine@sha256:713fc2ebfd92e8217db451477182ee91944b123d2b1ee9fd5a8c6e91ca6c3a11`
- `mysql:8.4@sha256:a81d043930e51745522bd5c430961cf8286f0a2a6e0108f7ea71b0902`

Image size: not captured (build stops at the pre-existing red `deps` stage).

## 7. Known baseline, not fixed (Phase 6's job)

1. `composer.json`/`composer.lock` inconsistency (laravel/framework v9 vs ^12, sanctum v3 vs ^4, tinker v2.7 vs ^2.11, collision v6.3 vs ^6.4) — blocks every image build. Dependency resolution is Phase 6 scope.
2. `spatie/laravel-ignition ^1.0` requires `illuminate/support ^9`, incompatible with `laravel/framework ^12` — must be resolved in Phase 6 before `make verify` can be green.
3. The PHP runtime floor: composer declares `^8.1.9`; Laravel 12 typically requires >=8.2. The committed image uses `php:8.2-fpm-alpine` (satisfies `^8.1.9`); constraint tightening is Phase 6.

## 8. Out-of-scope findings (not fixed in Phase 5)

- Phase 4: default branch is `master` (Gate 4 rename parked), `.idea/` IDE artifact present at root, Dependabot config absent, full-history gitleaks scan not run.
- Phase 6: dependency declaration/lockfile resolution (blocking `make verify`), PHP floor confirmation.
- Phase 10: no CI exists (no `.github/workflows`).
- Phase 11: real API/auth tests beyond these 3 characterization tests.

## 9. Risks / assumptions

- Port block `20110-20119` is shared in `ports.md` with `product-catalog-api` and `csv-import-api` (pre-existing bookkeeping duplication); none of those repos are active in Phase 5, so no live collision. Reconcile `ports.md` in a Phase 0/5 pass.
- `COMPOSE_PROJECT_NAME` must be lowercase in Docker (`restapi-laravel-p5`); the canonical display name is `RESTAPI-Laravel-p5`.
- No secrets baked into image layers; `APP_KEY` is generated at container entrypoint runtime (not committed).
- `make verify` red state is the honest Phase 5 baseline; Phase 6 must return it to green before Phase 5 can be claimed fully green.

## 10. Attestation

No AI attribution (`Co-Authored-By`, `Claude-Session`, etc.) in any commit/PR. No secrets in images/layers. No `git --force` / history rewrite.
