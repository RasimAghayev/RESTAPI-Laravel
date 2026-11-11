# ADR 001: Base API Controller for DRY CRUD Operations

## Status
Accepted

## Context
The three API controllers (`CustomerController`, `InvoiceController`, `InvoiceItemsController`) each implement the same CRUD scaffolding:
- `index()`: instantiate filter, transform request, query model, paginate, return collection resource
- `store()`: validate via FormRequest, create model, return resource
- `show()`: route-model binding, optional includes, return resource
- `update()`: validate via FormRequest, update model, return resource
- `destroy()`: delete model

This results in ~150 lines of near-identical code across three controllers. Changes to pagination, filtering, or resource wrapping require edits in three places.

## Decision
Create `App\Http\Controllers\Api\V1\BaseApiController` with generic CRUD methods. Concrete controllers configure behavior via properties:
- `$modelClass` — Eloquent model class
- `$filterClass` — Filter class for query transformation
- `$resourceClass` — Single resource class
- `$collectionClass` — Collection resource class
- `$storeRequestClass` / `$updateRequestClass` — FormRequest classes

Methods are overridable for customization (e.g., `index()` eager-loads relationships conditionally).

## Consequences
**Positive**:
- Eliminates ~150 lines of duplication
- Single place to change pagination, filtering, response format
- New resources get CRUD for free by extending and configuring 5 properties

**Negative**:
- Adds indirection; new developers must understand the base class
- Over-customization can lead to "god base class" — mitigate by keeping base methods small and focused

**Neutral**:
- No behavior change; existing endpoints work identically
- Tests continue to pass without modification