Feature: Home Page
  Ensure the home page is rendering correctly

  Scenario: View the homepage content
    Given I am on the homepage
    Then I should see "Commonwealth of Australia"
    And the response should contain "<meta name=\"Generator\" content=\"Drupal 8 (http://drupal.org) + govCMS (http://govcms.gov.au)\" />"
