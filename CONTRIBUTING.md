# Contributing to RESTAPI-Laravel

Thank you for considering contributing to this project!

## Getting Started

1. Fork the repository
2. Clone your fork locally
3. Create a feature branch: `git checkout -b feature/your-feature-name`
4. Make your changes
5. Run the verification gate: `make verify`
6. Commit your changes (see Commit Messages below)
7. Push to your fork and open a Pull Request against `master`

## Development Workflow

### Prerequisites

- Docker Desktop / Docker Engine + Buildx
- Make (GNU Make)
- Git

### Local Verification

Before pushing, always run:

```bash
make verify
```

This builds the Docker images and runs the full test suite. The CI pipeline runs the same command.

### Code Style

- PHP: [Laravel Pint](https://laravel.com/docs/pint) (runs in CI via `make lint`)
- Follow PSR-12 and Laravel conventions
- Run `make lint` locally to check formatting

### Testing

- Write tests for new features
- Run `make test` to execute the PHPUnit suite
- Target: all tests passing (currently 5 tests, 5 assertions)

## Commit Messages

Use conventional commits:

```
<type>(<scope>): <short summary>

<body>

<footer>
```

Types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `ci`, `build`, `perf`

Examples:
- `feat(api): add customer search endpoint`
- `fix(docker): resolve healthcheck timeout`
- `docs(readme): update API endpoint table`
- `ci(github-actions): pin actions to SHA`

No AI attribution (no `Co-Authored-By: Claude`, `Claude-Session`, etc.).

## Pull Requests

- Base branch: `master`
- Ensure `make verify` passes locally
- CI must pass (GitHub Actions)
- Keep PRs focused and atomic
- Update documentation if behavior changes

## Security

- No secrets in code, images, or CI logs
- `.env` files are gitignored
- Report security issues privately (see SECURITY.md if present)

## Branching

- `master` — default branch, protected
- Feature branches: `feature/<short-name>`
- Fix branches: `fix/<short-name>`
- Docs branches: `docs/<short-name>`
- CI branches: `ci/<short-name>`

## Dependabot

Automated weekly updates (Monday 06:00 UTC) for:
- Composer (PHP)
- npm (frontend)
- Docker base images
- GitHub Actions

Review and merge Dependabot PRs promptly.

## Questions?

Open an issue for discussion before starting significant work.