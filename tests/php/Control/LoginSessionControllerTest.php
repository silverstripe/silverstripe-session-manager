<?php

namespace SilverStripe\SessionManager\Tests\Control;

use HttpRequest;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Middleware\ConfirmationMiddleware\Url;
use SilverStripe\Control\Session;
use SilverStripe\Control\Tests\HttpRequestMockBuilder;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Security\SecurityToken;
use SilverStripe\SessionManager\Control\LoginSessionMiddleware;
use SilverStripe\SessionManager\Controllers\LoginSessionController;
use SilverStripe\SessionManager\Models\LoginSession;

class LoginSessionControllerTest extends FunctionalTest
{
    use HttpRequestMockBuilder;

    protected static $fixture_file = '../LoginSessionTest.yml';

    /** @var Session */
    private $session;

    /** @var HttpRequest */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $sessionID = $this->idFromFixture(LoginSession::class, 'x1');
        $this->session = new Session(['activeLoginSession' => $sessionID]);
        $this->request = $this->buildRequestMock('/', [], [], null, $this->session);
        $this->request->method('getIP')->willReturn('192.168.0.1');

        $memberID = $this->idFromFixture(Member::class, 'owner');
        $this->logInAs($memberID);
        SecurityToken::enable();
        $this->session->set('SecurityID', SecurityToken::inst()->getValue());
    }

    public function testRemove()
    {
        $sessionID = $this->idFromFixture(LoginSession::class, 'x3');
        $token = SecurityToken::inst()->getValue();
        $response = Director::test(
            "loginsession/$sessionID?SecurityID=$token",
            null,
            $this->session,
            'DELETE'
        );
        $this->assertResponse(
            $response,
            200,
            'Successfully removed session.',
            'User can invalidate their session with a valid CSRF token'
        );

        $this->assertEmpty(
            LoginSession::get()->byID($sessionID),
            'x3 LoginSession has been deleted'
        );
    }

    public function testNoUser()
    {
        $sessionID = $this->idFromFixture(LoginSession::class, 'x3');
        $token = SecurityToken::inst()->getValue();
        $this->logOut();
        $response = Director::test(
            "loginsession/$sessionID?SecurityID=$token",
            null,
            $this->session,
            'DELETE'
        );
        $this->assertResponse(
            $response,
            401,
            'Your session has expired',
            'Anonymous user can not invalidate session'
        );
        $this->assertNotEmpty(
            LoginSession::get()->byID($sessionID),
            'x3 LoginSession has NOT been deleted'
        );
    }

    public function testBadSecurityID()
    {
        $sessionID = $this->idFromFixture(LoginSession::class, 'x3');
        $token = 'whats-a-CSRF-token';
        $response = Director::test(
            "loginsession/$sessionID?SecurityID=$token",
            null,
            $this->session,
            'DELETE'
        );
        $this->assertResponse(
            $response,
            400,
            'Invalid request',
            'A valid CSRF token must be provided to invalidate a session'
        );
        $this->assertNotEmpty(
            LoginSession::get()->byID($sessionID),
            'x3 LoginSession has NOT been deleted'
        );
    }

    public function badIDs()
    {
        return [
            'No ID' => [''],
            'None nemuric ID' => ['three']
        ];
    }

    /**
     * @param $id
     * @dataProvider badIDs
     */
    public function testBadID($sessionID)
    {
        $token = SecurityToken::inst()->getValue();
        $response = Director::test(
            "loginsession/$sessionID?SecurityID=$token",
            null,
            $this->session,
            'DELETE'
        );
        $this->assertResponse(
            $response,
            400,
            'Invalid request',
            'A valid LoginSesseion ID must be provided to invalidate a session'
        );
    }

    public function testNonExistentID()
    {
        $sessionID = 99999;
        $token = SecurityToken::inst()->getValue();
        $response = Director::test(
            "loginsession/$sessionID?SecurityID=$token",
            null,
            $this->session,
            'DELETE'
        );
        $this->assertResponse(
            $response,
            404,
            'This session could not be found or is no longer active.',
            'Cannot invalidate a LoginSession for a non-existent ID'
        );
    }

    public function testOtherUserSessionID()
    {
        $sessionID = $this->idFromFixture(LoginSession::class, 'x2');
        $token = SecurityToken::inst()->getValue();
        $response = Director::test(
            "loginsession/$sessionID?SecurityID=$token",
            null,
            $this->session,
            'DELETE'
        );

        $this->assertResponse(
            $response,
            404,
            'This session could not be found or is no longer active.',
            'Can not invalidate an other user\'s session'
        );
        $this->assertNotEmpty(
            LoginSession::get()->byID($sessionID),
            'x2 LoginSession has NOT been deleted'
        );
    }

    private function assertResponse(HTTPResponse $response, $code, $toast, $message)
    {
        $this->assertEquals(
            $code,
            $response->getStatusCode(),
            $message
        );
        $body = json_decode($response->getBody() ?? '', true);
        $this->assertEquals(
            ['message' => $toast],
            $body
        );
        $this->assertStringContainsString(
            'no-store',
            $response->getHeader('Cache-Control'),
            'Login Session Controller response is never cached',
            true
        );
    }
}
