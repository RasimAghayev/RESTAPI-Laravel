# ADR 005: Fix Model Relationships and Aggregate Boundaries (DDD)

## Status
Accepted

## Context
Current model issues:
1. **Typo**: `InvoiceItems::invoceitemscustomer()` should be `customer()`
2. **Redundant FK**: `InvoiceItems` has `customer_id` column + relationship, but `Invoice` already belongs to `Customer`. An item's customer is always `invoice->customer`.
3. **No aggregate enforcement**: `InvoiceItems` can be created/updated/deleted directly, bypassing `Invoice` invariants (e.g., `totalAfterpercentage` = sum of items, status transitions).

In DDD terms, `Invoice` is the aggregate root; `InvoiceItems` are internal entities. External access should go through `Invoice`.

## Decision
1. **Fix typo**: Rename `invoceitemscustomer()` → `customer()` (belongsTo Customer)
2. **Remove redundant `customer_id`**:
   - New migration: drop `customer_id` column + FK from `invoice_items` table
   - Remove `customer_id` from `InvoiceItems` `$fillable` / `$attributes`
   - Access customer via `$item->invoice->customer`
3. **Enforce aggregate boundary**:
   - Add `Invoice::addItem(array $data)`: creates item, recalculates totals, saves
   - Add `Invoice::removeItem(InvoiceItems $item)`: deletes item, recalculates totals
   - `InvoiceItems` no longer directly createable via `InvoiceItems::create()`
   - Service layer (`InvoiceService`) is the only entry point for item mutations
4. **API routes** (per ADR 004):
   - `POST /api/v1/invoices/{invoice}/items` → `InvoiceService::addItem()`
   - `DELETE /api/v1/invoices/{invoice}/items/{item}` → `InvoiceService::removeItem()`
   - `GET /api/v1/invoiceitems` remains (read-only, filterable for reporting)

## Consequences
**Positive**:
- Data integrity: item totals always match invoice totals
- Clear aggregate boundary: `Invoice` controls its items
- Simpler `InvoiceItems` model (no redundant FK, no direct mutations)

**Negative**:
- Migration drops column — requires care in production (Phase 9+)
- `InvoiceItems` factory/seeder must create via `Invoice::addItem()`
- Slight API change for item creation (nested under invoice) — *no existing consumers*

**Neutral**:
- Read-only `GET /api/v1/invoiceitems` preserved for cross-invoice queries
- Characterization test for `invoiceitems` index unchanged