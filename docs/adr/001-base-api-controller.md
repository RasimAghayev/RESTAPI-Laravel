# ADR 001: Base API Controller for DRY CRUD Operations

## Status
Deferred (trait crash in PHP 8.5)

## Context
The three API controllers (`CustomerController`, `InvoiceController`, `InvoiceItemsController`) each implement the same CRUD scaffolding:
- `index()`: instantiate filter, transform request, query model, paginate, return collection resource
- `store()`: validate via FormRequest, create model, return resource
- `show()`: route-model binding, optional includes, return resource
- `update()`: validate via FormRequest, update model, return resource
- `destroy()`: delete model

This results in ~150 lines of near-identical code across three controllers. Changes to pagination, filtering, or resource wrapping require edits in three places.

## Decision Attempted
Create `App\Http\Controllers\Api\V1\ApiCrudTrait` with abstract methods for configuration:
- `getModelClass()` — Eloquent model class
- `getFilterClass()` — Filter class for query transformation
- `getResourceClass()` — Single resource class
- `getCollectionClass()` — Collection resource class
- `getStoreRequestClass()` / `getUpdateRequestClass()` — FormRequest classes

Concrete controllers use the trait and implement the abstract methods. Also attempted `BaseApiController` class inheritance.

## Outcome
Both approaches crashed with "Premature end of PHP process" in Docker test stage (PHP 8.5.12). The crash occurs during PHPUnit execution, not during application bootstrap. Root cause unknown — possibly PHP 8.5 trait abstract method handling, return type hints, or Laravel autoloading interaction.

## Fallback
Controllers extend `Controller` directly with explicit inline CRUD methods. No abstraction layer. Duplication remains (~120 lines across 3 controllers) but all 5 characterization tests pass reliably.

## Next Steps
- Revisit when PHP 8.5 trait/abstract method stability improves
- Consider Laravel's official CRUD controllers (if available in future)
- Monitor PHP 8.5 patch releases for trait-related fixes

## Consequences
**Positive**:
- Tests pass; no crashes in CI/CD
- Simple, explicit code; easy to debug

**Negative**:
- ~120 lines of duplication remain
- Changes to pagination/filtering/response format require edits in 3 places
- New resources must copy-paste CRUD scaffolding

**Neutral**:
- No behavior change; existing endpoints work identically
- Tests continue to pass without modification