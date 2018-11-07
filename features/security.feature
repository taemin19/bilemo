Feature: Security
  In order to access protected resources
  As an API client
  I need to be able to authenticate

  Scenario: Authenticate the client to obtain the JWT token
    Given the following clients exist:
      | name | username | password |
      | Doe  | johndoe  | test     |
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/login_check" with body:
    """
    {
      "username": "johndoe",
      "password": "test"
    }
    """
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "application/json"
    And the response should be in JSON
    And the JSON node "token" should exist

  Scenario Outline: Throw 401 errors when the client authentication failed
    Given the following clients exist:
      | name | username | password |
      | Doe  | johndoe  | test     |
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/login_check" with body:
    """
    {
      "username": "<username>",
      "password": "<password>"
    }
    """
    Then the response status code should be 401
    And the header "Content-Type" should be equal to "application/json"
    And the header "WWW-Authenticate" should be equal to "Bearer"
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "code": 401,
      "message": "Bad credentials"
    }
    """

    Examples:
      | username | password |
      | john     | test     |
      | johndoe  | testtest |

  Scenario Outline: Throw 401 errors when the authentication is not yet been provided
    When I add "Accept" header equal to "application/hal+json"
    And I send a "<method>" request to "<url>"
    Then the response status code should be 401
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/json"
    And the header "WWW-Authenticate" should be equal to "Bearer"
    And the JSON should be equal to:
    """
    {
      "code": 401,
      "message": "JWT Token not found"
    }
    """

    Examples:
      | url             | method |
      | /api/products   | GET    |
      | /api/products/1 | GET    |
      | /api/users      | GET    |
      | /api/users      | POST   |
      | /api/users/1    | GET    |
      | /api/users/1    | DELETE |

  @loginAsClient1
  Scenario: Throw 403 error when the current client try to access another client's user
    Given the following users exist for the client2:
      | firstname | lastname | email              |
      | John      | Doe      | john.doe@email.com |
    When I add "Accept" header equal to "application/hal+json"
    And I send a "GET" request to "/api/users/1"
    Then the response status code should be 403
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json; charset=utf-8"
    And the JSON nodes should be equal to:
      | type   | https://tools.ietf.org/html/rfc2616#section-10 |
      | title  | An error occurred                              |
      | detail | Access Denied.                                 |
