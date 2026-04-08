# Apuntate — Backend

Symfony 7 REST API for the Apuntate platform.

> For project overview, architecture, and contributing guidelines see the [root README](../../README.md).

## Stack

- PHP 8.2+, Symfony 7.0
- Doctrine ORM 3 with PostgreSQL 16
- JWT authentication (lexik/jwt-authentication-bundle)
- EasyAdmin for admin panel

## Development

All commands are run from the **monorepo root** via Make:

```bash
make up                  # Start Docker services
make back-composer       # Install dependencies
make back-tests          # Run tests
make back-phpcs-fixer    # Fix code style
make back-phpstan        # Static analysis
make back-sh             # Shell into container
```

## JWT Setup

Generate keypairs for JWT authentication:

```bash
make back-sh
php bin/console lexik:jwt:generate-keypair
```

## Database

```bash
make back-db-update          # Recreate and migrate (dev)
make back-db-update env=test # Recreate and migrate (test)
make back-migrate            # Run pending migrations only
make back-migrations-diff    # Generate migration from entity changes
```

## API Endpoints

| Method | Endpoint              | Auth     | Description          |
|--------|----------------------|----------|----------------------|
| POST   | `/api/auth/login`    | Public   | JWT login            |
| POST   | `/api/auth/refresh`  | Public   | Refresh token        |
| GET    | `/api/services`      | JWT      | List services        |
| POST   | `/api/services`      | JWT      | Create service       |
| GET    | `/api/services/{id}` | JWT      | Service details      |
| GET    | `/api/profiler`      | JWT      | User profile         |
| GET    | `/api/alerts`        | JWT      | List alerts          |
