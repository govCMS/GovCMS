Feature: Home Page
  Ensure the home page is rendering correctly

  Background:
    [Given|*] I am on the homepage

  @api @javascript
  Scenario: View the homepage content
    Given I am an anonymous user
    Then I should see "Commonwealth of Australia"

  Scenario: Check the homepage meta tag.
    Given I am an anonymous user
    Then the response should contain "<meta name=\"Generator\" content=\"Drupal 8 (http://drupal.org) + govCMS (http://govcms.gov.au)\" />"
