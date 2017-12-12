@api
Feature: User
  govCMS site user behavior

  Scenario: Create user
    Given users:
      | name | mail |
      | Tim Junior | tim.junior@example.com |
    When I am logged in as "Tim Junior"
    Then I should see "Commonwealth of Australia"
