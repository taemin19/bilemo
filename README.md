# OpenClassrooms-Projet7

[Créez un web service exposant une API](https://openclassrooms.com/projects/creez-un-web-service-exposant-une-api)

## Description

The API is written in PHP with the Symfony framework.
The development is based on:
- [Docker](https://www.docker.com/) (runtime environment)
- [Symfony 4](https://symfony.com/doc/current/index.html)
- [Api Platform server component](https://api-platform.com/docs/distribution#using-symfony-flex-and-composer-advanced-users)
- [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle) (Json Web Token authentication)
- [Behat](http://behat.org/en/latest/) (functional tests)
- [PHPUnit](https://phpunit.de/index.html) (unit tests)

The API will follow these rules:
- The API only returns JSON responses
- The supported formats are jsonhal (application/hal+json) and jsonproblem (application/problem+json)
- Resources API routes require authentication
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
    $ make app

See the available commands of the Makefile:

    $ make

#### Database
Add the tables/schema to database:

    $ make db

Load a set of data:

    $ make fixtures

#### JSON Web Token
Generate the SSH keys:

    $ openssl genrsa -out config/jwt/private.pem -aes256 4096
    $ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
    $ openssl rsa -in config/jwt/private.pem -out config/jwt/private2.pem
    $ mv config/jwt/private.pem config/jwt/private.pem-back
    $ mv config/jwt/private2.pem config/jwt/private.pem
    
*Test SSH keys are already generated in the project.*    

#### Testing
Create test database and add tables/schema:

    $ make db--test

Run unit tests:

    $ make test-u

Run functional tests:

    $ make test-f

### Author
- Daniel Thébault
