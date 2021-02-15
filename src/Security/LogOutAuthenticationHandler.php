<?php

namespace SilverStripe\SessionManager\Security;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\AuthenticationHandler;
use SilverStripe\Security\Member;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Model\LoginSession;

/**
 * This is separate to LogInAuthenticationHandler so that it can be registered with
 * Injector and called *before* the other AuthenticationHandler::logOut() implementations
 */
class LogOutAuthenticationHandler implements AuthenticationHandler
{
    public function authenticateRequest(HTTPRequest $request)
    {
    }

    public function logIn(Member $member, $persistent = false, HTTPRequest $request = null)
    {
    }

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

        if (RememberLoginHash::config()->get('logout_across_devices')) {
            foreach ($member->LoginSessions() as $session) {
                $session->delete();
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
}
