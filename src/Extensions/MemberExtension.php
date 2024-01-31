<?php

namespace SilverStripe\SessionManager\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SilverStripe\SessionManager\FormFields\SessionManagerField;
use SilverStripe\SessionManager\Models\LoginSession;

/**
 * Augment `Member` to allow relationship to the LoginSession DataObject
 *
 * @method HasManyList<LoginSession> LoginSessions()
 *
 * @extends DataExtension<Member>
 */
class MemberExtension extends DataExtension
{
    /**
     * URL to the user help abot managing session
     * @var string
     * @config
     */
    private static $session_login_help_url =
        'https://userhelp.silverstripe.org/en/4/managing_your_website/session_manager';

    /**
     * @var array
     */
    private static $has_many = [
        'LoginSessions' => LoginSession::class
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('LoginSessions');

        $member = $this->getOwner();
        if (!$member->exists()) {
            // We're creating a new member
            return;
        }

        $session = $member->LoginSessions()->first();
        if (!$session || !$session->canView()) {
            // We're assuming that the first session permission are representative of the other sessions
            return;
        }

        $helpUrl = $member::config()->get('session_login_help_url');

        $fields->addFieldToTab(
            'Root.Main',
            SessionManagerField::create(
                'LoginSessions',
                _t(__CLASS__ . '.LOGIN_SESSIONS', 'Login sessions'),
                $this->owner->ID,
                $helpUrl ? _t(__CLASS__ . '.LEARN_MORE', 'Learn more') : '',
                $helpUrl ?: ''
            )->setReadonly(!$session->canDelete())
        );
    }
}
