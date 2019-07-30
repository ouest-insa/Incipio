Feature: Admin
  As an admin I am be able to access easyadmin homepage

  Scenario: I can see link
    Given I am logged in as "admin"
    Given I am on "/"
    Then the response status code should be 200
    And I follow "Administration BDD"
    Then the response status code should be 200
    And I should see "EasyAdmin"
