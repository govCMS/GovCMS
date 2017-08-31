Feature: Home Page
  Ensure the home page is rendering correctly

  Scenario: View the homepage content
    Given I am on the homepage
    Then I should see "Commonwealth of Australia"
