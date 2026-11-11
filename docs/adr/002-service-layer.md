# ADR 002: Service Layer for Business Logic (SRP + DIP)

## Status
Accepted

## Context
Controllers currently contain or directly call Eloquent operations. Business logic (e.g., invoice total calculation from items, status transitions, validation rules beyond form requests) has no home. This violates:
- **Single Responsibility Principle**: Controllers handle HTTP + business logic + persistence
- **Dependency Inversion Principle**: Controllers depend on concrete Eloquent models

Testing business logic requires full HTTP stack + database. Unit tests are slow and brittle.

## Decision
Introduce a Service layer (`App\Services\*`) containing pure business logic:
- `InvoiceService`: `createFromRequest()`, `update()`, `calculateTotals()`, `transitionStatus()`
- `CustomerService`: `create()`, `update()`, `delete()`
- `InvoiceItemsService`: `create()`, `delete()` (items immutable after create)

Services receive data via DTOs (FormRequest → array/DTO) and return domain objects. They inject **repository interfaces** (ADR 003), not concrete models.

Controllers become thin: validate request → call service → return resource.

## Consequences
**Positive**:
- Business logic unit-testable without HTTP/database
- Controllers thin (~10 lines per action)
- Easy to add domain events, validation, calculations without touching controllers
- Services can be reused by jobs, commands, API v2

**Negative**:
- More classes; slight increase in boilerplate for simple CRUD
- Must discipline against "anemic services" that just delegate to repositories

**Neutral**:
- No API contract change
- Existing characterization tests pass unchanged