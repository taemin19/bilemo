@loginAsClient1
Feature: Products
  In order to access product resources
  As an API client
  I need to be able to retrieve them trough the API

  Scenario: Retrieve a collection of products
    Given the following products exist:
      | model     | brand   | storage | color  | price   | description |
      | galaxy S9 | samsung | 64      | black  | 849.99  | new model   |
      | iphone X  | apple   | 128     | silver | 1089.99 | new model   |
    When I add "Accept" header equal to "application/hal+json"
    And I send a "GET" request to "/api/products"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/hal+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "_links": {
        "self": {
          "href": "/api/products"
        },
        "item": [
          {
              "href": "/api/products/1"
          },
          {
              "href": "/api/products/2"
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
                "href": "/api/products/1"
              }
            },
            "id": 1,
            "model": "galaxy S9",
            "brand": "samsung",
            "storage": 64,
            "color": "black",
            "price": 849.99,
            "description": "new model"
          },
          {
            "_links": {
              "self": {
                "href": "/api/products/2"
              }
            },
            "id": 2,
            "model": "iphone X",
            "brand": "apple",
            "storage": 128,
            "color": "silver",
            "price": 1089.99,
            "description": "new model"
          }
        ]
      }
    }
    """

  Scenario Outline: Apply a search filter on product collection
    Given the following products exist:
      | model   | brand   | storage | color  | price   | description |
      | product | samsung | 64      | black  | 849.99  | new model   |
      | product | apple   | 64      | black  | 849.99  | new model   |
    When I add "Accept" header equal to "application/hal+json"
    And I send a "GET" request to "/api/products" with parameters:
      | key   | value   |
      | brand | <brand> |
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/hal+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "_links": {
        "self": {
          "href": "/api/products?brand=<brand>"
        },
        "item": [
          {
              "href": "/api/products/<id>"
          }
        ]
      },
      "totalItems": 1,
      "itemsPerPage": 30,
      "_embedded": {
        "item": [
          {
            "_links": {
              "self": {
                "href": "/api/products/<id>"
              }
            },
            "id": <id>,
            "model": "product",
            "brand": "<brand>",
            "storage": 64,
            "color": "black",
            "price": 849.99,
            "description": "new model"
          }
        ]
      }
    }
    """

    Examples:
      | brand   | id |
      | samsung | 1  |
      | apple   | 2  |

  Scenario: Retrieve one product
    Given the following products exist:
      | model     | brand   | storage | color | price  | description |
      | galaxy S9 | samsung | 64      | black | 849.99 | new model   |
    When I add "Accept" header equal to "application/hal+json"
    And I send a "GET" request to "/api/products/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/hal+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "_links": {
        "self": {
          "href": "/api/products/1"
        }
      },
      "id": 1,
      "model": "galaxy S9",
      "brand": "samsung",
      "storage": 64,
      "color": "black",
      "price": 849.99,
      "description": "new model"
    }
    """

  Scenario: Throw a 404 error when a product is not found
    When I add "Accept" header equal to "application/hal+json"
    And I send a "GET" request to "/api/products/1"
    Then the response status code should be 404
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/problem+json; charset=utf-8"
    And the JSON node "type" should contain "https://tools.ietf.org/html/rfc2616#section-10"
    And the JSON node "title" should contain "An error occurred"
    And the JSON node "detail" should contain "Not Found"
