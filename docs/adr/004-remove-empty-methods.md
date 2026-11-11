# ADR 004: Remove Empty Controller Methods (KISS/YAGNI)

## Status
Accepted

## Context
`InvoiceController` and `InvoiceItemsController` have empty method bodies for `store`, `update`, `show`, `destroy` (only `//` comments). This is dead code that:
- Misleads developers into thinking functionality exists
- Violates KISS (unnecessary code) and YAGNI (methods for features not needed)
- Returns 200 OK with empty response for write operations — a bug

## Decision
**For `InvoiceController`**: Implement all methods via `InvoiceService` (create, read, update, delete invoices). Invoice is an aggregate root; full CRUD is justified.

**For `InvoiceItemsController`**:
- Keep `index` (list items, filterable by invoice/customer)
- Keep `store` (add item to invoice — implemented via `InvoiceService::addItem()`)
- **Remove `show`** — items accessed via `InvoiceResource` nested collection
- **Remove `update`** — items immutable once created; correction = delete + recreate
- **Remove `destroy`** — cascade delete from `Invoice` (items cannot exist without parent)

Routes updated: `invoiceitems` becomes nested under `invoices` for `store` only; `index` remains top-level for cross-invoice filtering.

## Consequences
**Positive**:
- No dead/placeholder code
- API surface matches actual domain model (items belong to invoice)
- Simpler controller (3 methods instead of 5)

**Negative**:
- Breaking change for consumers calling `PUT/PATCH/DELETE /api/v1/invoiceitems/{id}` — *mitigated: no consumers exist yet (Phase 5 baseline only tests GET index)*
- Nested route for `store` changes URL from `/api/v1/invoiceitems` to `/api/v1/invoices/{invoice}/items` — *documented in CHANGELOG*

**Neutral**:
- `GET /api/v1/invoiceitems` (filterable) preserved for reporting use cases
- Characterization test for `invoiceitems` index still passes