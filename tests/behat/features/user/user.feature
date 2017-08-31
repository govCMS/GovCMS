@api
Feature: User
  govCMS site user behavior

  Scenario: Create user
    Given users:
      | name | mail | status |
      | Tim | tim@example.com | 1 |
    When I am logged in as "Tim"
    Then I should see "Commonwealth of Australia"
