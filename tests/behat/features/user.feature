Feature: User
  govCMS site user behaviours

  @api
  Scenario: Create users
    Given users:
      | name | status |
      | Test user | 1 |
    When I am logged in as "Test user"
    Then I should see the link "Log out"
