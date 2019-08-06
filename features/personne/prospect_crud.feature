Feature: Prospect
  As an suiveur I am able to CRUD a Prospect.

  Scenario: I can see Prospect List
    Given I am logged in as "suiveur"
    When I go to "/prospect"
    Then the response status code should be 200
    And I should see "Liste des prospects"

  Scenario: I can create a new Prospect
    Given I am logged in as "suiveur"
    When I go to "/prospect/add"
    Then the response status code should be 200
    When I fill in "Nom" with "James"
    And I select "Particulier" from "Entite"
    And I press "Enregistrer"
    Then the response status code should be 200
    # will be the 9th prospect
    Then the url should match "/prospect/voir/9"
    And I should see "Prospect enregistré"
    And I should see "James"
    When I go to "/prospect"
    Then the response status code should be 200
    And I should see "James"

  Scenario: I can edit a Prospect
    Given I am logged in as "suiveur"
    When I go to "/prospect/modifier/9"
    Then the response status code should be 200
    And I select "Association" from "Entite"
    And I press "Enregistrer"
    Then the response status code should be 200
    Then the url should match "/prospect/voir/9"
    And I should see "Prospect enregistré"

  Scenario: I can delete a Prospect
    Given I am logged in as "suiveur"
    When I go to "/prospect/modifier/9"
    Then the response status code should be 200
    And I press "Supprimer le prospect"
    #And I press "OK"
    #Then the url should match "/prospect"
    #And I should see "Prospect supprimé"
    #And I should not see "James"

# TODO : ajout de tests pour la création d'employés