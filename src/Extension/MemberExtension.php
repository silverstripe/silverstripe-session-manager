<?php

namespace SilverStripe\SessionManager\Extensions;

use SilverStripe\Control\Session;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_Base;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\FormField\SessionManagerField;
use SilverStripe\SessionManager\Forms\GridFieldRevokeLoginSessionAction;
use SilverStripe\SessionManager\Model\LoginSession;

class MemberExtension extends Extension implements PermissionProvider
{
    public const SESSION_MANAGER_ADMINISTER_SESSIONS = 'SESSION_MANAGER_ADMINISTER_SESSIONS';

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
        $fields->removeByName(['LoginSessions', 'SessionManagerField']);

        if (!$this->owner->exists() || !$this->currentUserCanViewSessions()) {
            return $fields;
        }

        $fields->addFieldToTab(
            'Root.Main',
            $sessionManagerField = SessionManagerField::create(
                'SessionManagerField',
                _t(__CLASS__ . '.SESSION_MANAGER_SETTINGS_FIELD_LABEL', 'Authenticated devices'),
                $this->owner->ID
            )
        );

        if (!$this->currentUserCanEditSessions()) {
            $sessionManagerField->setReadonly(true);
        }

        return $fields;
    }

    /**
     * @return int
     */
    private function getSessionLifetime(): int
    {
        if ($lifetime = Session::config()->get('timeout')) {
            return $lifetime;
        }

        return LoginSession::config()->get('default_session_lifetime');
    }

    /**
     * Determines whether the logged in user has sufficient permission to see the SessionManager config for this Member.
     *
     * @return bool
     */
    public function currentUserCanViewSessions(): bool
    {
        return (Permission::check(self::SESSION_MANAGER_ADMINISTER_SESSIONS)
            || $this->currentUserCanEditSessions());
    }

    /**
     * Determines whether the logged in user has sufficient permission
     * to modify the SessionManager config for this Member.
     * Note that this is different from being able to _reset_ the config (which administrators can do).
     *
     * @return bool
     */
    public function currentUserCanEditSessions(): bool
    {
        return (Security::getCurrentUser() && Security::getCurrentUser()->ID === $this->owner->ID);
    }

    /**
     * Provides the SessionManager view/reset permission for selection in the permission list in the CMS.
     *
     * @return array
     */
    public function providePermissions(): array
    {
        $label = _t(
            __CLASS__ . '.SESSION_MANAGER_PERMISSION_LABEL',
            'View/purge login sessions for other members'
        );

        $category = _t(
            'SilverStripe\\Security\\Permission.PERMISSIONS_CATEGORY',
            'Roles and access permissions'
        );

        $description = _t(
            __CLASS__ . '.SESSION_MANAGER_PERMISSION_DESCRIPTION',
            'Ability to view and purge active login sessions for other members.'
            . ' Requires the "Access to \'Security\' section" permission.'
        );

        return [
            self::SESSION_MANAGER_ADMINISTER_SESSIONS => [
                'name' => $label,
                'category' => $category,
                'help' => $description,
                'sort' => 200,
            ],
        ];
    }
}
