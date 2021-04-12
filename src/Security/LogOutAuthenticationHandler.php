<?php

namespace SilverStripe\SessionManager\Security;

use InvalidArgumentException;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\AuthenticationHandler;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Model\LoginSession;

/**
 * This is separate to LogInAuthenticationHandler so that it can be registered with
 * Injector and called *before* the other AuthenticationHandler::logOut() implementations
 */
class LogOutAuthenticationHandler implements AuthenticationHandler
{
    use Configurable;

    /**
     * Members with no admin access i.e. website user accounts - revoke all sessions on logout
     *
     * @config
     * @var bool
     */
    private static $no_admin_access_revoke_all_on_logout = true;

    /**
     * @param HTTPRequest $request
     * @return Member|null
     * @throws ValidationException
     */
    public function authenticateRequest(HTTPRequest $request)
    {
        // noop
    }

    /**
     * @param Member $member
     * @param bool $persistent
     * @param HTTPRequest $request|null
     */
    public function logIn(Member $member, $persistent = false, HTTPRequest $request = null)
    {
        // noop
    }

    /**
     * @param HTTPRequest $request|null
     * @throws InvalidArgumentException
     */
    public function logOut(HTTPRequest $request = null)
    {
        // Fall back to retrieving request from current Controller if available
        if ($request === null) {
            if (!Controller::has_curr()) {
                throw new InvalidArgumentException(
                    "Authentication with SessionManager enabled requires an active HTTPRequest."
                );
            }

            $request = Controller::curr()->getRequest();
        }

        $loginHandler = Injector::inst()->get(LogInAuthenticationHandler::class);
        $member = Security::getCurrentUser();

        // Members without admin access are unable to access session manager and individually revoke login sessions
        // These types of members often exist on sites where website users can create accounts via the frontend
        // and a corresponding record is created on the Member table
        // Since there's no other way for these users to logout any malicious devices, auto log out of all devices
        // when one device is logged out
        if (static::config()->get('no_admin_access_revoke_all_on_logout') && !$this->hasAdminAccess($member)) {
            foreach ($member->LoginSessions() as $loginSession) {
                $loginSession->delete();
            }
        } else {
            $loginSessionID = $request->getSession()->get($loginHandler->getSessionVariable());
            $loginSession = LoginSession::get()->byID($loginSessionID);
            if ($loginSession && $loginSession->canDelete($member)) {
                $loginSession->delete();
            }
        }

        $request->getSession()->clear($loginHandler->getSessionVariable());
    }

    /**
     * Decides whether the provided user has access to any LeftAndMain controller, which indicates some level
     * of access to the CMS.
     *
     * @see LeftAndMain::init()
     * @param Member $member
     * @return bool
     */
    private function hasAdminAccess(Member $member): bool
    {
        return Member::actAs($member, function () use ($member) {
            $leftAndMain = LeftAndMain::singleton();
            if ($leftAndMain->canView($member)) {
                return true;
            }

            // Look through all LeftAndMain subclasses to find if one permits the member to view
            $menu = $leftAndMain->MainMenu(false);
            foreach ($menu as $candidate) {
                if (
                    $candidate->Link
                    && $candidate->Link !== $leftAndMain->Link()
                    && $candidate->MenuItem->controller
                    && singleton($candidate->MenuItem->controller)->canView($member)
                ) {
                    return true;
                }
            }

            return false;
        });
    }
}
