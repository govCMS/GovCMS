Feature: Home Page
  Ensure the home page is rendering correctly

  Background:
    [Given|*] I am on the homepage

  @api @javascript
  Scenario: View the homepage content
    Given I am an anonymous user
    Then the response should contain "<input placeholder=\"What are you looking for?\" data-drupal-selector=\"edit-keys\" type=\"text\" id=\"edit-keys\" name=\"keys\" value=\"\" size=\"30\" maxlength=\"128\" class=\"form-text au-text-input\">"

  Scenario: Check the homepage meta tag.
    Given I am an anonymous user
    Then the response should contain "<meta name=\"Generator\" content=\"Drupal 8 (http://drupal.org) + govCMS (http://govcms.gov.au)\" />"
