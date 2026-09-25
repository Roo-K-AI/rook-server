.PHONY: \
	build up down start stop restart shell logs composer install update require dump-autoload test artisan migrate fresh seed tinker npm yarn dev watch help storage network

default: help

build:
	@echo "Building containers..."
	docker compose build

up:
	@echo "Starting containers..."
	docker compose up -d

down:
	@echo "Stopping containers..."
	docker compose down

start:
	@echo "Starting containers..."
	docker compose start

stop:
	@echo "Stopping containers..."
	docker compose stop

restart:
	@echo "Restarting containers..."
	docker compose restart

shell:
	@echo "Entering app container..."
	docker compose exec app bash

logs:
	@echo "Showing logs..."
	docker compose logs -f

composer:
	@echo "Running composer..."
	docker compose exec app composer $(filter-out $@,$(MAKECMDGOALS))

install:
	@echo "Installing dependencies..."
	docker compose exec app composer install

update:
	@echo "Updating dependencies..."
	docker compose exec app composer update

require:
	@echo "Requiring package..."
	docker compose exec app composer require $(filter-out $@,$(MAKECMDGOALS))

dump-autoload:
	@echo "Dumping autoload..."
	docker compose exec app composer dump-autoload

test:
	@echo "Running tests..."
	docker compose exec app php artisan test

artisan:
	@echo "Running artisan..."
	docker compose exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

migrate:
	@echo "Running migrations..."
	docker compose exec app php artisan migrate

storage:
	@echo "Setting up storage permissions..."
	docker compose exec app mkdir -p storage bootstrap/cache
	docker compose exec app chmod -R 775 storage bootstrap/cache
	docker compose exec app chown -R www-data:www-data storage bootstrap/cache

network:
	@echo "creating rook network"
	docker network create rook-network

fresh:
	@echo "Fresh migrations..."
	docker compose exec app php artisan migrate:fresh

seed:
	@echo "Running seeds..."
	docker compose exec app php artisan db:seed

tinker:
	@echo "Running tinker..."
	docker compose exec app php artisan tinker

npm:
	@echo "Running npm..."
	docker compose exec npm npm $(filter-out $@,$(MAKECMDGOALS))

yarn:
	@echo "Running yarn..."
	docker compose exec npm yarn $(filter-out $@,$(MAKECMDGOALS))

dev:
	@echo "Running dev..."
	docker compose exec npm npm run dev

watch:
	@echo "Running watch..."
	docker compose exec npm npm run watch

%:
	@:

help:
	@echo "\033[34mRook - Commandes disponibles:\033[0m"
	@echo ""
	@echo "\033[32mDocker:\033[0m"
	@echo "  \033[33mmake up\033[0m\t\t\t- Lance les conteneurs Docker en arrière-plan"
	@echo "  \033[33mmake down\033[0m\t\t\t- Arrête et supprime les conteneurs"
	@echo "  \033[33mmake build\033[0m\t\t\t- Reconstruit les images Docker"
	@echo "  \033[33mmake restart\033[0m\t\t\t- Redémarre les conteneurs"
	@echo "  \033[33mmake logs\033[0m\t\t\t- Affiche les logs des conteneurs (mode suivi)"
	@echo "  \033[33mmake shell\033[0m\t\t\t- Ouvre un shell Bash dans le conteneur 'app'"
	@echo ""
	@echo "\033[32mComposer:\033[0m"
	@echo "  \033[33mmake install\033[0m\t\t\t- Installe les dépendances PHP"
	@echo "  \033[33mmake update\033[0m\t\t\t- Met à jour les dépendances PHP"
	@echo "  \033[33mmake require <pkg>\033[0m\t\t- Ajoute un package Composer (ex: make require laravel/sanctum)"
	@echo "  \033[33mmake dump-autoload\033[0m\t\t- Régénère l'autoloader"
	@echo ""
	@echo "\033[32mArtisan (Laravel):\033[0m"
	@echo "  \033[33mmake migrate\033[0m\t\t\t- Exécute les migrations de base de données"
	@echo "  \033[33mmake fresh\033[0m\t\t\t- Réinitialise la BDD (drop + migrations)"
	@echo "  \033[33mmake seed\033[0m\t\t\t- Exécute les seeders"
	@echo "  \033[33mmake tinker\033[0m\t\t\t- Lance Tinker (REPL Laravel)"
	@echo "  \033[33mmake test\033[0m\t\t\t- Lance les tests PHPUnit"
	@echo ""
	@echo "\033[32mFrontend:\033[0m"
	@echo "  \033[33mmake npm <cmd>\033[0m\t\t- Exécute une commande npm (ex: make npm install)"
	@echo "  \033[33mmake yarn <cmd>\033[0m\t\t- Exécute une commande yarn"
	@echo "  \033[33mmake dev\033[0m\t\t\t- Compile les assets en mode développement"
	@echo "  \033[33mmake watch\033[0m\t\t\t- Surveille les changements des assets"
	@echo ""
	@echo "\033[32mConfiguration:\033[0m"
	@echo "  \033[33mmake storage\033[0m\t\t\t- Configure les permissions des dossiers Laravel"
	@echo "  \033[33mmake network\033[0m\t\t\t- Crée le réseau Docker rook-network"
	@echo ""
	@echo "\033[31mNote:\033[0m Pour les commandes avec arguments (composer/artisan/npm), utilisez:"
	@echo "  \033[33mmake <cible> <arguments>\033[0m (ex: make artisan queue:work)"
