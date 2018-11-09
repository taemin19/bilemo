.PHONY: cache-clear
.DEFAULT_GOAL = help

# Constants
DOCKER_COMPOSE = docker-compose

# Environments
ENV_PHP = $(DOCKER_COMPOSE) exec php
ENV_BLACKFIRE = $(DOCKER_COMPOSE) exec blackfire

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

require: composer.json ## Install a package to require, PACKAGE=package_name
	$(COMPOSER) require $(PACKAGE) -a -o

require-dev: composer.json ## Install a package to require-dev, PACKAGE=package_name
	$(COMPOSER) require --dev $(PACKAGE) -a -o

remove: composer.json ## Remove a package from require, PACKAGE=package_name
	$(COMPOSER) remove $(PACKAGE) -a -o

remove-dev: composer.json ## Remove a package from require-dev, PACKAGE=package_name
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

db-migration: ## Generate a new migration
	$(ENV_PHP) php bin/console make:migration

db-validate: ## Validate mapping/database
	$(ENV_PHP) php bin/console doctrine:schema:validate

db-migrate: src/Migrations ## Execute migrations that have not already been run
	$(ENV_PHP) php bin/console doctrine:migrations:migrate

fixtures: src/DataFixtures ## Load a "fake" set data into the database
	$(ENV_PHP) php bin/console doctrine:fixtures:load

db--test: config/packages/test/doctrine.yaml ## Create a test database and add tables/schema in test env
	$(ENV_PHP) php bin/console doctrine:database:create --env=test
	$(ENV_PHP) php bin/console doctrine:schema:update --force --env=test

functional-test: features ## Run functional tests, [FEATURE=example.feature] to test a specific feature
	$(ENV_PHP) vendor/bin/behat features/$(FEATURE)

unit-test: tests ## Run unit tests, [TEST=Dir[/Test.php]] to test a directory or a specific test file
	$(ENV_PHP) php ./bin/phpunit tests/$(TEST)

## Console commands
client: src/Command/CreateClientCommand.php ## Create a client, [ARGS=name username password] for a no interactive wizard
	$(ENV_PHP) php bin/console app:create-client $(ARGS)

client-delete: src/Command/DeleteClientCommand.php ## Delete a client, [ARGS=username] for a no interactive wizard
	$(ENV_PHP) php bin/console app:delete-client $(ARGS)

product: src/Command/CreateProductCommand.php ## Create a product, [ARGS=model brand storage color price description] for a no interactive wizard
	$(ENV_PHP) php bin/console app:create-product $(ARGS)

product-delete: src/Command/DeleteProductCommand.php ## Delete a product, [ARGS=id] for a no interactive wizard
	$(ENV_PHP) php bin/console app:delete-product $(ARGS)

## Blackfire commands
blackfire: ## Profile HTTP request, [TOKEN=token] [ROUTE=path]
	$(ENV_BLACKFIRE) blackfire curl -H "Authorization: Bearer $(TOKEN)" http://172.20.0.1:8083/api/$(ROUTE)
