<?php

namespace SilverStripe\SessionManager\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Member;
use SilverStripe\SessionManager\Security\LogInAuthenticationHandler;
use SilverStripe\SessionManager\Security\LogOutAuthenticationHandler;

class SecurityExtension extends Extension
{
    public function updateSetCurrentUser(?Member $member)
    {
        if (is_null($member)) {
            $handler = Injector::inst()->get(LogOutAuthenticationHandler::class);
            $handler->logOut();
            return;
        }
        $handler = Injector::inst()->get(LogInAuthenticationHandler::class);
        $handler->logIn($member);
    }
}
