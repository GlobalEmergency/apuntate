# Testing Strategy

This document describes the testing approach for the Apuntate monorepo.

## Philosophy

Tests verify **behavior**, not implementation. We follow an **Outside-in** strategy and mock **only I/O** at the outermost boundary.

### Core Rules

1. **Test use cases**, not private methods or internal structure.
2. **Mock only I/O**: repository interfaces, HTTP clients, filesystem, external APIs.
3. **Never mock**: domain entities, value objects, domain services, or any business logic.
4. **Outside-in**: each feature starts with an acceptance test and drives inward.
5. **One assertion per concept**: each test verifies a single behavior.

## Test Pyramid

```
        ╱  ╲
       ╱ E2E ╲          Few — verify full user journeys
      ╱────────╲
     ╱Integration╲      Some — verify component collaboration
    ╱──────────────╲
   ╱    Unit Tests   ╲   Many — verify use cases and domain logic
  ╱────────────────────╲
```

- **Unit tests** form the base: fast, isolated, test use cases and domain logic.
- **Integration tests** verify that infrastructure implementations work (database queries, API responses).
- **E2E tests** verify critical user flows end-to-end (sparingly).

## Outside-In Workflow

When adding a new feature:

### Step 1: Acceptance Test

Write a test that describes the feature from the user's perspective:

```php
// tests/Acceptance/CreateServiceTest.php
public function test_admin_can_create_a_service(): void
{
    $this->authenticateAsAdmin();

    $response = $this->post('/api/services', [
        'name' => 'Night Watch',
        'date' => '2024-03-15',
    ]);

    $this->assertResponseStatusCodeSame(201);
    $this->assertJsonContains(['name' => 'Night Watch']);
}
```

This test will fail because nothing exists yet. Now implement just enough to make it pass.

### Step 2: Unit Test for Use Case

Before implementing the use case, write a unit test:

```php
// tests/Unit/Application/CreateServiceTest.php
public function test_creates_service_with_valid_data(): void
{
    // Arrange: mock only the repository (I/O boundary)
    $repository = $this->createMock(ServiceRepositoryInterface::class);
    $repository->expects($this->once())
        ->method('save')
        ->with($this->isInstanceOf(Service::class));

    $useCase = new CreateService($repository);

    // Act: execute with real domain objects
    $result = $useCase->execute(
        name: 'Night Watch',
        date: new DateTimeImmutable('2024-03-15'),
    );

    // Assert: verify behavior
    $this->assertEquals('Night Watch', $result->getName());
}
```

### Step 3: Domain Tests (if needed)

If the domain object has complex business rules, test them directly:

```php
// tests/Unit/Entity/ServiceTest.php
public function test_service_cannot_have_past_date(): void
{
    $this->expectException(InvalidArgumentException::class);

    new Service(
        name: 'Night Watch',
        date: new DateTimeImmutable('2020-01-01'),
    );
}
```

Domain tests use **no mocks at all** — pure logic, pure assertions.

### Step 4: Integration Test (if needed)

Verify the infrastructure implementation works with a real database:

```php
// tests/Integration/Repository/ServiceRepositoryTest.php
public function test_persists_and_retrieves_service(): void
{
    $service = new Service(name: 'Night Watch', date: new DateTimeImmutable('2024-03-15'));

    $this->repository->save($service);

    $found = $this->repository->find($service->getId());
    $this->assertEquals('Night Watch', $found->getName());
}
```

## What to Mock (and What Not To)

### Mock These (I/O Boundaries)

| Dependency              | Why it's I/O                      |
|------------------------|-----------------------------------|
| Repository interfaces  | Database access                   |
| HTTP clients           | External API calls                |
| Filesystem             | File read/write operations        |
| Email/notification     | External service communication    |
| Clock/time providers   | Non-deterministic system resource |

### Never Mock These

| Dependency              | Why not                            |
|------------------------|-------------------------------------|
| Domain entities        | They ARE the logic being tested     |
| Value objects          | Pure data, no side effects          |
| Domain services        | Business rules must be exercised    |
| DTOs / request objects | Simple data carriers                |
| Enums / constants      | Static values, no behavior to mock  |

### The Boundary Rule

Draw a clear line between your code and the outside world. Everything inside that line is tested with real objects. Everything outside is mocked:

```
┌─────────────────────────────────┐
│         Your Code                │
│                                  │
│  Controllers ──► Use Cases       │
│                    │             │
│               Domain Logic       │
│                    │             │
│           Repository Interface   │  ◄── This is the boundary
├──────────────────────────────────┤
│      I/O (mocked in tests)       │
│  Database, HTTP, Files, Email    │
└──────────────────────────────────┘
```

## Backend Testing

### Configuration

- Framework: **PHPUnit 11**
- Config: `apps/back/phpunit.xml.dist`
- Test database: `apuntate_test` (PostgreSQL, real migrations applied)

### Directory Structure

```
apps/back/tests/
├── Acceptance/        # Full HTTP request tests
├── Unit/
│   ├── Application/   # Use case tests (mock I/O)
│   └── Entity/        # Domain logic tests (no mocks)
└── Integration/
    └── Repository/    # Real database tests
```

### Running Tests

```bash
# All tests
make back-tests

# Specific suite
make back-tests suite=unit

# With coverage
make back-coverage

# Recreate test database first
make back-db-update env=test
```

### Test Database

The test database uses real PostgreSQL with real migrations. This ensures schema compatibility. Each test runs in a transaction that is rolled back after execution (via DAMA DoctrineTestBundle).

## Frontend Testing

### Configuration

- Framework: **Karma + Jasmine**
- Config: `apps/front/karma.conf.js`

### Directory Structure

Test files are co-located with their source:

```
apps/front/src/
├── app/
│   ├── pages/dashboard/
│   │   ├── dashboard.component.ts
│   │   └── dashboard.component.spec.ts    # ◄── test alongside source
│   └── ...
├── services/
│   ├── authentication.service.ts
│   └── authentication.service.spec.ts
└── ...
```

### Running Tests

```bash
make front-tests
```

### Mocking in Frontend Tests

Same rules apply. Mock only HTTP calls (via `HttpClientTestingModule`) and external services. Test component logic with real domain models:

```typescript
// Good: mock the HTTP layer
const httpMock = TestBed.inject(HttpTestingController);
const req = httpMock.expectOne('/api/services');
req.flush(mockServiceData);

// Bad: mocking a TypeScript interface that has no I/O
```

## Test Naming

Use descriptive names that read as specifications:

```php
// PHP
public function test_creates_gaps_for_each_unit_component(): void
public function test_rejects_service_with_past_date(): void
public function test_returns_401_when_token_is_expired(): void
```

```typescript
// TypeScript
it('should display service details when loaded')
it('should redirect to login when token is missing')
it('should refresh token on 401 response')
```

## CI Integration

Tests run automatically in GitHub Actions:

- **Backend**: `back-tests.yml` — runs on changes to `apps/back/**`
- **Frontend**: `front-lint.yml` — runs lint + tests on PRs to `apps/front/**`

All tests must pass before merging to `main`.
