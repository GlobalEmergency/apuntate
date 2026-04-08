# Contributing to Apuntate

Thank you for your interest in contributing to Apuntate. This guide will help you get started.

## Prerequisites

- Docker and Docker Compose
- Node.js 20+ (for frontend development)
- Git

## Getting Started

1. Fork and clone the repository:
   ```bash
   git clone git@github.com:<your-username>/apuntate.git
   cd apuntate
   ```

2. Run the full setup:
   ```bash
   make install
   ```

3. Start the development environment:
   ```bash
   make up                # Backend API on :8081, PostgreSQL on :5432
   make front-install     # Install frontend dependencies
   make front-start       # Frontend on :4200
   ```

4. Verify everything works:
   ```bash
   make back-tests
   make front-tests
   ```

## Development Workflow

### Branch Naming

```
<type>/<short-description>
```

Examples:
- `feat/service-cancellation`
- `fix/jwt-refresh-race-condition`
- `refactor/extract-calendar-use-case`

### Commit Messages

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>
```

- **Types:** `feat`, `fix`, `refactor`, `test`, `docs`, `chore`, `ci`, `style`, `perf`
- **Scopes:** `back`, `front`, `infra`, `docs`

### Pull Request Process

1. Create a feature branch from `main`.
2. Make your changes following the architecture and code style guidelines below.
3. Ensure all tests pass: `make back-tests && make front-tests`.
4. Ensure code style is clean: `make back-phpcs-validate && make front-lint`.
5. Open a PR against `main` with a clear description of the changes.
6. Address review feedback.

## Architecture Guidelines

This project follows strict architectural principles. Please read [docs/architecture.md](docs/architecture.md) for the full details.

### DDD — Domain-Driven Design

- **Domain layer** (entities, value objects): pure business logic, no framework dependencies.
- **Application layer** (use cases): orchestrates domain objects, depends only on interfaces.
- **Infrastructure layer** (controllers, repositories): handles I/O, implements domain interfaces.

Dependencies flow inward: Infrastructure -> Application -> Domain.

### SOLID Principles

- Single Responsibility: one class, one reason to change.
- Open/Closed: extend through new classes, not modification.
- Liskov Substitution: subtypes must honor base type contracts.
- Interface Segregation: small, focused interfaces.
- Dependency Inversion: depend on abstractions, inject interfaces.

### Clean Code

- Descriptive names, no abbreviations.
- Small functions, single level of abstraction.
- Early returns over nested conditionals.
- Comments only explain *why*, never *what*.

### Outside-In Development

When adding a new feature:

1. Write an acceptance test describing the expected behavior.
2. Implement the entry point (controller/route).
3. Create the use case (application service).
4. Build domain objects as needed.
5. Implement infrastructure (repository, external service) last.

### Frontend — Atomic Design

Organize components following Atomic Design:

- **Atoms:** `src/app/components/atoms/` — basic elements (buttons, inputs, icons)
- **Molecules:** `src/app/components/molecules/` — groups of atoms (form fields, search bars)
- **Organisms:** `src/app/components/organisms/` — complex sections (header, sidebar, tables)
- **Templates:** `src/app/layouts/` — page layouts
- **Pages:** `src/app/pages/` — route-level components with real data

## Code Style

### Backend (PHP)

- Ruleset: `@Symfony` via php-cs-fixer
- Validate: `make back-phpcs-validate`
- Auto-fix: `make back-phpcs-fixer`
- Static analysis: `make back-phpstan`

### Frontend (TypeScript)

- Linter: ESLint
- Validate: `make front-lint`
- Styles: SCSS only, no inline styles
- Modules: lazy-loaded feature modules

## Testing

See [docs/testing.md](docs/testing.md) for the full testing guide.

### Key Principles

- **Test use cases** (application layer), not implementation details.
- **Mock only I/O boundaries**: repository interfaces, HTTP clients, filesystem.
- **Never mock** domain entities, value objects, or domain services.
- **Outside-in**: start from the outermost layer and drive tests inward.

### Running Tests

```bash
# Backend
make back-tests                    # All tests
make back-tests suite=unit         # Specific suite

# Frontend
make front-tests
```

## Reporting Issues

- Use GitHub Issues for bug reports and feature requests.
- Include steps to reproduce for bugs.
- Reference related code or tests when possible.

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By participating, you agree to uphold this code.

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).
