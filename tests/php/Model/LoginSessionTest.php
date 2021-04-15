<?php

namespace SilverStripe\SessionManager\Tests\Extension;

use SilverStripe\Control\Middleware\ConfirmationMiddleware\Url;
use SilverStripe\Control\Session;
use SilverStripe\Control\Tests\HttpRequestMockBuilder;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Control\LoginSessionMiddleware;
use SilverStripe\SessionManager\FormField\SessionManagerField;
use SilverStripe\SessionManager\Model\LoginSession;

class LoginSessionTest extends SapphireTest
{
    protected static $fixture_file = '../LoginSessionTest.yml';

    public function setUp()
    {
        return parent::setUp();
    }

    public function testCanCreate()
    {
        $this->logInWithPermission('ADMIN');

        $this->assertFalse(
            LoginSession::singleton()->canCreate(),
            'No one is allowed to directly create a LoginSession'
        );
    }

    public function testCanView()
    {
        $this->logInWithPermission('ADMIN');

        /** @var LoginSession $session */
        $session = $this->objFromFixture(LoginSession::class, 'x1');

        $this->assertFalse(
            $session->canView(),
            'By default, only a LoginSession\'s owner can view it'
        );

        $this->assertTrue(
            $session->canView($session->Member()),
            'A LoginSession\'s owner can view it'
        );
    }

    public function testCanEdit()
    {
        $this->logInWithPermission('ADMIN');

        /** @var LoginSession $session */
        $session = $this->objFromFixture(LoginSession::class, 'x1');

        $this->assertFalse(
            $session->canEdit(),
            'No one is allowed to directly edit a LoginSession'
        );

        $this->assertFalse(
            $session->canEdit($session->Member()),
            'A LoginSession\'s owner can not edit it'
        );
    }

    public function testCanDelete()
    {
        $this->logInWithPermission('ADMIN');

        /** @var LoginSession $session */
        $session = $this->objFromFixture(LoginSession::class, 'x1');

        $this->assertFalse(
            $session->canView(),
            'By default, only a LoginSession\'s owner can delete it'
        );

        $this->assertTrue(
            $session->canView($session->Member()),
            'A LoginSession\'s owner can delete it'
        );
    }
}
