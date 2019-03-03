Feature: CRUD a user
  I am able to register and connect.

  Scenario: Registration link is visible
    Given I am on "/login"
    Then I should see "Inscription"

  Scenario: I can register
    Given I am on "/register"
    Then the response status code should be 200
    And I fill in "fos_user_registration_form_email" with "test_user@localdomain.local"
    And I fill in "fos_user_registration_form_username" with "test_user"
    And I fill in "fos_user_registration_form_plainPassword_first" with "test_user"
    And I fill in "fos_user_registration_form_plainPassword_second" with "test_user"
    And I press "Créer un compte"
    Then I should see "L'utilisateur a été créé avec succès."

