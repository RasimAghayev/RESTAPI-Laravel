# Phase 5 Makefile — RESTAPI-Laravel container baseline
# Targets: build test verify up down run shell logs migrate seed lint security clean
# `up` depends on `verify` — the stack only comes up after the image builds and tests pass.
# COMPOSE_PROJECT_NAME is set in docker-compose.yml (name: RESTAPI-Laravel-p5).

APP_IMAGE := restapi-laravel-p5-app:local
TEST_IMAGE := restapi-laravel-p5-test:local
DEPS_IMAGE := restapi-laravel-p5-deps:local
COMPOSE := docker compose
APP_CONTAINER := restapi-laravel-p5-app

.PHONY: help build test verify up down run shell logs migrate seed lint security clean

help: ## Show available targets
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-22s\033[0m %s\n", $$1, $$2}

build: ## Build the runtime image (multi-stage Dockerfile)
	docker build -t $(APP_IMAGE) .

test: ## Build the test stage and run PHPUnit (build fails if tests fail)
	docker build --target test -t $(TEST_IMAGE) .

verify: build test ## Build + run tests (the verification gate)

up: verify ## Bring the stack up only after verify passes
	$(COMPOSE) up -d

down: ## Stop and remove the stack (keeps the DB volume)
	$(COMPOSE) down

run: ## Bring the stack up in the foreground (after verify)
	$(COMPOSE) up

shell: ## Open a shell in the running app container
	docker exec -it $(APP_CONTAINER) sh

logs: ## Follow container logs
	$(COMPOSE) logs -f

migrate: ## Run migrations on the running stack
	$(COMPOSE) exec $(APP_CONTAINER) php artisan migrate --force

seed: ## Seed the database on the running stack
	$(COMPOSE) exec $(APP_CONTAINER) php artisan db:seed --force

lint: verify ## Run Pint formatter check (dev tools, from the test image)
	docker run --rm $(TEST_IMAGE) vendor/bin/pint --test

security: ## Scan the runtime image for known vulnerabilities (best effort)
	@docker scout cves $(APP_IMAGE) 2>/dev/null || echo "image scanner unavailable in this environment"

clean: ## Remove containers and images (DB volume kept unless CONFIRM=1)
	$(COMPOSE) down --remove-orphans 2>/dev/null || true
	docker rmi $(APP_IMAGE) $(TEST_IMAGE) $(DEPS_IMAGE) 2>/dev/null || true
	@if [ "$(CONFIRM)" = "1" ]; then \
		$(COMPOSE) down -v 2>/dev/null || true; \
		echo "DB volume removed"; \
	else \
		echo "DB volume preserved (use CONFIRM=1 to remove)"; \
	fi
