<?php

namespace SilverStripe\SessionManager\Tests\Extensions;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;
use SilverStripe\SessionManager\FormFields\SessionManagerField;
use SilverStripe\SessionManager\Models\LoginSession;

class MemberExtensionTest extends SapphireTest
{
    protected static $fixture_file = '../LoginSessionTest.yml';

    protected static $required_extensions = [
        LoginSession::class => [
            ForcePermission::class
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        ForcePermission::reset();
    }

    private function findField(Member $member): ?SessionManagerField
    {
        $fields = $member->getCMSFields();
        return $fields->fieldByName('Root.Main.LoginSessions');
    }

    public function testNoFieldForNewMember()
    {
        $this->logInWithPermission('ADMIN');
        $member = Member::create();
        $field = $this->findField($member);
        $this->assertNull($field, 'Unsaved member form does not show LoginSessionField');
    }

    public function testOwnerCanTerminateTheirSession()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'owner');
        $this->logInAs($member);

        $field = $this->findField($member);
        $this->assertNotNull($field, 'Member can see the LoginSessionField for their profile');
        $this->assertFalse($field->isReadonly(), 'Member can terminate their LoginSession');
    }

    public function testNoInteractionWithOtherUserSession()
    {
        $this->logInWithPermission('ADMIN');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'other');
        $field = $this->findField($member);
        $this->assertNull(
            $field,
            'By default, user can not see the LoginSessionField when viewing another user\'s profile '
        );
    }

    public function testNoLoginSessionFieldWithoutSession()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sessionless');
        ForcePermission::forceCanView(true);
        $this->logInWithPermission('ADMIN');

        $field = $this->findField($member);
        $this->assertNull(
            $field,
            'LoginSessionField is not shown for Members without any LoginSession object'
        );
    }

    public function testLoginSessionFieldWithExtendedPermission()
    {
        $this->logInWithPermission('ADMIN');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'other');
        ForcePermission::forceCanView(true);

        $field = $this->findField($member);
        $this->assertNotNull(
            $field,
            'Member can see the LoginSessionField if they have been granted view right on LoginSession'
        );
        $this->assertTrue(
            $field->isReadonly(),
            'LoginSessionField is readonly if member does not have delete right on login session'
        );

        ForcePermission::forceCanDelete(true);
        $field = $this->findField($member);
        $this->assertFalse(
            $field->isReadonly(),
            'LoginSessionField is not readonly if member has delete right on login session'
        );
    }
}
