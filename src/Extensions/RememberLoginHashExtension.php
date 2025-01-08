<?php

namespace SilverStripe\SessionManager\Extensions;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\SessionManager\Models\LoginSession;
use SilverStripe\SessionManager\Security\LogInAuthenticationHandler;
use SilverStripe\SessionManager\Middleware\LoginSessionMiddleware;

/**
 * @method LoginSession LoginSession()
 *
 * @extends Extension<RememberLoginHash>
 */
class RememberLoginHashExtension extends Extension
{
    /**
     * @var array
     */
    private static $has_one = [
        'LoginSession' => LoginSession::class
    ];

    /**
     * @return void
     */
    protected function onAfterGenerateToken(): void
    {
        $loginHandler = Injector::inst()->get(LogInAuthenticationHandler::class);
        $loginHandler->setRememberLoginHash($this->owner);
    }

    /**
     * Overwrites the core session variable with the LoginSession record ID
     * during session renewal when the user selects 'remember me' (ALC).
     * This works in tandem with LoginSessionMiddleware, and avoids the
     * overhead of an additional DB query.
     *
     * @see LoginSessionMiddleware
     * @return void
     */
    protected function onAfterRenewSession(): void
    {
        $loginHandler = Injector::inst()->get(LogInAuthenticationHandler::class);
        $request = Injector::inst()->get(HTTPRequest::class);
        $request->getSession()->set($loginHandler->getSessionVariable(), $this->owner->LoginSessionID);
    }
}
