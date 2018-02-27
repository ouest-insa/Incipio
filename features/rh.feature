Feature: RH
  As an admin I am be able to CRUD a Competence
  
  @createSchema
  Scenario: I can see RH Homepage & Add Competence button
    Given I am logged in as "admin"
    Given I am on "/rh"
    Then the response status code should be 200
    Then I should see "Liste des Compétences"
    And I should see "Ajouter une compétence"
    
  Scenario: I can create a new Competence
    Given I am logged in as "admin"
    Given I am on "/rh/competence/add"
    Then the response status code should be 200
    When I fill in "Nom" with "Django"
    When I fill in "Description" with "Django"
    And I press "Enregistrer la compétence"
    #The id is 14
    Then the url should match "/rh/competence/14"
    And I should see "Modifier la compétence"
    And I should see "Django"
    And I should see "Intervenants Potentiels"
    And I should see "Etudes liées"
    
  Scenario: I can see a Competence
    Given I am logged in as "admin"
    Given I am on "/rh/competence/1"
    Then the response status code should be 200
    Then I should see "Modifier la compétence"
    And I should see "Intervenants Potentiels"
    And I should see "Etudes liées"
  
  Scenario: I can edit a Competence
    Given I am logged in as "admin"
    Given I am on "/rh/competence/modifier/1"
    Then the response status code should be 200
    When I fill in "Nom" with "Test"
    And I press "Enregistrer la compétence"
    Then the url should match "/rh/competence/1"
    And I should see "Test"
    And I should not see "PHP"
    And I should see "Modifier la compétence"
    And I should see "Intervenants Potentiels"
    And I should see "Etudes liées"
    
  Scenario: I can delete a Competence
    Given I am logged in as "admin"
    Given I am on "/rh/competence/modifier/14"
    Then the response status code should be 200
    And I press "Supprimer la compétence"
    Then the url should match "/rh"
    And I should not see "Django"
  
  @dropSchema
  Scenario: I can see parameters
    Given I am logged in as "admin"
    Given I am on "/rh/visualiser/competences"
    Then the response status code should be 200
