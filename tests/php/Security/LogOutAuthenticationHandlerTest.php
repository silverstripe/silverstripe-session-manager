<?php

namespace SilverStripe\SessionManager\Tests\Security;

use SilverStripe\Control\Session;
use SilverStripe\Control\Tests\HttpRequestMockBuilder;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Model\LoginSession;
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

    public function testMemberAdminAccessLogout()
    {
        $deviceALoginSessionID = $this->objFromFixture(LoginSession::class, 'admin_access_device_a')->ID;
        $session = new Session(['activeLoginSession' => $deviceALoginSessionID]);
        $request = $this->buildRequestMock('/', [], [], null, $session);
        $request->method('getIP')->willReturn('192.168.0.1');

        $member = $this->objFromFixture(Member::class, 'admin_access');
        Security::setCurrentUser($member);

        $logOutAuthenticationHandler = new LogOutAuthenticationHandler();
        $logOutAuthenticationHandler->logOut($request);

        $loginSession = $member->LoginSessions()->find('UserAgent', 'Admin Access Device A');
        $this->assertNull($loginSession, 'Login session A is deleted on logout');

        $loginSession = $member->LoginSessions()->find('UserAgent', 'Admin Access Device B');
        $this->assertNotNull($loginSession, 'Login session B is not deleted on logout');
    }

    public function testMemberNoAdminAccessLogout()
    {
        $deviceALoginSessionID = $this->objFromFixture(LoginSession::class, 'no_admin_access_device_a')->ID;
        $session = new Session(['activeLoginSession' => $deviceALoginSessionID]);
        $request = $this->buildRequestMock('/', [], [], null, $session);
        $request->method('getIP')->willReturn('192.168.0.1');

        $member = $this->objFromFixture(Member::class, 'no_admin_access');
        Security::setCurrentUser($member);

        $logOutAuthenticationHandler = new LogOutAuthenticationHandler();
        $logOutAuthenticationHandler->logOut($request);

        $loginSession = $member->LoginSessions()->find('UserAgent', 'No Admin Access Device A');
        $this->assertNull($loginSession, 'Login session A is deleted on logout');

        $loginSession = $member->LoginSessions()->find('UserAgent', 'No Admin Access Device B');
        $this->assertNull($loginSession, 'Login session B is deleted on logout');
    }

    public function testMemberNoAdminAccessLogoutRevokeAllFalse()
    {
        Config::modify()->set(LogOutAuthenticationHandler::class, 'no_admin_access_revoke_all_on_logout', false);

        $deviceALoginSessionID = $this->objFromFixture(LoginSession::class, 'no_admin_access_device_a')->ID;
        $session = new Session(['activeLoginSession' => $deviceALoginSessionID]);
        $request = $this->buildRequestMock('/', [], [], null, $session);
        $request->method('getIP')->willReturn('192.168.0.1');

        $member = $this->objFromFixture(Member::class, 'no_admin_access');
        Security::setCurrentUser($member);

        $logOutAuthenticationHandler = new LogOutAuthenticationHandler();
        $logOutAuthenticationHandler->logOut($request);

        $loginSession = $member->LoginSessions()->find('UserAgent', 'No Admin Access Device A');
        $this->assertNull($loginSession, 'Login session A is deleted on logout');

        $loginSession = $member->LoginSessions()->find('UserAgent', 'No Admin Access Device B');
        $this->assertNotNull($loginSession, 'Login session B is not deleted on logout');
    }
}
