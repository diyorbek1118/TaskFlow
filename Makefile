.PHONY: up down build restart logs shell db-shell redis-shell npm-install composer-install migrate seed fresh optimize clear-cache test queue help

# Load environment variables
ifneq (,$(wildcard .env))
    include .env
    export
endif

# Default target
help:
	@echo "Available commands:"
	@echo "  up          - Start all containers"
	@echo "  down        - Stop all containers"
	@echo "  build       - Build and start containers"
	@echo "  restart     - Restart all containers"
	@echo "  logs        - Show container logs"
	@echo "  shell       - Access application shell"
	@echo "  db-shell    - Access database shell"
	@echo "  npm-install - Install npm dependencies"
	@echo "  composer-install - Install composer dependencies"
	@echo "  migrate     - Run database migrations"
	@echo "  seed        - Run database seeders"
	@echo "  fresh       - Fresh migration (drop all tables)"
	@echo "  optimize    - Optimize application"
	@echo "  clear-cache - Clear all caches"
	@echo "  test        - Run tests"
	@echo "  help        - Show this help message"

# Start all containers
up:
	sudo docker compose up -d

# Stop all containers
down:
	sudo docker compose down

# Build and start containers
build:
	sudo docker compose up -d --build

# Restart all containers
restart:
	sudo docker compose restart

# Show container logs
logs:
	sudo docker compose logs -f

# Access application shell
shell:
	sudo docker compose exec app bash

# Access database shell
db-shell:
	sudo docker compose exec mysql mysql -u${DB_USERNAME:-laravel} -p${DB_PASSWORD:-secret} ${DB_DATABASE:-laravel}

# Access redis shell
redis-shell:
	sudo docker compose exec redis redis-cli

# Install npm dependencies
npm-install:
	sudo docker compose exec app npm install

# Install composer dependencies
composer-install:
	sudo docker compose exec app composer install

# Run database migrations
migrate:
	sudo docker compose exec app php artisan migrate

# Run database seeders
seed:
	docker compose exec app php artisan db:seed

# Fresh migration (drop all tables)
fresh:
	docker compose exec app php artisan migrate:fresh

# Clear all caches
clear-cache:
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

# Run tests
test:
	docker compose exec app php artisan test

# Quick development setup
dev-setup: build composer-install npm-install migrate optimize
	@echo "Development environment is ready!"
	@echo "App URL: http://localhost:${APP_PORT:-8080}"

# View queue logs
queue-logs:
	docker compose logs -f queue

# Git o'zgarishlarni yuborish (Interaktiv xabar so'rash bilan)
push:
	@echo "📝 Commit xabarini kiriting:"
	@read msg; \
	if [ -z "$$msg" ]; then \
		echo "❌ Xato: Xabar bo'sh bo'lishi mumkin emas!"; \
		exit 1; \
	fi; \
	git add -A; \
	git commit -m "$$msg" || echo "⚠️ Commit qilinmadi (o'zgarish yo'q)"; \
	git push origin main; \
	echo "✅ O'zgarishlar Git-ga muvaffaqiyatli yuborildi!"

# Oxirgi versiyani olish
pull:
	git pull origin main
	@echo "📥 Oxirgi o'zgarishlar qabul qilindi!"

# Git holatini tekshirish
status:
	git status

# Default deployment command
deploy:
	@echo "🔄 Pulling latest changes..."
	git pull origin main

	@echo "🚀 Starting deployment..."
	
	# 1. Install dependencies
	composer install --optimize-autoloader --no-dev --no-interaction
	
	# 2. Database migrations (optional, uncomment if needed)
	# php artisan migrate --force
	
	# 3. Optimization
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	php artisan event:cache
	php artisan optimize
	
	@echo "✅ Deployment finished successfully!"

# Just optimization
optimize:
	@echo "⚡ Optimizing application..."
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	php artisan event:cache
	php artisan optimize
	@echo "✨ Optimization done!"
