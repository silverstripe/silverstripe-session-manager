<?php

namespace SilverStripe\SessionManager\Tests\Security;

use SilverStripe\Control\Middleware\ConfirmationMiddleware\Url;
use SilverStripe\Control\Session;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Control\LoginSessionMiddleware;
use SilverStripe\SessionManager\Model\LoginSession;
use SilverStripe\SessionManager\Security\LogInAuthenticationHandler;

class LogInAuthenticationHandlerTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static $fixture_file = 'LogInAuthenticationHandlerTest.yml';

    public function testLogin()
    {
        // log out
        Security::setCurrentUser(null);

        $member1 = $this->objFromFixture(Member::class, 'member1');
        $loginAuthenticationHandler = new LogInAuthenticationHandler();

        $loginSession = $member1->LoginSessions()->first();
        $this->assertNull(
            $loginSession,
            "Login session is does not exist before login"
        );

        $loginAuthenticationHandler->logIn(
            $member1,
            false,
            null
        );

        $loginSession = $member1->LoginSessions()->first();
        $this->assertNotNull(
            $loginSession,
            "Login session is generated on login"
        );
    }
}
