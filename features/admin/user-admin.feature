Feature: User admin
  As an admin I am able to edit users from the user admin panel

  @dropSchema
  Scenario: Empty scenario to drop schema
    Given I am logged in as "admin"

  @createSchema
  Scenario: I can edit a user
    Given I am logged in as "admin"
    Given I am on "/user/modifier/2"
    Then the response status code should be 200
    Then I should see "Modifier un utilisateur"
    When I fill in "Nom d'utilisateur" with "eleve2"
    When I fill in "Adresse e-mail" with "eleve2@local.localdomain.com"
    When I fill in "Mot de passe admin pour validation" with "admin"
    And I press "Enregistrer l'utilisateur"
    Then the url should match "/user/lister"
    Then I should see "Utilisateur modifié"
    And I should see "eleve2@local.localdomain.com"
    And I should not see "eleve@local.localdomain.com"

  Scenario: Bringing back eleve for next test
    Given I am logged in as "admin"
    Given I am on "/user/modifier/2"
    Then the response status code should be 200
    Then I should see "Modifier un utilisateur"
    When I fill in "Nom d'utilisateur" with "eleve"
    When I fill in "Adresse e-mail" with "eleve@local.localdomain.com"
    When I fill in "Mot de passe admin pour validation" with "admin"
    And I press "Enregistrer l'utilisateur"
    Then the url should match "/user/lister"
    Then I should see "Utilisateur modifié"
    And I should see "eleve@local.localdomain.com"
    And I should not see "eleve2@local.localdomain.com"

  Scenario Outline: Users without ROLE_ADMIN user admin panel
    Given I am logged in as "<user>"
    Given I am on "<page>"
    Then the response status code should be 403
    And I should see "Page interdite"
    And I should not see "Liste des utilisateurs"

    Examples:
      | user    | page              |
      | eleve   | /user/lister      |
      | eleve   | /user/modifier/2  |
      | eleve   | /user/supprimer/2 |
      | suiveur | /user/lister      |
      | suiveur | /user/modifier/2  |
      | suiveur | /user/supprimer/2 |
      | treso   | /user/lister      |
      | treso   | /user/modifier/2  |
      | treso   | /user/supprimer/2 |
      | rgpd    | /user/lister      |
      | rgpd    | /user/modifier/2  |
      | rgpd    | /user/supprimer/2 |
      | ca      | /user/lister      |
      | ca      | /user/modifier/2  |
      | ca      | /user/supprimer/2 |
