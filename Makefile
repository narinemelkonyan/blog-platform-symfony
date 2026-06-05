.PHONY: help up down restart logs cache-clear dev-up dev-down dev-restart prod-up prod-down prod-restart clean

.DEFAULT_GOAL := help

help: ## Show this help message
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-20s %s\n", $$1, $$2}'

up: ## Start all containers (default docker-compose.yml)
	@echo "Starting containers..."
	docker compose up -d --build

down: ## Stop all containers (default docker-compose.yml)
	@echo "Stopping containers..."
	docker compose down

restart: down up ## Restart all containers

logs: ## Show logs from all containers
	docker compose logs -f

dev-up: ## Start development environment
	@echo "Starting development environment..."
	docker compose -f docker-compose.dev.yml up -d --build

dev-down: ## Stop development environment
	@echo "Stopping development environment..."
	docker compose -f docker-compose.dev.yml down

dev-restart: dev-down dev-up ## Restart development environment

dev-logs: ## Show development logs
	docker compose -f docker-compose.dev.yml logs -f

prod-up: ## Start production environment
	@echo "Starting production environment..."
	docker compose -f docker-compose.prod.yml up -d --build

prod-down: ## Stop production environment
	@echo "Stopping production environment..."
	docker compose -f docker-compose.prod.yml down

prod-restart: prod-down prod-up ## Restart production environment

prod-logs: ## Show production logs
	docker compose -f docker-compose.prod.yml logs -f

cache-clear: ## Clear Symfony cache
	@echo "Clearing cache..."
	docker compose exec php php bin/console cache:clear

app-cache-clear: ## Clear application Redis cache pools
	docker exec symfony-php php bin/console app:cache:clear

cache-warmup: ## Warmup Symfony cache
	docker compose exec php php bin/console cache:warmup

composer-update: ## Update Composer dependencies
	docker compose exec php composer update

migration-migrate: ## Run database migrations
	docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction

migration-generate: ## Generate new migration
	docker compose exec php php bin/console doctrine:migrations:generate

migration-diff: ## Generate migration
	docker compose exec php php bin/console doctrine:migrations:diff

asset-map-compile: ## Asset compile
	docker compose exec php php bin/console asset-map:compile
