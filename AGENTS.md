# Agents / Contributors Guide

Technical conventions and instructions for working with this monorepo.

## Monorepo Structure

```
apuntate/
├── apps/
│   ├── back/       # Symfony 7 REST API (PHP 8.2+)
│   └── front/      # Angular 15 SPA (TypeScript)
├── docs/           # Architecture and testing documentation
├── docker-compose.yaml
├── docker-compose.dev.yaml
└── Makefile        # Root orchestrator
```

- Backend and frontend are independent applications sharing a single repository.
- Each app has its own dependency management (`composer.json` / `package.json`).
- Shared infrastructure (Docker, CI/CD, Makefile) lives at the root.

## Running the Project

```bash
# Full setup: build containers, install deps, create DB, run tests
make install

# Start services (API on :8081, PostgreSQL on :5432)
make up

# Frontend dev server (requires Node 20+)
make front-install
make front-start    # http://localhost:4200
```

## Architecture Principles

### Domain-Driven Design (DDD)

The backend follows a layered DDD architecture:

```
Domain (Entities, Value Objects, Repository Interfaces)
    ↑
Application (Use Cases / Services)
    ↑
Infrastructure (Controllers, Doctrine Repositories, External APIs)
```

- **Domain layer** contains pure business logic. No framework dependencies.
- **Application layer** orchestrates use cases. Depends only on domain interfaces.
- **Infrastructure layer** implements interfaces and handles I/O (database, HTTP, filesystem).

### SOLID Principles

- **S** — One class, one responsibility. Use cases are single-purpose.
- **O** — Extend behavior through new classes, not modifying existing ones.
- **L** — Subtypes must be substitutable for their base types.
- **I** — Prefer small, focused interfaces over large ones.
- **D** — Depend on abstractions (interfaces), not concretions. Inject repository interfaces, not Doctrine repositories directly.

### Clean Code

- Descriptive naming. No abbreviations except universally understood ones (ID, URL, API).
- Small functions with a single level of abstraction.
- No comments that explain *what* — the code should be self-explanatory. Comments only for *why*.
- Early returns over nested conditionals.

### Outside-In Development Strategy

Start from the outside (acceptance/integration) and drive inward:

1. **Write an acceptance test** describing the desired behavior from the user's perspective.
2. **Implement the controller/entry point** that receives the request.
3. **Create the use case** (application service) that orchestrates the domain.
4. **Build domain objects** (entities, value objects) as needed.
5. **Implement infrastructure** (repositories, external services) last.

This ensures every line of code exists because a test required it.

### Frontend — Atomic Design System

Components follow the Atomic Design methodology:

| Level     | Description                          | Example                        |
|-----------|--------------------------------------|--------------------------------|
| Atoms     | Basic UI elements                    | Button, Input, Icon, Label     |
| Molecules | Simple groups of atoms               | SearchBar, FormField, NavItem  |
| Organisms | Complex UI sections                  | Header, Sidebar, ServiceTable  |
| Templates | Page-level layouts                   | FullLayout, BlankLayout        |
| Pages     | Concrete instances with real data    | Dashboard, Login, ServiceDetail|

Place components accordingly:
- `src/app/components/atoms/`
- `src/app/components/molecules/`
- `src/app/components/organisms/`
- `src/app/layouts/` (templates)
- `src/app/pages/` (pages)

## Testing Strategy

### Unit Tests on Use Cases

Tests target the **Application layer** (use cases/services). Mock **only I/O boundaries**:

- Mock: Repository interfaces, HTTP clients, filesystem, external APIs.
- Do NOT mock: Domain entities, value objects, domain services, or any business logic.

This approach tests the maximum amount of real code while isolating external dependencies.

```php
// Good: mock the repository interface, test real domain logic
$repository = $this->createMock(ServiceRepositoryInterface::class);
$repository->method('find')->willReturn($service);
$useCase = new CreateGaps($repository);
$result = $useCase->execute($input);

// Bad: mocking domain entities or internal services
```

### Backend Testing

- Framework: PHPUnit 11
- Run: `make back-tests` or `make back-tests suite=unit`
- Test database: separate PostgreSQL database (`apuntate_test`), real migrations applied
- Location: `apps/back/tests/`

### Frontend Testing

- Framework: Karma + Jasmine
- Run: `make front-tests`
- Location: `*.spec.ts` files alongside their source files

See [docs/testing.md](docs/testing.md) for the complete testing guide.

## Backend Conventions

### Namespace

All backend classes live under `GlobalEmergency\Apuntate\`.

### Directory Layout

```
apps/back/src/
├── Application/       # Use cases and application services
│   └── Services/
├── Entity/            # Doctrine entities (domain layer)
│   └── Traits/
├── Repository/        # Doctrine repository implementations
├── Api/
│   └── Infrastructure/
│       └── Rest/      # REST API controllers
├── Controller/
│   ├── Admin/         # EasyAdmin CRUD controllers
│   └── Manager/       # Manager-role controllers
├── Security/          # Authentication
├── Services/          # Infrastructure services
├── Shared/            # Shared infrastructure (custom types, fixtures)
└── Kernel.php
```

### Code Style

- Standard: `@Symfony` ruleset via php-cs-fixer
- Validate: `make back-phpcs-validate`
- Auto-fix: `make back-phpcs-fixer`
- Static analysis: `make back-phpstan` (level 2)

### API Routes

All API endpoints are prefixed with `/api/`:
- `POST /api/auth/login` — JWT authentication
- `POST /api/auth/refresh` — Token refresh
- `GET/POST /api/services` — Service management
- `GET/POST /api/profiler` — User profile
- `GET/POST /api/alerts` — Alerts

## Frontend Conventions

### Directory Layout

```
apps/front/src/
├── app/
│   ├── components/    # Reusable UI components (atoms, molecules, organisms)
│   ├── layouts/       # Page layouts (templates in Atomic Design)
│   ├── pages/         # Route-level pages
│   ├── model/         # TypeScript interfaces/models
│   └── material.module.ts
├── services/          # Angular services (API, auth, nav)
├── interceptor/       # HTTP interceptors (JWT)
├── guards/            # Route guards
├── domain/            # Domain interfaces
├── environments/      # Environment configs (generated by setenv.ts)
└── assets/            # Static assets, SCSS
```

### UX/UI Principles

- **User feedback is mandatory**: every action must provide visible feedback (loading, success, error).
- No silent failures — always show error messages to the user (snackbar, inline, or dialog).
- Handle all HTTP error codes with user-facing messages (e.g., 409 = "email already in use").
- Loading indicators (spinner or disabled button) during async operations.
- Success confirmation after completed actions.

### Responsive Design

- **Mobile first**: design for mobile viewport first, then scale up.
- Use **CSS Grid and Flexbox** for layout — never media queries for layout purposes.
- Images must be responsive (`max-width: 100%; height: auto`).
- Touch-friendly targets (min 44px).

### Code Style

- ESLint for TypeScript linting
- SCSS for styles (no inline styles)
- Lazy-loaded feature modules
- Reactive patterns with RxJS

### Environment Configuration

Environment variables are injected at build time via `setenv.ts`:
- `APP_ENV` — Environment name (dev/staging/prod)
- `API_URL` — Backend API base URL

## CI/CD

GitHub Actions workflows with path filters (only run when relevant files change):

| Workflow                 | Trigger                          | Scope         |
|--------------------------|----------------------------------|---------------|
| `back-tests.yml`         | Push to main, PRs                | `apps/back/**`|
| `back-codacy.yml`        | Push to main, PRs, weekly        | `apps/back/**`|
| `front-lint.yml`         | PRs                              | `apps/front/**`|
| `front-deploy.yaml`      | Reusable (staging/prod)          | `apps/front/**`|
| `front-autodeploy.yaml`  | Push to main                     | `apps/front/**`|

## Commit Conventions

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <description>

[optional body]
```

Types: `feat`, `fix`, `refactor`, `test`, `docs`, `chore`, `ci`, `style`, `perf`
Scopes: `back`, `front`, `infra`, `docs`

Examples:
```
feat(back): add service cancellation use case
fix(front): correct JWT refresh race condition
ci(infra): add path filters to backend test workflow
docs: update architecture decision records
```

## Important Rules

- Never commit secrets, credentials, or `.env.local` files.
- Do not add AI-generated attribution to commits or code.
- Always run tests before pushing: `make back-tests && make front-tests`.
- Keep domain logic free of framework dependencies.
- Mock only I/O at the outermost boundary in tests.
