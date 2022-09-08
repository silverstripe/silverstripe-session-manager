@retry

Feature: See other devices and revoke their access
  As a CMS user
  I want to see other devices that are currently logged in
  So that I can revoke their access

  Background:
    Given a "group" "AUTHOR" has permissions "Access to 'Pages' section"
    And I am logged in as a member of "AUTHOR" group
    # Create a mock login session
    And There is a login session for a second device

  Scenario: I can see other devices and revoke their access
    When I go to "/admin/myprofile"
    # Assert text for the two login sessions
    Then I should see the text "Current" in the ".login-session  .text-success" element
    Then I should see the text "Log out" in the ".login-session__logout" element
    # Click "Log out" button
    When I click on the ".login-session__logout" element

    # We cannot reliably test the "Logging out..." text or css transition to hide the logged-out session
    # because of behat timing issues, which is possibly the result of not enough resources available
    # for the current worker.

    # Assert toast notification
    Then I should see a "Successfully removed session." success toast
