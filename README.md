# Apuntate

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

Service management platform for emergency organizations. Allows participants to view available services, check resource needs, and quickly register for positions within their emergency grouping.

## Tech Stack

| Layer    | Technology                          |
|----------|-------------------------------------|
| Backend  | PHP 8.2+, Symfony 7, Doctrine ORM   |
| Frontend | Angular 15, Angular Material, SCSS  |
| Database | PostgreSQL 16                       |
| Auth     | JWT (lexik/jwt-authentication-bundle)|
| Infra    | Docker, GitHub Actions, Terraform   |

## Project Structure

```
apuntate/
├── apps/
│   ├── back/          # Symfony REST API
│   └── front/         # Angular SPA
├── docs/
│   ├── architecture.md
│   └── testing.md
├── docker-compose.yaml
├── docker-compose.dev.yaml
├── Makefile
├── AGENTS.md
├── CONTRIBUTING.md
└── CODE_OF_CONDUCT.md
```

## Quick Start

**Prerequisites:** Docker and Docker Compose installed.

```bash
# Build, install dependencies, create database and run tests
make install

# Start all services
make up
```

- Backend API: http://localhost:8081
- Frontend dev server: http://localhost:4200 (requires `make front-install && make front-start`)

## Development Commands

### Full Stack

| Command         | Description                          |
|-----------------|--------------------------------------|
| `make up`       | Start all Docker services            |
| `make down`     | Stop all Docker services             |
| `make install`  | Full setup: build, deps, db, tests   |

### Backend

| Command                    | Description                        |
|----------------------------|------------------------------------|
| `make back-sh`             | Shell into PHP container           |
| `make back-composer`       | Install PHP dependencies           |
| `make back-tests`          | Run PHPUnit tests                  |
| `make back-tests suite=X`  | Run specific test suite            |
| `make back-db-update`      | Recreate database and migrate      |
| `make back-migrate`        | Run pending migrations             |
| `make back-phpcs-fixer`    | Fix code style                     |
| `make back-phpcs-validate` | Validate code style (dry-run)      |
| `make back-phpstan`        | Run static analysis                |

### Frontend

| Command              | Description                    |
|----------------------|--------------------------------|
| `make front-install` | Install npm dependencies       |
| `make front-start`   | Start dev server (port 4200)   |
| `make front-build`   | Production build               |
| `make front-lint`    | Run ESLint                     |
| `make front-tests`   | Run unit tests                 |

## Architecture

This project follows **Domain-Driven Design**, **SOLID principles**, **Clean Code** practices, and an **Outside-in development strategy**. The frontend uses an **Atomic Design** system for component organization.

See [docs/architecture.md](docs/architecture.md) for detailed architecture documentation and [docs/testing.md](docs/testing.md) for the testing strategy.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines on how to contribute to this project.

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.

Copyright 2021 [GlobalEmergency.online](https://globalemergency.online)
