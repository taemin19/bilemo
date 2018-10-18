.PHONY: cache-clear
.DEFAULT_GOAL = help

# Constants
DOCKER_COMPOSE = docker-compose
DOCKER = docker

# Environments
ENV_PHP = $(DOCKER) exec bilemo_php
ENV_BLACKFIRE = $(DOCKER) exec bilemo_blackfire

# Tools
COMPOSER = $(ENV_PHP) composer

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-10s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## Main commands
dev: docker-compose.yml ## Build and run the app
	$(DOCKER_COMPOSE) build --no-cache
	$(DOCKER_COMPOSE) up -d --build --remove-orphans --force-recreate
	make install
	make cache

rebuild: docker-compose.yml ## Build and run the app if you change a service’s Dockerfile or the contents of its build directory
	$(DOCKER_COMPOSE) up -d --build --remove-orphans --no-recreate
	make install
	make cache

start: docker-compose.yml ## Starts containers
	$(DOCKER_COMPOSE) up -d

stop: docker-compose.yml ## Stops running containers without removing them
	$(DOCKER_COMPOSE) stop

down: docker-compose.yml ## Stops containers and removes containers
	$(DOCKER_COMPOSE) down

clean: ## Allow to delete the generated files and clean the project folder
	$(ENV_PHP) rm -rf .env ./vendor

## Composer commands
install: composer.json ## Install the dependencies
	$(COMPOSER) install -a -o
	$(COMPOSER) clear-cache
	$(COMPOSER) dump-autoload --optimize --classmap-authoritative

update: composer.lock ## Get the latest versions of the dependencies
	$(COMPOSER) update -a -o

require: composer.json ## Install a package to require
	$(COMPOSER) require $(PACKAGE) -a -o

require-dev: composer.json ## Install a package to require-dev
	$(COMPOSER) require --dev $(PACKAGE) -a -o

remove: composer.json ## Remove a package from require
	$(COMPOSER) remove $(PACKAGE) -a -o

remove-dev: composer.json ## Remove a package from require-dev
	$(COMPOSER) remove --dev $(PACKAGE) -a -o

autoload: composer.json ## Update the autoloader
	$(COMPOSER) dump-autoload -a -o

## Symfony commands
cache: var/cache ## Clear the cache in current env
	$(ENV_PHP) php bin/console cache:clear

cache--dev: var/cache/dev ## Clear the cache in dev env
	$(ENV_PHP) php bin/console cache:clear --env=dev

cache--prod: var/cache/prod ## Clear the cache in prod env
	$(ENV_PHP) php bin/console cache:clear --env=prod

cache--test: var/cache/test ## Clear the cache in test env
	$(ENV_PHP) php bin/console cache:clear --env=test

router: config/routes ## Get a list of the routes
	$(ENV_PHP) php bin/console debug:router

migration: ## Generate a new migration
	$(ENV_PHP) php bin/console make:migration

migrate: ## Execute migrations that have not already been run
	$(ENV_PHP) php bin/console doctrine:migrations:migrate

fixtures: src/DataFixtures ## Load a "fake" set data into the database
	$(ENV_PHP) php bin/console doctrine:fixtures:load

db--test: ## Create database and add tables/shema in test env
	$(ENV_PHP) php bin/console doctrine:database:create --env=test
	$(ENV_PHP) php bin/console doctrine:schema:update --force --env=test

functional-test: features ## Run functional tests
	$(ENV_PHP) vendor/bin/behat
