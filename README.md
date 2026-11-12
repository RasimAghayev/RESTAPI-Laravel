# RESTAPI-Laravel

![Views](https://hits.sh/github.com/RasimAghayev/RESTAPI-Laravel.svg?label=views&color=0e75b6)

A Laravel-based REST API for customer and invoice management, containerized with Docker and validated through a CI/CD pipeline.

## Features

- **Laravel 13** on **PHP 8.5** with **PHPUnit 13**
- **RESTful API** v1: Customers, Invoices, Invoice Items
- **Health endpoints**: `/api/health/live` (liveness) and `/api/health/ready` (readiness with DB check)
- **Sanctum authentication** for protected routes
- **Multi-stage Docker build** (base → deps → build → test → runtime)
- **Docker Compose** stack: PHP-FPM, Nginx, MySQL
- **Makefile** for common operations
- **GitHub Actions CI**: secret scan, build/test/lint, best-effort audit/scan
- **Dependabot** weekly updates for Composer, npm, Docker, GitHub Actions

## Quick Start

### Prerequisites

- Docker Desktop / Docker Engine + Buildx
- Make (GNU Make)

### Development

```bash
# Build and run tests (verification gate)
make verify

# Start the full stack (after verify passes)
make up

# Stop the stack
make down

# View logs
make logs

# Open shell in app container
make shell

# Run migrations
make migrate

# Seed database
make seed

# Lint with Pint (from test image)
make lint

# Security scan (best effort)
make security

# Clean up containers and images
make clean
```

### Environment

Copy `customer-invoice-api/.env.example` to `customer-invoice-api/.env` and adjust as needed. The compose stack uses `.env.example` for the app service.

## API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/` | Welcome view | No |
| GET | `/api/health/live` | Liveness probe | No |
| GET | `/api/health/ready` | Readiness probe (DB check) | No |
| GET | `/api/user` | Authenticated user | Sanctum |
| GET | `/api/v1/customers` | List customers | No |
| POST | `/api/v1/customers` | Create customer | No |
| GET | `/api/v1/customers/{id}` | Show customer | No |
| PUT/PATCH | `/api/v1/customers/{id}` | Update customer | No |
| DELETE | `/api/v1/customers/{id}` | Delete customer | No |
| GET | `/api/v1/invoices` | List invoices | No |
| POST | `/api/v1/invoices` | Create invoice | No |
| GET | `/api/v1/invoices/{id}` | Show invoice | No |
| GET | `/api/v1/invoiceitems` | List invoice items | No |
| POST | `/api/v1/invoiceitems` | Create invoice item | No |
| GET | `/api/v1/invoiceitems/{id}` | Show invoice item | No |

### Health Endpoints

- **`GET /api/health/live`** — Returns `200 OK` with `{"status":"ok"}` for liveness checks.
- **`GET /api/health/ready`** — Returns `200 OK` with `{"status":"ok","checks":{"database":"ok"}}` if DB is reachable, `503` with `{"status":"degraded","checks":{"database":"unavailable"}}` otherwise.

## Docker

### Multi-stage Dockerfile

```dockerfile
# base     — php:8.5-fpm-alpine + extensions (intl, zip, pcntl, pdo_sqlite)
# deps     — composer install (all deps incl. dev)
# build    — node:22-alpine, npm install/build (Vite)
# test     — runs phpunit in sqlite memory DB
# runtime  — slim php-fpm, no dev deps, non-root user, HEALTHCHECK
```

Images are digest-pinned for reproducibility.

### Docker Compose

```yaml
services:
  app:     # PHP-FPM (runtime target)
  web:     # Nginx (port 20110 -> 80)
  db:      # MySQL 8.4 (port 20111 -> 3306, healthcheck)
```

Ports reserved in `projects/info/state/ports.md` (block `20110-20119`).

## Makefile Targets

| Target | Description |
|--------|-------------|
| `help` | Show available targets |
| `build` | Build runtime image |
| `test` | Build test stage + run PHPUnit |
| `verify` | Build + test (verification gate) |
| `up` | Start stack (depends on `verify`) |
| `down` | Stop stack (keeps DB volume) |
| `run` | Start stack in foreground |
| `shell` | Shell in running app container |
| `logs` | Follow container logs |
| `migrate` | Run migrations |
| `seed` | Seed database |
| `lint` | Run Pint (from test image, depends on `verify`) |
| `security` | Scan runtime image (best effort) |
| `clean` | Remove containers/images (DB volume kept unless `CONFIRM=1`) |

## CI/CD

**GitHub Actions** (`.github/workflows/ci.yml`):

- Triggers: push/PR to `master`, `test`
- Permissions: `contents: read`
- Concurrency: cancels in-progress PR runs
- Steps:
  1. Checkout (SHA-pinned `actions/checkout@v4`)
  2. Secret scan (SHA-pinned `gitleaks/gitleaks-action@v2`)
  3. Docker Buildx setup (SHA-pinned)
  4. `make test` (Docker test stage)
  5. `make build` (runtime image)
  6. Pint lint via test image
  7. `composer audit` (best effort, `continue-on-error`)
  8. Image security scan via `make security` (best effort, `continue-on-error`)

## Testing

```bash
# Run tests in Docker (same as CI)
make test

# Or run verify (build + test)
make verify
```

Test suite (PHPUnit 13.4.6, PHP 8.5.12):
- 5 tests, 5 assertions
- Feature: API characterization (customers, invoices, invoiceitems index)
- Unit: Example test

## Architecture

- **Clean Architecture** layers: Controllers → Services → Repositories (interfaces + Eloquent implementations)
- **SOLID**: SRP (service classes), DIP (repository interfaces)
- **ADR 001**: DRY trait deferred (PHP 8.5 crash), inline CRUD methods used
- **InvoiceItems routes** restricted to `index`, `store`, `show` (no update/destroy)

## Dependencies

Managed via `customer-invoice-api/composer.json` and `composer.lock`. Key packages:
- `laravel/framework` ^13
- `laravel/sanctum` ^4
- `phpunit/phpunit` ^13
- `laravel/pint` ^1

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## License

MIT License — see [LICENSE](LICENSE).