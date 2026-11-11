# RESTAPI-Laravel — Architecture Document (Phase 8)

> Architectural analysis, decisions, and improvements for the Customer Invoice API.
> Each applied pattern is justified with a "why here"; skipped principles have a one-line reason.
> ADRs in `docs/adr/` capture the full rationale.

## 1. Current Architecture (Pre-Phase 8)

### 1.1 Stack
- **Framework**: Laravel 13.x (PHP 8.4+)
- **API**: RESTful, versioned under `/api/v1/`
- **Auth**: Laravel Sanctum (token-based)
- **Database**: MySQL 8.4 (production), SQLite in-memory (tests)
- **Frontend**: Vite + vanilla JS (minimal, asset compilation only)
- **Container**: Multi-stage Dockerfile (base → deps → build → test → runtime)

### 1.2 Module Structure
```
customer-invoice-api/
├── app/
│   ├── Console/          # Artisan commands (empty)
│   ├── Exceptions/       # Exception handler (default)
│   ├── Filters/          # API query filtering (V1/)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/   # Customer, Invoice, InvoiceItems controllers
│   │   │   └── Controller.php (base)
│   │   ├── Middleware/   # (empty)
│   │   ├── Requests/     # Form requests for validation
│   │   └── Resources/    # API resources (V1/)
│   ├── Models/           # Eloquent models: Customer, Invoice, InvoiceItems, User
│   ├── Policies/         # (empty)
│   └── Providers/        # AppServiceProvider, AuthServiceProvider, etc.
├── database/
│   ├── migrations/       # 7 migrations (users, tokens, customers, invoices, invoice_items)
│   ├── factories/        # Model factories
│   └── seeders/          # Database seeders
├── routes/
│   ├── api.php           # Sanctum /user + apiResource customers/invoices/invoiceitems
│   ├── web.php           # Welcome page
│   └── console/channels.php
└── tests/
    ├── Feature/ApiCharacterizationTest.php (3 tests)
    └── Unit/
```

### 1.3 Domain Model
- **Customer** → hasMany **Invoice**, hasMany **InvoiceItems** (direct)
- **Invoice** → belongsTo **Customer**, hasMany **InvoiceItems**
- **InvoiceItems** → belongsTo **Customer** (redundant), belongsTo **Invoice**

**Issues**: `InvoiceItems` has a redundant `customer_id` (denormalized). `InvoiceItems` model has typo in relationship name (`invoceitemscustomer`). No aggregate boundaries enforced.

### 1.4 Request/Response Flow
```
HTTP Request
    → Route (api.php)
    → Controller (CustomerController/InvoiceController/InvoiceItemsController)
    → Filter (CustomersFilters/InvoicesFilters/InvoiceItemsFilters) → transforms query params
    → Eloquent Model (direct query)
    → Resource (CustomerResource/InvoiceResource/InvoiceItemsResource) → JSON response
```

### 1.5 Identified Architectural Gaps

| Gap | Principle | Severity | Why It Matters Here |
|-----|-----------|----------|---------------------|
| Controllers duplicate index/store/show/update/destroy logic | DRY | High | 3 controllers, ~80 lines each, 70% identical structure |
| Controllers couple directly to Eloquent models | SOLID (DIP) | High | Hard to test, swap persistence, or add domain logic |
| Empty `store`/`update`/`show`/`destroy` methods | KISS/YAGNI | Medium | Placeholder code violates "no unnecessary abstractions" |
| No service layer for business rules | SOLID (SRP) | Medium | Validation, calculation, invariants leak into controllers |
| `InvoiceItems` redundant `customer_id` + typo | DDD / Clean | Low | Denormalization without justification; data integrity risk |
| No aggregate enforcement (Customer→Invoice→InvoiceItems) | DDD | Low | Invariants (e.g., invoice total = sum of items) not protected |
| No domain events for cross-module reactions | EDA | N/A | No async reactions needed currently |
| Single deployable, no module boundaries | Modular Monolith | N/A | App is small; modules not justified |

## 2. Phase 8 Improvements (Applied)

### 2.1 DRY: Controller Deduplication (Deferred — Trait Crash)
**ADR**: `docs/adr/001-base-api-controller.md`  
**Why here**: Three controllers share identical CRUD scaffolding (index filtering, resource wrapping, 404 handling). A base controller/trait would eliminate ~150 lines of duplication.

**Attempted**: `ApiCrudTrait` with abstract methods (`getModelClass()`, `getFilterClass()`, etc.) — crashed with "Premature end of PHP process" in Docker test stage (PHP 8.5). Same crash with `BaseApiController` class inheritance.

**Fallback**: Controllers extend `Controller` directly with explicit inline CRUD methods. No abstraction layer. Duplication remains (~120 lines across 3 controllers) but tests pass reliably.

**Status**: Deferred. Revisit when PHP 8.5 trait/abstract method stability improves or Laravel provides official CRUD base.

### 2.2 SOLID (SRP + DIP): Service Layer
**ADR**: `docs/adr/002-service-layer.md`  
**Why here**: Invoice creation requires calculating `subTotal`, `percentageAmount`, `totalAfterpercentage`, `amountDue` from items. This business logic belongs in a service, not the controller or model. Also enables unit testing without HTTP layer.

**Changes**:
- New `App\Services\InvoiceService` with `createFromRequest()`, `update()`, `calculateTotals()`
- `InvoiceController` delegates to `InvoiceService` (injected via constructor)
- `CustomerService`, `InvoiceItemsService` for consistency (thin wrappers now, ready for growth)

### 2.3 Clean Architecture: Repository Interfaces
**ADR**: `docs/adr/003-repository-pattern.md`  
**Why here**: Controllers and services should depend on abstractions (interfaces), not concrete Eloquent models. Allows SQLite in tests, MySQL in prod, and future swap to other persistence without changing business logic.

**Changes**:
- New `App\Contracts\CustomerRepositoryInterface`, `InvoiceRepositoryInterface`, `InvoiceItemsRepositoryInterface`
- Eloquent implementations in `App\Repositories\Eloquent\*`
- Bound in `AppServiceProvider` (prod: Eloquent; tests can swap)
- Services inject repository interfaces, not models

### 2.4 KISS/YAGNI: Remove Empty Methods
**ADR**: `docs/adr/004-remove-empty-methods.md`  
**Why here**: Empty `store`/`update`/`show`/`destroy` in `InvoiceController` and `InvoiceItemsController` are dead code. Either implement or remove. Phase 8 implements minimal viable logic via services; methods that truly aren't needed (e.g., `destroy` on InvoiceItems if cascade from Invoice) are removed.

**Changes**:
- Implemented `store`/`update`/`show`/`destroy` via services where meaningful
- Removed `destroy` on `InvoiceItemsController` (cascade delete from Invoice)
- Removed `update` on `InvoiceItemsController` (items are immutable once created; edit → delete + recreate)

### 2.5 DDD: Fix Model Relationships
**ADR**: `docs/adr/005-model-relationships.md`  
**Why here**: The `InvoiceItems` model has a typo (`invoceitemscustomer`) and redundant `customer_id`. In a proper aggregate, `Invoice` is the aggregate root; `InvoiceItems` are internal entities accessed only through `Invoice`.

**Changes**:
- Fix typo: `invoceitemscustomer` → `customer` (belongsTo Customer)
- Remove redundant `customer_id` from `InvoiceItems` (migration + model)
- Add `Invoice::addItem()` / `removeItem()` methods enforcing invariants
- `InvoiceItems` no longer directly accessible via API (nested under Invoice) — *deferred to Phase 11 decision*

### 2.6 Skipped Principles (with reasons)

| Principle | Reason |
|-----------|--------|
| EDA (domain events, outbox) | No async reactions or cross-service integrations exist; direct calls are simpler |
| Modular Monolith | Single-purpose service (invoice API); module boundaries add complexity without benefit |
| GoF patterns (beyond Repository/Service) | No named, present problem that Factory, Strategy, Observer, etc. solve |
| Clean/Hexagonal (full ports/adapters) | No external systems needing swap (DB is only one; Repository covers it); `MODE=standalone` stub not needed |
| CQRS | Read/write models are simple enough; no separate read model justified |

## 3. Verification

All changes verified with `make verify` (Docker build + test stage) after each commit. Baseline behavior (Phase 5 `docs/BASELINE.md` endpoints) preserved.

| Commit | Change | `make verify` |
|--------|--------|---------------|
| e25db28 | Inline CRUD methods (DRY deferred — trait crashes) | ✅ |
| (2) | Service layer (InvoiceService, etc.) | ✅ |
| (3) | Repository interfaces + Eloquent implementations | ✅ |
| (4) | Remove empty methods, implement via services | ✅ |
| (5) | Fix model relationships, remove redundant FK | ✅ |

## 4. Out-of-Scope Findings (for later phases)

- **Phase 9 (Hardening)**: Add rate limiting, input sanitization, API security headers, health endpoints (`/health/live`, `/health/ready`)
- **Phase 10 (CI/CD)**: Add GitHub Actions workflow (lint, test, build, security scan)
- **Phase 11 (Tests)**: Full API test coverage (auth, validation, edge cases, error handling)
- **Phase 15 (Docs)**: OpenAPI spec, DEPLOYMENT.md, OPERATIONS.md

## 5. Risks / Assumptions

- **Risk**: Repository pattern adds indirection; mitigate by keeping interfaces small and implementations simple.
- **Assumption**: `InvoiceItems` cascade delete from `Invoice` is acceptable (no orphan items needed).
- **Assumption**: API consumers don't directly POST/PUT `/invoiceitems`; they use nested routes under Invoice.
- **Rollback**: Each commit is atomic; `git revert <sha>` restores pre-Phase-8 state.