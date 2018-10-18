# OpenClassrooms-Projet7

[Créez un web service exposant une API](https://openclassrooms.com/projects/creez-un-web-service-exposant-une-api)

## Description

The API is written in PHP with the Symfony framework.
The development is based on:
- [Symfony 4](https://symfony.com/doc/current/index.html)
- [Api Platform server component](https://api-platform.com/docs/distribution#using-symfony-flex-and-composer-advanced-users)
- [Docker](https://www.docker.com/) (runtime environment)
- [Behat](http://behat.org/en/latest/) (functional tests)

The API will follow these rules:
- The API only returns JSON responses
- The supported formats are jsonhal (application/hal+json) and jsonproblem (application/problem+json)
- All API routes require authentication
- Authentication is handled via JSON Web Token (JWT)

## Requirements
- PHP 7.1.3 or higher
- [Composer](https://getcomposer.org/)

## Installation
#### Install the project
The project should be installed using [Make](https://www.gnu.org/software/make/), be sure to have configured the `.env` file after using `cp .env.dist .env`.
    
    $ git clone https://github.com/taemin19/bilemo.git
    $ cd bilemo
    $ cp .env.dist .env
    $ make dev

See the available commands of the Makefile:

    $ make

#### Database
Add the tables/schema to database:

    $ make migrate

Load a set of data:

    $ make fixtures

#### Testing
Create test database and add tables/schema:

    $ make db--test

Run functional tests:

    $ make functional-test

### Author
- Daniel Thébault
