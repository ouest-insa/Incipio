Feature: Default Doctypes
  The provided doctypes can be published out of the box.

  @dropSchema
  Scenario: Empty scenario t drop schema
    Given I am logged in as "admin"

  @createSchema
  Scenario: I can export the default AP
    Given I am logged in as "admin"
    Given I am on "/Documents/Publiposter/AP/etude/2"
    Then the response status code should not be 500
    Then the response status code should be 200


  Scenario: I can export the default CC
    Given I am logged in as "admin"
    Given I am on "/Documents/Publiposter/CC/etude/2"
    Then the response status code should not be 500
    Then the response status code should be 200
