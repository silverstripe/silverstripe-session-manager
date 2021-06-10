<?php

namespace SilverStripe\SessionManager\Tests\Middleware;

use SilverStripe\Control\Cookie;
use SilverStripe\Control\Middleware\ConfirmationMiddleware\Url;
use SilverStripe\Control\Session;
use SilverStripe\Control\Tests\HttpRequestMockBuilder;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Middleware\LoginSessionMiddleware;
use SilverStripe\SessionManager\Models\LoginSession;

class LoginSessionMiddlewareTest extends SapphireTest
{
    use HttpRequestMockBuilder;

    protected static $fixture_file = 'LoginSessionMiddlewareTest.yml';

    public function testMiddlewareUpdatesLoginSession()
    {
        // log out
        Security::setCurrentUser(null);

        $sessionID = $this->objFromFixture(LoginSession::class, 'x1')->ID;
        $session = new Session(['activeLoginSession' => $sessionID]);
        $request = $this->buildRequestMock('/', [], [], null, $session);
        $request->method('getIP')->willReturn('192.168.0.1');
        $middleware = new LoginSessionMiddleware(new Url('no-match'));
        $next = false;
        $middleware->process(
            $request,
            function () use (&$next) {
                $next = true;
            }
        );
        $this->assertTrue($next, "Middleware does not do anything if not logged in");

        // Then log in as member1
        $member1 = $this->objFromFixture(Member::class, 'member1');
        Security::setCurrentUser($member1);
        DBDatetime::set_mock_now('2003-02-15 12:00:00');

        $middleware = new LoginSessionMiddleware(new Url('no-match'));
        $next = false;
        $middleware->process(
            $request,
            function () use (&$next) {
                $next = true;
            }
        );

        $loginSession = $member1->LoginSessions()->first();
        $this->assertEquals(
            $loginSession->LastAccessed,
            '2003-02-15 12:00:00',
            "Middleware updates last accessed of login session when logged in"
        );
        $this->assertEquals(
            $loginSession->IPAddress,
            '192.168.0.1',
            "Middleware updates ip address of login session when logged in"
        );
        $this->assertEquals(
            Security::getCurrentUser(),
            $member1,
            "Middleware keeps member logged in when session is valid"
        );
    }

    public function testMiddlewareSessionRevoked()
    {
        $sessionID = $this->objFromFixture(LoginSession::class, 'x1')->ID;
        $session = new Session(['activeLoginSession' => $sessionID]);
        $request = $this->buildRequestMock('/', [], [], null, $session);
        $request->method('getIP')->willReturn('192.168.0.1');

        // Log in as member1
        $member1 = $this->objFromFixture(Member::class, 'member1');
        Security::setCurrentUser($member1);

        $loginSession = $member1->LoginSessions()->first();
        $loginSession->delete();

        $middleware = new LoginSessionMiddleware(new Url('no-match'));
        $next = false;
        $middleware->process(
            $request,
            function () use (&$next) {
                $next = true;
            }
        );

        $this->assertNull(
            Security::getCurrentUser(),
            "Middleware logs user out if session is revoked"
        );
    }

    public function testMiddlewareSessionWrongMember()
    {
        $sessionID = $this->objFromFixture(LoginSession::class, 'x1')->ID;
        $session = new Session(['activeLoginSession' => $sessionID]);
        $request = $this->buildRequestMock('/', [], [], null, $session);
        $request->method('getIP')->willReturn('192.168.0.1');

        // Log in as member2
        /** @var Member $member2 */
        $member2 = $this->objFromFixture(Member::class, 'member2');
        Security::setCurrentUser($member2);

        $middleware = new LoginSessionMiddleware(new Url('no-match'));
        $next = false;
        $middleware->process(
            $request,
            function () use (&$next) {
                $next = true;
            }
        );

        $this->assertNull(
            Security::getCurrentUser(),
            "Middleware logs user out if session has mismatched member"
        );
    }

    /**
     * Assert the RememberLoginHash for un-revoked LoginSessions are untouched
     */
    public function testOtherDeviceRememberLoginHashUntouched()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member_rmh');
        Security::setCurrentUser($member);

        $session1 = $this->objFromFixture(LoginSession::class, 'rmh1');
        $hash1 = RememberLoginHash::generate($member);
        $hash1->LoginSessionID = $session1->ID;
        $hash1->DeviceID = 'IE2';
        $hash1->write();

        $session2 = $this->objFromFixture(LoginSession::class, 'rmh2');
        $hash2 = RememberLoginHash::generate($member);
        $hash2->LoginSessionID = $session2->ID;
        $hash2->DeviceID = 'C64';
        $hash2->write();

        $deviceFilter = ['DeviceID' => ['IE2', 'C64']];

        $this->assertSame(2, RememberLoginHash::get()->filter($deviceFilter)->count());
        $this->assertSame(
            [
                'Internet Explorer 2',
                'Commodore 64 browser'
            ],
            LoginSession::get()
                ->filter(['ID' => RememberLoginHash::get()->filter($deviceFilter)->column('LoginSessionID')])
                ->column('UserAgent')
        );

        // revoke the 2nd device
        $member->LoginSessions()->find('UserAgent', 'Commodore 64 browser')->delete();

        // "press f5 to refresh" the 2nd device which will trigger the middleware to call IdentityStore logOut()
        $default = Cookie::get('alc_device');
        Cookie::set('alc_device', 'C64');
        $session = new Session(['activeLoginSession' => $session2->ID]);
        $request = $this->buildRequestMock('/', [], [], null, $session);
        $request->method('getIP')->willReturn('192.168.0.1');
        $middleware = new LoginSessionMiddleware();
        $middleware->process($request, function () {
            // noop
        });
        Cookie::set('alc_device', $default);

        $this->assertSame(1, RememberLoginHash::get()->filter($deviceFilter)->count());
        $this->assertSame(
            [
                'Internet Explorer 2'
            ],
            LoginSession::get()
                ->filter(['ID' => RememberLoginHash::get()->filter($deviceFilter)->column('LoginSessionID')])
                ->column('UserAgent')
        );
    }
}
