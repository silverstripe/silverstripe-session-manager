<?php

namespace SilverStripe\SessionManager\Extensions;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Session;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_Base;
use SilverStripe\MFA\Authenticator\ChangePasswordHandler;
use SilverStripe\MFA\Exception\InvalidMethodException;
use SilverStripe\MFA\FormField\RegisteredMFAMethodListField;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\FormField\SessionManagerField;
use SilverStripe\SessionManager\Forms\GridFieldRevokeLoginSessionAction;
use SilverStripe\SessionManager\Model\LoginSession;

class MemberExtension extends Extension implements PermissionProvider
{
    public const SESSION_MANAGER_ADMINISTER_SESSIONS = 'SESSION_MANAGER_ADMINISTER_SESSIONS';

    private static $has_many = [
        'LoginSessions' => LoginSession::class
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        # todo: remove start \\
        $fields->removeByName('LoginSessions');

        $sessionLifetime = $this->getSessionLifetime();
        $maxAge = DBDatetime::now()->getTimestamp() - $sessionLifetime;
        $currentSessions = $this->owner->LoginSessions()->filterAny([
            'Persistent' => true,
            'LastAccessed:GreaterThan' => date('Y-m-d H:i:s', $maxAge)
        ]);

        $fields->addFieldToTab(
            'Root.Sessions',
            GridField::create(
                'LoginSessions',
                'Sessions',
                $currentSessions,
                GridFieldConfig_Base::create()
                    ->addComponent(GridFieldRevokeLoginSessionAction::create())
            )
        );
        # todo: remove end //

        $fields->removeByName(['SessionManagerField']);

        if (!$this->owner->exists() || !$this->currentUserCanViewSessionManagerConfig()) {
            return $fields;
        }

        $fields->addFieldToTab(
            'Root.Main',
            $methodListField = SessionManagerField::create(
                'SessionManagerField',
                _t(__CLASS__ . '.SESSION_MANAGER_SETTINGS_FIELD_LABEL', 'Session Manager Settings'),
                $this->owner->ID
            )
        );

        if (!$this->currentUserCanEditSessionManagerConfig()) {
            $methodListField->setReadonly(true);
        }

        return $fields;
    }

    /**
     * @return int
     */
    protected function getSessionLifetime()
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
    public function currentUserCanViewSessionManagerConfig(): bool
    {
        return (Permission::check(self::SESSION_MANAGER_ADMINISTER_SESSIONS)
            || $this->currentUserCanEditSessionManagerConfig());
    }

    /**
     * Determines whether the logged in user has sufficient permission to modify the SessionManager config for this Member.
     * Note that this is different from being able to _reset_ the config (which administrators can do).
     *
     * @return bool
     */
    public function currentUserCanEditSessionManagerConfig(): bool
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
            'View/reset SessionManager configuration for other members'
        );

        $category = _t(
            'SilverStripe\\Security\\Permission.PERMISSIONS_CATEGORY',
            'Roles and access permissions'
        );

        $description = _t(
            __CLASS__ . '.SESSION_MANAGER_PERMISSION_DESCRIPTION',
            'Ability to view and reset registered SessionManager methods for other members.'
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
