Feature: Related documents
  Related documents can be created on a member or an etude
  They are correctly removed on cascade

  @createSchema
  Scenario: I can create a related document on a member
    Given I am logged in as "admin"
    Given I am on "/Documents/Upload/Etudiant/1"
    Then the response status code should be 200
    Given I fill in "Nom du fichier" with "composer.json"
    And I attach the file "composer.json" to "Fichier"
    And I press "Mettre en ligne"
    Then I should see "Document mis en ligne"
    And the url should match "/personne/membre/1"

  @dropSchema
  Scenario: I can delete a member with related document
    Given I am logged in as "admin"
    Given I am on "/personne/membre/1"
    Then I should see "composer.json"
    Given I am on "/personne/membre/modifier/1"
    And I press "Supprimer le membre"
    Then the response status code should be 200
    And the url should match "/personne/membre"
    And I should see "Membre supprimé"
