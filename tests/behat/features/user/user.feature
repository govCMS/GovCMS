Feature: User
  govCMS site user testing

  @api @javascript
  Scenario: Create user
    Given users:
      | name | mail |
      | Tim Junior | tim.junior@example.com |
    And I am logged in as a user with the "Administer users" permission
    When I visit "admin/people"
    Then I should see the text "Tim Junior"
  @api @javascript
  Scenario: As a Site Administrator I should have access to Basic site settings
    Given I am logged in as a user with the "site_administrator" role
    When I go to "admin/config/system/site-information"
    Then I should see "Basic site settings"