Feature: Bulletin de versement
  I am able to CRUD a BV.

  Scenario: I can see the BV homepage
    Given I am logged in as "admin"
    When I go to "/Tresorerie/BV"
    Then the response status code should be 200
    Then I should see "Liste des BV"
    And I should see "Ajouter un BV"

  Scenario: I can create a new BV with valid Base URSSAF data
    Given I am logged in as "admin"
    When I go to "/Tresorerie/BV/Ajouter"
    Then the response status code should be 200
    Then I should see "Ajout d'un BV"
    When I fill in "Mandat" with "2018"
    When I fill in "Numero" with "1"
    When I fill in "Nombre j e h" with "10"
    When I fill in "Remuneration brute par j e h" with "200"
    # default 316BLA/20XX/RM/1
    When I select "1" from "Mission"
    When I fill in "Numéro de Virement" with "1234"
    When I fill in "Date d'émission" with "2017-02-18"
    And I press "Enregistrer"
    Then the url should match "/Tresorerie/BV"
    And I should see "BV enregistré"
    And the response status code should be 200

  Scenario: I can't create a new BV with invalid Base URSSAF data
    Given I am logged in as "admin"
    When I go to "/Tresorerie/BV/Ajouter"
    Then the response status code should be 200
    Then I should see "Ajout d'un BV"
    When I fill in "Mandat" with "2018"
    When I fill in "Numero" with "2"
    When I fill in "Nombre j e h" with "10"
    When I fill in "Remuneration brute par j e h" with "200"
    # default: 316BLA/20XX/RM/1
    When I select "2" from "Mission"
    When I fill in "Numéro de Virement" with "12345"
    # Data unavailable for that date
    When I fill in "Date d'émission" with "2001-02-18"
    And I press "Enregistrer"
    Then the url should match "/Tresorerie/BV"
    And I should see "Il n'y a aucune base Urssaf définie pour cette période"
    And I should not see "BV enregistré"

  Scenario: I can export a Bv with a fresh new install
    Given I am logged in as "admin"
    When I go to "/Tresorerie/BV/1"
    Then I should see "Générer le BV"
    And the response status code should be 200
    Given I am on "/Documents/Publiposter/BV/bv/1"
    Then the response status code should not be 500
    Then the response status code should be 200

