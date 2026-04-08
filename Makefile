PROJECT_NAME := apuntate

# ==========================================
# Full stack
# ==========================================

.PHONY: check
check: back-phpcs-validate back-tests front-lint front-tests

.PHONY: fix
fix: back-phpcs-fixer

.PHONY: up
up:
	docker compose -f docker-compose.yaml -f docker-compose.dev.yaml -p $(PROJECT_NAME) up -d

.PHONY: down
down:
	docker compose -f docker-compose.yaml -f docker-compose.dev.yaml -p $(PROJECT_NAME) down

.PHONY: build
build: back-build

.PHONY: install
install: build up back-composer
	$(MAKE) back-db-update
	$(MAKE) back-db-update env=test
	$(MAKE) back-tests

# ==========================================
# Backend
# ==========================================

.PHONY: back-build
back-build:
	@docker build -t $(PROJECT_NAME)-php-fpm -f apps/back/etc/docker/php-fpm/dev/Dockerfile apps/back
	@docker build -t $(PROJECT_NAME)-pgsql -f apps/back/etc/docker/postgres/Dockerfile apps/back/etc/docker/postgres

.PHONY: back-build-prod
back-build-prod:
	@docker build -t $(PROJECT_NAME)-php-fpm -f apps/back/etc/docker/php-fpm/prod/Dockerfile apps/back

.PHONY: back-sh
back-sh:
	@docker exec -u www-data -it $(PROJECT_NAME)-php-fpm bash

.PHONY: back-composer
back-composer:
	@docker exec -u www-data --tty $(PROJECT_NAME)-php-fpm composer install --no-interaction

ifdef env
ENV=--env=$(env)
endif

.PHONY: back-db-update
back-db-update:
	@docker exec -u www-data --tty $(PROJECT_NAME)-php-fpm php ./bin/console doctrine:database:drop --if-exists --force $(ENV)
	@docker exec -u www-data --tty $(PROJECT_NAME)-php-fpm php ./bin/console doctrine:database:create $(ENV)
	@docker exec -u www-data --tty $(PROJECT_NAME)-php-fpm php ./bin/console doctrine:query:sql 'CREATE SCHEMA $(PROJECT_NAME)' $(ENV)
	@docker exec -u www-data --tty $(PROJECT_NAME)-php-fpm php ./bin/console doctrine:query:sql 'CREATE EXTENSION IF NOT EXISTS "uuid-ossp"' $(ENV)
	@docker exec -u www-data --tty $(PROJECT_NAME)-php-fpm php ./bin/console doctrine:migrations:migrate --no-interaction $(ENV)

.PHONY: back-migrate
back-migrate:
	@docker exec -u www-data --tty $(PROJECT_NAME)-php-fpm php ./bin/console doctrine:migrations:migrate --no-interaction

.PHONY: back-migrations-diff
back-migrations-diff:
	@docker exec -u www-data --tty $(PROJECT_NAME)-php-fpm php ./bin/console doctrine:migrations:diff --no-interaction

.PHONY: back-phpcs-fixer
back-phpcs-fixer:
	@docker exec -u www-data --tty $(PROJECT_NAME)-php-fpm php -dxdebug.mode=off vendor/bin/php-cs-fixer fix

.PHONY: back-phpcs-validate
back-phpcs-validate:
	@docker exec -u www-data --tty $(PROJECT_NAME)-php-fpm php -dxdebug.mode=off vendor/bin/php-cs-fixer fix --dry-run --diff --stop-on-violation

.PHONY: back-phpstan
back-phpstan:
	@docker exec -u www-data --tty $(PROJECT_NAME)-php-fpm php vendor/bin/phpstan analyse -c phpstan.neon -l 2 src tests --no-interaction --xdebug

ifdef suite
TESTS_SUITE=--testsuite $(suite)
endif

.PHONY: back-tests
back-tests:
	@docker exec -u www-data --tty -e APP_ENV=test $(PROJECT_NAME)-php-fpm php -dxdebug.mode=coverage vendor/bin/phpunit $(TESTS_SUITE)

.PHONY: back-coverage
back-coverage:
	@$(MAKE) back-db-update env=test
	@docker exec -u www-data --tty -e APP_ENV=test $(PROJECT_NAME)-php-fpm php -dxdebug.mode=coverage vendor/bin/phpunit

# ==========================================
# Frontend
# ==========================================

.PHONY: front-install
front-install:
	@docker exec --tty $(PROJECT_NAME)-frontend npm ci

.PHONY: front-logs
front-logs:
	@docker compose -f docker-compose.yaml -f docker-compose.dev.yaml -p $(PROJECT_NAME) logs -f frontend

.PHONY: front-build
front-build:
	@docker exec --tty $(PROJECT_NAME)-frontend npx ng build --configuration production

.PHONY: front-lint
front-lint:
	@docker exec --tty $(PROJECT_NAME)-frontend npm run lint

.PHONY: front-tests
front-tests:
	@docker exec --tty $(PROJECT_NAME)-frontend npm run tests
