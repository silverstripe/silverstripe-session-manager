<?php

namespace SilverStripe\SessionManager\Tests\Security;

use SilverStripe\Control\Session;
use SilverStripe\Control\Tests\HttpRequestMockBuilder;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Models\LoginSession;
use SilverStripe\SessionManager\Security\LogOutAuthenticationHandler;

class LogOutAuthenticationHandlerTest extends SapphireTest
{
    use HttpRequestMockBuilder;

    protected static $fixture_file = 'LogOutAuthenticationHandlerTest.yml';

    public function testLogout()
    {
        $sessionID = $this->objFromFixture(LoginSession::class, 'x1')->ID;
        $session = new Session(['activeLoginSession' => $sessionID]);
        $request = $this->buildRequestMock('/', [], [], null, $session);
        $request->method('getIP')->willReturn('192.168.0.1');

        $member1 = $this->objFromFixture(Member::class, 'member1');
        Security::setCurrentUser($member1);

        $logOutAuthenticationHandler = new LogOutAuthenticationHandler();
        $logOutAuthenticationHandler->logOut($request);

        $loginSession = $member1->LoginSessions()->first();
        $this->assertNull(
            $loginSession,
            "Login session is deleted on logout"
        );
    }
}
