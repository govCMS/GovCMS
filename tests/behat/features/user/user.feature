@api
Feature: User
  govCMS site user behavior

  Scenario: Create user
    Given users:
      | name | mail | roles |
      | Tim Junior | tim.junior@example.com | 1 |
    When I am logged in as "Tim Junior"
    Then I should see "Commonwealth of Australia"
