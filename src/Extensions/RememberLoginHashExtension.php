<?php

namespace SilverStripe\SessionManager\Extensions;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\SessionManager\Model\LoginSession;
use SilverStripe\SessionManager\Security\LogInAuthenticationHandler;

class RememberLoginHashExtension extends Extension
{
    private static $has_one = [
        'LoginSession' => LoginSession::class
    ];

    public function onAfterGenerateToken()
    {
        $loginHandler = Injector::inst()->get(LogInAuthenticationHandler::class);
        $loginHandler->setRememberLoginHash($this->owner);
    }

    public function onAfterRenewToken()
    {
        $loginHandler = Injector::inst()->get(LogInAuthenticationHandler::class);
        $request = Injector::inst()->get(HTTPRequest::class);
        $request->getSession()->set($loginHandler->getSessionVariable(), $this->owner->LoginSessionID);
    }
}
