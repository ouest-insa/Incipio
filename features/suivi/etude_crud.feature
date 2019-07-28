Feature: Etude
  I am able to CRUD an Etude.

  Scenario: I can see Etude pipeline
    Given I am logged in as "admin"
    When I go to "/suivi"
    Then the response status code should be 200
    Then I should see "Etudes en Négociation"

  Scenario: I can see an etude
    Given I am logged in as "admin"
    When I go to "/suivi/etude/315GLA"
    Then the response status code should be 200
  
  Scenario: I can export Gantt Chart
	Given I am logged in as "admin"
    When I go to "/Documents/GetGantt/1"
    Then the response status code should be 200
