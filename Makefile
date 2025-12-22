.PHONY: help start stop restart build install test clean logs shell db-shell

help: ## Afficher cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

start: ## Démarrer les conteneurs Docker
	docker compose up -d
	@echo "✓ Application démarrée sur http://localhost:8080"
	@echo "✓ phpMyAdmin disponible sur http://localhost:8081"

stop: ## Arrêter les conteneurs Docker
	docker compose down

restart: stop start ## Redémarrer les conteneurs

build: ## Construire les images Docker
	docker compose build --no-cache

install: ## Installer les dépendances Composer
	docker compose exec php composer install

update: ## Mettre à jour les dépendances
	docker compose exec php composer update

test: ## Lancer les tests unitaires
	docker compose exec php vendor/bin/phpunit

test-coverage: ## Lancer les tests avec couverture
	docker compose exec php vendor/bin/phpunit --coverage-html coverage

clean: ## Nettoyer les fichiers temporaires
	rm -rf vendor
	rm -rf coverage
	docker compose down -v

logs: ## Afficher les logs
	docker compose logs -f

logs-php: ## Afficher les logs PHP
	docker compose logs -f php

logs-mysql: ## Afficher les logs MySQL
	docker compose logs -f mysql

shell: ## Ouvrir un shell dans le conteneur PHP
	docker compose exec php bash

db-shell: ## Ouvrir un shell MySQL
	docker compose exec mysql mysql -u crm_user -pcrm_password crm_db

db-reset: ## Réinitialiser la base de données
	docker compose exec mysql mysql -u root -proot_password -e "DROP DATABASE IF EXISTS crm_db; CREATE DATABASE crm_db;"
	docker compose exec -T mysql mysql -u root -proot_password crm_db < docker/init.sql
	@echo "✓ Base de données réinitialisée avec succès!"

db-init: ## Initialiser la base de données
	docker compose exec -T mysql mysql -u root -proot_password crm_db < docker/init.sql
	@echo "✓ Base de données initialisée avec succès!"

composer-dump: ## Regénérer l'autoload Composer
	docker compose exec php composer dump-autoload

ps: ## Lister les conteneurs
	docker compose ps

init: build start install db-init ## Initialisation complète du projet
	@echo "✓ Projet initialisé avec succès!"
