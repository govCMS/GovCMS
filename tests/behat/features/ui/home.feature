Feature: Home Page
  Ensure the home page is rendering correctly

  Background:
    Given I am an anonymous user
    When I visit "/"

  @api @javascript
  Scenario: View the homepage content
    And I should see "GovCMS"

  Scenario: Check the homepage meta tag.
    Then the response should contain "<meta name=\"Generator\" content=\"Drupal 9 (http://drupal.org) + GovCMS (http://govcms.gov.au)\" />"
