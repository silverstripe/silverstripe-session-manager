<?php

namespace SilverStripe\SessionManager\Tests\Extensions;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\SessionManager\Models\LoginSession;

class LoginSessionTest extends SapphireTest
{
    protected static $fixture_file = '../LoginSessionTest.yml';

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

    public function testOrphanSession()
    {
        $this->logInWithPermission('ADMIN');

        /** @var LoginSession $session */
        $session = $this->objFromFixture(LoginSession::class, 'orphan');

        $this->assertFalse($session->canView(), 'Orphan session can not be viewed');
        $this->assertFalse($session->canEdit(), 'Orphan session can not be edited');
        $this->assertFalse($session->canDelete(), 'Orphan session can not be deleted');
    }

    public function testAnonymousUserDoNotHaveAnyRights()
    {
        $this->logOut();

        /** @var LoginSession $session */
        $session = $this->objFromFixture(LoginSession::class, 'orphan');

        $this->assertFalse($session->canView(), 'Anonymous can not view');
        $this->assertFalse($session->canEdit(), 'Anonymous can not edit');
        $this->assertFalse($session->canDelete(), 'Anonymous can not delete');
    }
}
