Feature: Admin
  As an admin I am be able to TUNE a Preferences
  
  Scenario: I can see Preferences Homepage & Validate button
    Given I am logged in as "admin"
    Given I am on "/parameters/admin"
    Then the response status code should be 200
    Then I should see "Paramètres"
    And I should see "Enregistrer"

  Scenario: I can rename the junior
    Given I am logged in as "admin"
    Given I am on "/parameters/admin"
    Then the response status code should be 200
    When I fill in "Nom de la junior" with "New Jeyser"
    When I fill in "Année de création de la junior" with "1983"
    And I press "Enregistrer"
    Then the url should match "/parameters/admin"
    Then I should see "Paramètres mis à jour"
    And I should see "Enregistrer"
    And the response should contain "New Jeyser"
    And the response should contain "1983"
