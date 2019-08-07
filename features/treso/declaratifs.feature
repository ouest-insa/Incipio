Feature: Declaratifs/ summary pages

  Scenario: I can see the TVA summary homepage
    Given I am logged in as "admin"
    When I go to "/Tresorerie/Declaratifs/TVA"
    Then the response status code should be 200
    Then I should see "Déclaration de la TVA"

  Scenario: I can see the TVA summary homepage for a selected date
    Given I am logged in as "admin"
    When I go to "/Tresorerie/Declaratifs/TVA/2019/08"
    Then the response status code should be 200
    Then I should see "Déclaration de la TVA"
    Then I should see "Déclaratif pour la période : August 2019"

  Scenario: I can see the TVA summary homepage for a selected 3 months period
    Given I am logged in as "admin"
    When I go to "/Tresorerie/Declaratifs/TVA/2019/08/1"
    Then the response status code should be 200
    Then I should see "Déclaration de la TVA"
    Then I should see "Déclaratif pour la période : August 2019 - October 2019"

  Scenario: I can see the BRC summary homepage
    Given I am logged in as "admin"
    When I go to "/Tresorerie/Declaratifs/BRC"
    Then the response status code should be 200
    Then I should see "Déclaration URSSAF - BRC"

  Scenario: I can see the TVA summary homepage for a selected date
    Given I am logged in as "admin"
    When I go to "/Tresorerie/Declaratifs/BRC/2019/08"
    Then the response status code should be 200
    Then I should see "Déclaration URSSAF - BRC"
