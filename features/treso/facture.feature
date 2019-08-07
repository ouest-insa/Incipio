Feature: Facture
  I am able to CRUD a Facture.


  Scenario: I can see the Facture homepage
    Given I am logged in as "admin"
    When I go to "/Tresorerie/Factures"
    Then the response status code should be 200
    Then I should see "Liste des factures"
    And I should see "Ajouter une Facture"

  Scenario: I can create a new Facture
    Given I am logged in as "admin"
    When I go to "/Tresorerie/Facture/Ajouter"
    Then the response status code should be 200
    Then I should see "Ajouter une facture"
    When I fill in "Exercice Comptable" with "2018"
    When I fill in "Numéro de la Facture" with "3"
    When I fill in "Objet de la Facture" with "Gherkin facture"
    And I press "Enregistrer la Facture"
    Then the url should match "/Tresorerie/Facture/3"
    And I should see "Facture ajoutée"
    And the response status code should be 200


  Scenario: I can export a Facture with a fresh new install
    Given I am logged in as "admin"
    When I go to "/Tresorerie/Facture/1"
    Then I should see "Générer la Facture"
    And the response status code should be 200
    Given I am on "/Documents/Publiposter/FS/facture/3"
    Then the response status code should not be 500
    Then the response status code should be 200
