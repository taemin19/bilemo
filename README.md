# OpenClassrooms-Projet7

[Créez un web service exposant une API](https://openclassrooms.com/projects/creez-un-web-service-exposant-une-api)

## Description

The API is written in PHP with the Symfony framework.
The development is based on:
- [Symfony 4](https://symfony.com/doc/current/index.html)
- [Api Platform server component](https://api-platform.com/docs/distribution#using-symfony-flex-and-composer-advanced-users)
- [Docker](https://www.docker.com/) runtime environment

The API will follow these rules:
- The API only returns JSON responses
- All API routes require authentication
- Authentication is handled via JSON Web Token (JWT)

## Requirements
- PHP 7.1.3 or higher
- [Composer](https://getcomposer.org/)

## Installation
#### 1. Install the project:
The project should be installed using [Make](https://www.gnu.org/software/make/), be sure to have configured the `.env` file after using `cp .env.dist .env`.
    
    $ git clone https://github.com/taemin19/bilemo.git
    $ cd bilemo
    $ cp .env.dist .env
    $ make dev

See the available commands of the Makefile.

    $ make

#### 2. Add the tables/schema to Database:
    $ make migrate

### Author
- Daniel Thébault
