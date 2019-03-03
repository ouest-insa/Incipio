Feature: Filiere
  As an admin I am be able to CRUD a Filiere.

  # the "@createSchema" annotation provided by Behat creates a temporary SQLite database for testing the API
  @createSchema
  Scenario: I can see Filiere Homepage & Add Filiere button
    Given I am logged in as "admin"
    Given I am on "/poste"
    Then the response status code should be 200
    Then I should see "Liste des filières"
    And I should see "Ajouter une filière"

  Scenario: I can create a new Filiere
    Given I am logged in as "admin"
    Given I am on "/filiere/add"
    Then the response status code should be 200
    When I fill in "Nom" with "Mecanique"
    When I fill in "Description" with "Mecanique"
    # it will create the filiere id 6
    And I press "Enregistrer"
    Then the url should match "/poste"
    And I should see "Filière ajoutée"
    And I should see "Mecanique"

  Scenario: I can edit a Filiere
    Given I am logged in as "admin"
    Given I am on "/filiere/modifier/1"
    Then the response status code should be 200
    When I fill in "Nom" with "Testing science"
    And I press "Enregistrer"
    Then the url should match "/poste"
    And I should see "Filière modifiée"
    And I should see "Testing science"

  Scenario: I can not delete a Filiere with members
    Given I am logged in as "admin"
    Given I am on "/filiere/modifier/1"
    Then the response status code should be 200
    And I press "Supprimer la filière"
    Then the url should match "/poste"
    And I should see "Impossible de supprimer une filière ayant des membres."

  Scenario Outline: Users without ROLE_ADMIN can't access Filiere module
    Given I am logged in as "<user>"
    Given I am on "<page>"
    Then the response status code should be 403
    And I should see "Page interdite"
    And I should not see "Filières"

    Examples:
      | user    | page                         |
      | eleve   | /poste              |
      | eleve   | /filiere/add        |
      | eleve   | /filiere/modifier/1 |
      | suiveur | /filiere/add        |
      | suiveur | /filiere/modifier/1 |
      | treso   | /poste              |
      | treso   | /filiere/add        |
      | treso   | /filiere/modifier/1 |
      | rgpd    | //poste              |
      | rgpd    | /filiere/add        |
      | rgpd    | /filiere/modifier/1 |
      | ca      | /filiere/add        |
      | ca      | /filiere/modifier/1 |

  Scenario Outline: Users with ROLE_SUIVEUR can access page but can't act on Filiere
    Given I am logged in as "<user>"
    Given I am on "<page>"
    Then the response status code should be 200
    And I should not see "Ajouter une filière"
    And I should not see "Filière d'exemple"

    Examples:
      | user    | page                         |
      | suiveur | /poste              |
      | ca      | /poste              |

  Scenario: I can delete a Filiere
    Given I am logged in as "admin"
    Given I am on "/filiere/modifier/6"
    Then the response status code should be 200
    And I press "Supprimer la filière"
    Then the url should match "/poste"
    And I should see "Filière supprimée"
    And I should not see "Mecanique"
