<?php

namespace SilverStripe\SessionManager\Tests\Control;

use SilverStripe\Control\Middleware\ConfirmationMiddleware\Url;
use SilverStripe\Control\Session;
use SilverStripe\Control\Tests\HttpRequestMockBuilder;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Control\LoginSessionMiddleware;
use SilverStripe\SessionManager\Model\LoginSession;

class LoginSessionMiddlewareTest extends SapphireTest
{
    use HttpRequestMockBuilder;

    protected $usesDatabase = true;

    protected static $fixture_file = 'LoginSessionMiddlewareTest.yml';

    public function testMiddlewareUpdatesLoginSession()
    {
        // log out
        Security::setCurrentUser(null);

        $sessionID = $this->idFromFixture(LoginSession::class, '1');
        $session = new Session(['activeLoginSession' => $sessionID]);
        $request = $this->buildRequestMock('/', [], [], null, $session);
        $request->method('getIP')->willReturn('192.168.0.1');

        $middleware = new LoginSessionMiddleware(new Url('no-match'));
        $next = false;
        $middleware->process(
            $request,
            static function () use (&$next) {
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
            static function () use (&$next) {
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
        $sessionID = $this->idFromFixture(LoginSession::class, '1');
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
            static function () use (&$next) {
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
        $sessionID = $this->idFromFixture(LoginSession::class, '1');
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
            static function () use (&$next) {
                $next = true;
            }
        );

        $this->assertNull(
            Security::getCurrentUser(),
            "Middleware logs user out if session has mismatched member"
        );
    }
}
