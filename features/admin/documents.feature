Feature: Admin
  As an admin I am be able to access documents pages

  Scenario: I can see Documents Homepage & Upload button
    Given I am logged in as "admin"
    Given I am on "/Documents"
    Then the response status code should be 200
    Then I should see "Liste des Documents"
    And I should see "Ajouter un Doctype"

  Scenario: I can see Upload Document page
    Given I am logged in as "admin"
    Given I am on "/DocumentsType/Upload"
    Then the response status code should be 200
    Then I should see "Uploader un Document Type"
    And I should see "Uploader"
