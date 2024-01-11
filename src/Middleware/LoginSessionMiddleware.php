<?php

namespace SilverStripe\SessionManager\Middleware;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Middleware\HTTPMiddleware;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\Connect\DatabaseException;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Models\LoginSession;
use SilverStripe\SessionManager\Security\LogInAuthenticationHandler;

class LoginSessionMiddleware implements HTTPMiddleware
{
    /**
     * @param HTTPRequest $request
     * @param callable $delegate
     * @return HTTPResponse
     */
    public function process(HTTPRequest $request, callable $delegate)
    {
        $loginHandler = Injector::inst()->get(LogInAuthenticationHandler::class);
        $member = Security::getCurrentUser();
        if (!$member) {
            return $delegate($request);
        }

        try {
            $loginSessionID = $request->getSession()->get($loginHandler->getSessionVariable());
            $loginSession = LoginSession::get_by_id($loginSessionID);

            // If the session has already been revoked, or we've got a mismatched
            // member / session, log the user out (this also revokes the session)
            if (!$loginSession || (int)$loginSession->MemberID !== (int)$member->ID) {
                RememberLoginHash::setLogoutAcrossDevices(false);
                $identityStore = Injector::inst()->get(IdentityStore::class);
                $identityStore->logOut($request);
                return $delegate($request);
            }

            // Update LastAccessed date and IP address if > that threshold
            $date = DBDatetime::now()->Rfc2822();
            $threshold = LoginSession::config()->get('last_accessed_threshold');
            if (strtotime($date) > strtotime($loginSession->LastAccessed) + $threshold) {
                $loginSession->updateLastAccessed($request);
            }
        } catch (DatabaseException $e) {
            // Database isn't ready, carry on.
        }

        return $delegate($request);
    }
}
