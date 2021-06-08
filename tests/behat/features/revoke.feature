@retry

Feature: See other devices and revoke their access
  As a CMS user
  I want to see other devices that are currently logged in
  So that I can revoke their access

  Background:
    Given I am logged in with "ADMIN" permissions
    # Create a mock login session
    And There is a login session for a second device

  Scenario: I can see other devices and revoke their access
    When I go to "/admin/security"
    # Click the ADMIN user
    And I click on the ".col-FirstName" element
    # Ensure XHR loaded from endpoint
    And I wait until I see the ".login-session  .text-success" element
    # Assert text for the two login sessions
    Then I should see the text "Current" in the ".login-session  .text-success" element
    Then I should see the text "Log out" in the ".login-session__logout" element
    # Click "Log out" button
    When I click on the ".login-session__logout" element
    # Wait for modal to fade in
    And I wait until I see the ".modal-dialog .btn-primary" element
    # Click the green button in the modal
    When I click on the ".modal-dialog .btn-primary" element
    # Assert text has changed
    Then I should see the text "Logging out..." in the ".login-session__logout" element
    # Assert hidden element is applied which fades to not visible via a css transition
    Then I see the ".login-session.hidden" element
    # Assert toast notification
    Then I should see a "Successfully removed session." success toast
