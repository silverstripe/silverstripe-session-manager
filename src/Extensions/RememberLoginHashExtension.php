<?php

namespace SilverStripe\SessionManager\Extensions;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\SessionManager\Models\LoginSession;
use SilverStripe\SessionManager\Security\LogInAuthenticationHandler;

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
    public function onAfterGenerateToken(): void
    {
        $loginHandler = Injector::inst()->get(LogInAuthenticationHandler::class);
        $loginHandler->setRememberLoginHash($this->owner);
    }

    /**
     * @return void
     */
    public function onAfterRenewToken(): void
    {
        $loginHandler = Injector::inst()->get(LogInAuthenticationHandler::class);
        $request = Injector::inst()->get(HTTPRequest::class);
        $request->getSession()->set($loginHandler->getSessionVariable(), $this->owner->LoginSessionID);
    }
}
