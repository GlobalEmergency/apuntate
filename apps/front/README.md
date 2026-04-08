# Apuntate — Frontend

Angular 15 SPA for the Apuntate platform.

> For project overview, architecture, and contributing guidelines see the [root README](../../README.md).

## Stack

- Angular 15.2 with Angular Material
- TypeScript 4.8
- SCSS styling
- FullCalendar for calendar views
- JWT-based authentication

## Development

All commands are run from the **monorepo root** via Make:

```bash
make front-install   # Install npm dependencies
make front-start     # Dev server on http://localhost:4200
make front-build     # Production build
make front-lint      # ESLint check
make front-tests     # Unit tests (Karma + Jasmine)
```

## Environment

Environment variables are injected at build time via `setenv.ts`:

| Variable  | Description               | Default                       |
|-----------|---------------------------|-------------------------------|
| `APP_ENV` | Environment name          | `dev`                         |
| `API_URL` | Backend API base URL      | `http://localhost:8080/api`   |

## Deployment

Production builds are deployed to AWS S3 + CloudFront via GitHub Actions. See `.github/workflows/front-deploy.yaml` at the monorepo root.
