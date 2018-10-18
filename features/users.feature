Feature: Users
  In order to access user resources
  As an API client
  I need to be able to retrieve, create and delete them trough the API

  Scenario: Retrieve a collection of users
    Given the following users exist:
      | firstname | lastname | email              |
      | John      | Doe      | john.doe@email.com |
      | Jane      | Doe      | jane.doe@email.com |
    When I add "Accept" header equal to "application/hal+json"
    And I send a "GET" request to "/api/users"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/hal+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "_links": {
        "self": {
          "href": "/api/users"
        },
        "item": [
          {
            "href": "/api/users/1"
          },
          {
            "href": "/api/users/2"
          }
        ]
      },
      "totalItems": 2,
      "itemsPerPage": 30,
      "_embedded": {
        "item": [
          {
            "_links": {
              "self": {
                "href": "/api/users/1"
              }
            },
            "id": 1,
            "firstname": "John",
            "lastname": "Doe",
            "email": "john.doe@email.com"
          },
          {
            "_links": {
              "self": {
                "href": "/api/users/2"
              }
            },
            "id": 2,
            "firstname": "Jane",
            "lastname": "Doe",
            "email": "jane.doe@email.com"
          }
        ]
      }
    }
    """

  Scenario: Create an user
    When I add "Content-Type" header equal to "application/hal+json"
    And I add "Accept" header equal to "application/hal+json"
    And I send a "POST" request to "/api/users" with body:
    """
    {
      "firstname": "John",
      "lastname": "Doe",
      "email": "john.doe@email.com"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/hal+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "_links": {
        "self": {
          "href": "/api/users/1"
        }
      },
      "id": 1,
      "firstname": "John",
      "lastname": "Doe",
      "email": "john.doe@email.com"
    }
    """

  Scenario: Retrieve one user
    Given the following users exist:
      | firstname | lastname | email              |
      | John      | Doe      | john.doe@email.com |
    When I add "Accept" header equal to "application/hal+json"
    And I send a "GET" request to "/api/users/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/hal+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "_links": {
        "self": {
          "href": "/api/users/1"
        }
      },
      "id": 1,
      "firstname": "John",
      "lastname": "Doe",
      "email": "john.doe@email.com"
    }
    """

  Scenario: Delete a user
    Given the following users exist:
      | firstname | lastname | email              |
      | John      | Doe      | john.doe@email.com |
    When I add "Accept" header equal to "application/hal+json"
    And I send a "DELETE" request to "/api/users/1"
    Then the response status code should be 204
    And the response should be empty

  Scenario Outline: Throw a 404 error when a user is not found
    When I add "Accept" header equal to "application/hal+json"
    And I send a "<method>" request to "<url>"
    Then the response status code should be 404
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json; charset=utf-8"
    And the JSON node "type" should contain "https://tools.ietf.org/html/rfc2616#section-10"
    And the JSON node "title" should contain "An error occurred"
    And the JSON node "detail" should contain "Not Found"

    Examples:
      | url          | method |
      | /api/users/1 | GET    |
      | /api/users/1 | DELETE |
