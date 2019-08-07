Feature: Note de Frais
  I am able to CRUD a Note de Frais.


  Scenario: I can see the Note de Frais homepage
    Given I am logged in as "admin"
    When I go to "/Tresorerie/NoteDeFrais"
    Then the response status code should be 200
    Then I should see "Liste des notes de frais"
    And I should see "Ajouter une note de frais"

  Scenario: I can create a new Note de Frais
    Given I am logged in as "admin"
    When I go to "/Tresorerie/NoteDeFrais/Ajouter"
    Then the response status code should be 200
    Then I should see "Ajouter une note de frais"
    When I fill in "Mandat" with "2018"
    When I fill in "Numéro de la Note de Frais" with "1"
    When I fill in "Objet de la Note de Frais" with "Buy some Gherkin"
    And I press "Enregistrer la Note de Frais"
    Then the url should match "/Tresorerie/NoteDeFrais/1"
    And I should see "Note de frais enregistrée"
    And the response status code should be 200


  Scenario: I cannot export a Note de Frais with a fresh new install
    Given I am logged in as "admin"
    When I go to "/Tresorerie/NoteDeFrais/1"
    Then I should see "Générer la Note de Frais"
    And the response status code should be 200
    Given I am on "/Documents/Publiposter/NF/nf/2"
    Then the response status code should not be 500
    Then the response status code should be 404
