# ADR 003: Repository Pattern for Data Access (Clean Architecture)

## Status
Accepted

## Context
Services and controllers currently depend directly on Eloquent models (`Customer::query()`, `Invoice::create()`). This couples business logic to Laravel's ORM and MySQL. Consequences:
- Tests cannot swap to SQLite without model changes (though Laravel supports it, the coupling remains)
- Future persistence changes (e.g., read replicas, caching layer, different DB) require changes throughout
- No clear boundary between domain and infrastructure

## Decision
Define repository interfaces in `App\Contracts\`:
- `CustomerRepositoryInterface`
- `InvoiceRepositoryInterface`
- `InvoiceItemsRepositoryInterface`

Provide Eloquent implementations in `App\Repositories\Eloquent\`:
- `EloquentCustomerRepository`
- `EloquentInvoiceRepository`
- `EloquentInvoiceItemsRepository`

Bind interfaces to implementations in `AppServiceProvider::register()`:
```php
$this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
// ...
```

Services inject interfaces via constructor. Tests can bind fakes/in-memory implementations.

Repository methods are narrow: `find($id)`, `paginate(array $filters)`, `create(array $data)`, `update($id, array $data)`, `delete($id)`. No query builders leak out.

## Consequences
**Positive**:
- Clean separation: domain (services) depends on abstractions, not Laravel
- Testability: swap repositories for fakes/in-memory in unit tests
- Extensibility: add caching, read replicas, or switch ORM without touching services

**Negative**:
- More interfaces/classes; boilerplate for simple CRUD
- Risk of "repository per model" anemic pattern — mitigate by keeping methods focused on aggregate roots

**Neutral**:
- No API behavior change
- Existing tests pass; new unit tests for services can use fake repositories