<?php

namespace Kinglozzer\SessionManager\Extensions;

use Kinglozzer\SessionManager\Model\LoginSession;
use Kinglozzer\SessionManager\Security\LogInAuthenticationHandler;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;

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
