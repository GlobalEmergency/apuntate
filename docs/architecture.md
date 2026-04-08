# Architecture

This document describes the architectural decisions and patterns used in the Apuntate monorepo.

## Overview

Apuntate is a service management platform for emergency organizations. It consists of two applications:

- **Backend** (`apps/back/`): Symfony 7 REST API providing data and authentication.
- **Frontend** (`apps/front/`): Angular 15 SPA consuming the API.

Both applications follow Domain-Driven Design principles, SOLID, and Clean Code practices.

## Domain-Driven Design

### Layered Architecture

The backend follows a strict layered architecture where dependencies always point inward:

```
┌─────────────────────────────────────────────────┐
│                Infrastructure                    │
│   Controllers, Doctrine Repos, External APIs     │
│                                                  │
│   ┌─────────────────────────────────────────┐   │
│   │             Application                  │   │
│   │         Use Cases / Services             │   │
│   │                                          │   │
│   │   ┌─────────────────────────────────┐   │   │
│   │   │           Domain                 │   │   │
│   │   │  Entities, Value Objects,        │   │   │
│   │   │  Repository Interfaces           │   │   │
│   │   └─────────────────────────────────┘   │   │
│   └─────────────────────────────────────────┘   │
└─────────────────────────────────────────────────┘
```

### Domain Layer

The innermost layer contains the core business logic:

- **Entities**: `Service`, `User`, `Unit`, `Component`, `Gap`, `Requirement`, `Speciality`
- **Value Objects**: `ServiceStatus` (enum-like status)
- **Repository Interfaces**: contracts that the infrastructure must fulfill
- **Traits**: `Timestampable` for `created_at`/`updated_at`

Rules:
- No Symfony or Doctrine imports in domain logic.
- Entities define behavior, not just data.
- Repository interfaces belong to the domain, implementations to infrastructure.

### Application Layer

Orchestrates domain objects to fulfill use cases:

- Located in `src/Application/Services/`
- Each use case is a single class with an `execute` method.
- Depends only on domain interfaces (repository interfaces, entities).
- No direct database queries or HTTP calls.

Example: `CreateGaps` — takes a service and generates gaps based on unit component requirements.

### Infrastructure Layer

Implements domain interfaces and handles all I/O:

- **REST Controllers**: `src/Api/Infrastructure/Rest/` — handle HTTP requests, delegate to use cases.
- **Doctrine Repositories**: `src/Repository/` — implement repository interfaces using Doctrine ORM.
- **Admin Controllers**: `src/Controller/Admin/` — EasyAdmin CRUD.
- **Custom Types**: `src/Shared/Infrastructure/` — Doctrine type mappings.

## SOLID in Practice

### Single Responsibility

Each use case class handles exactly one operation. Controllers only translate HTTP to use case calls and back. Repositories only handle data persistence.

### Open/Closed

New features are added by creating new use case classes, not by modifying existing ones. New API endpoints get new controller methods or classes.

### Liskov Substitution

Repository implementations are interchangeable. Any class implementing a repository interface can replace any other without breaking calling code.

### Interface Segregation

Repository interfaces expose only the methods needed by their consumers. A use case that only reads services depends on a read-only interface, not a full CRUD interface.

### Dependency Inversion

Use cases depend on repository interfaces (abstractions), never on Doctrine repositories (concretions). Symfony's autowiring resolves the binding at runtime:

```php
// Use case depends on interface
class CreateGaps {
    public function __construct(
        private GapRepositoryInterface $gapRepository,
        private UnitComponentRepositoryInterface $unitComponentRepository,
    ) {}
}

// Symfony wires the Doctrine implementation automatically
```

## Clean Code Guidelines

- **Naming**: classes are nouns (`ServiceRepository`), methods are verbs (`findNextServices`), booleans start with `is`/`has`/`can`.
- **Functions**: max 20 lines, single level of abstraction, max 3 parameters.
- **Early returns**: guard clauses at the top, happy path at the bottom.
- **No dead code**: unused methods, commented-out code, or unreachable branches are removed.
- **No magic values**: constants or enums for all non-obvious values.

## Frontend Architecture

### Atomic Design System

The frontend organizes components using Atomic Design:

```
┌──────────────────────────────────────┐
│              Pages                    │
│  Dashboard, Login, ServiceDetail      │
│                                       │
│  ┌──────────────────────────────┐    │
│  │          Templates            │    │
│  │  FullLayout, BlankLayout      │    │
│  │                               │    │
│  │  ┌──────────────────────┐    │    │
│  │  │     Organisms         │    │    │
│  │  │  Header, Sidebar,     │    │    │
│  │  │  ServiceTable          │    │    │
│  │  │                        │    │    │
│  │  │  ┌──────────────┐    │    │    │
│  │  │  │  Molecules    │    │    │    │
│  │  │  │  NavItem,     │    │    │    │
│  │  │  │  FormField    │    │    │    │
│  │  │  │               │    │    │    │
│  │  │  │  ┌────────┐  │    │    │    │
│  │  │  │  │ Atoms   │  │    │    │    │
│  │  │  │  │ Button, │  │    │    │    │
│  │  │  │  │ Input   │  │    │    │    │
│  │  │  │  └────────┘  │    │    │    │
│  │  │  └──────────────┘    │    │    │
│  │  └──────────────────────┘    │    │
│  └──────────────────────────────┘    │
└──────────────────────────────────────┘
```

**Directory mapping:**

| Atomic Level | Directory                          |
|--------------|-----------------------------------|
| Atoms        | `src/app/components/atoms/`       |
| Molecules    | `src/app/components/molecules/`   |
| Organisms    | `src/app/components/organisms/`   |
| Templates    | `src/app/layouts/`                |
| Pages        | `src/app/pages/`                  |

### Module Organization

- Feature modules are lazy-loaded for code splitting.
- Each feature module has its own routing configuration.
- Shared components live in the `components/` directory.
- Services are `providedIn: 'root'` for singleton behavior.

### State Management

- No external state library (NgRx/Akita). State is managed through:
  - `BehaviorSubject` observables in services.
  - Component-local state for UI concerns.
  - API calls as the source of truth.

### Service Layer

- `ApiService` — all backend HTTP calls.
- `AuthenticationService` — JWT token lifecycle, login/logout, role checking.
- `AlertService` — notification management.
- `NavService` — sidebar navigation state.

## Outside-In Development Strategy

When building a new feature, work from the outside in:

```
1. Acceptance Test     →  "When I POST /api/services, a service is created"
2. Controller          →  Receive request, call use case, return response
3. Use Case            →  Orchestrate domain logic
4. Domain Objects      →  Entities, value objects, business rules
5. Infrastructure      →  Repository implementation, database queries
```

Each step is driven by a failing test from the previous step. This ensures:

- Every line of production code exists because a test required it.
- The public API is designed from the consumer's perspective.
- Internal implementation details are never over-specified.
- Refactoring is safe because the test suite covers behavior, not structure.

## API Design

### Authentication

- JWT-based stateless authentication for all `/api/*` endpoints.
- Login: `POST /api/auth/login` returns access + refresh tokens.
- Refresh: `POST /api/auth/refresh` exchanges a refresh token.
- Public endpoints: only `/api/auth/*` routes.
- Role-based access: `ROLE_ADMIN` for admin operations.

### REST Conventions

- Resource-based URLs: `/api/services`, `/api/alerts`.
- Standard HTTP methods: GET (read), POST (create), PUT (update), DELETE (remove).
- JSON request and response bodies.
- HTTP status codes: 200 (OK), 201 (Created), 400 (Bad Request), 401 (Unauthorized), 404 (Not Found).

## Infrastructure

### Docker Services

| Service  | Image          | Port  | Purpose           |
|----------|---------------|-------|-------------------|
| php-fpm  | apuntate-php-fpm | 8081  | Symfony API server |
| pgsql    | apuntate-pgsql   | 5432  | PostgreSQL 16      |

### Deployment

- **Backend**: Docker container (php-fpm + nginx).
- **Frontend**: Static build deployed to AWS S3 + CloudFront CDN.
- **CI/CD**: GitHub Actions with monorepo path filters.
- **Infrastructure as Code**: Terraform for AWS resources (S3, CloudFront, IAM).
