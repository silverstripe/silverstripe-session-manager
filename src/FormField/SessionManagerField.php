<?php

namespace SilverStripe\SessionManager\FormField;

use SilverStripe\Admin\SecurityAdmin;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FormField;
use SilverStripe\SessionManager\Control\LoginSessionController;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Model\LoginSession;

class SessionManagerField extends FormField
{
    /**
     * {@inheritDoc}
     *
     * @param string $name Field name
     * @param string|null $title Field title
     * @param int $value Member ID to apply this field to
     */
    public function __construct(string $name, ?string $title, int $value)
    {
        parent::__construct($name, $title, $value);
    }

    public function Field($properties = array())
    {
        return $this->renderWith(self::class);
    }

    /**
     * @return array
     */
    public function getSchemaDataDefaults()
    {
        $defaults = parent::getSchemaDataDefaults();

        if (!$this->value && $this->getForm() && $this->getForm()->getRecord() instanceof Member) {
            $member = $this->getForm()->getRecord();
        } else {
            /** @var Member $member */
            $member = DataObject::get_by_id(Member::class, $this->value);
        }

        return array_merge($defaults, [
            'schema' => [
                'loginSessions' => $this->getLoginSessions($member)
            ],
        ]);
    }

    /**
     * @param Member $member
     * @return array
     */
    protected function getLoginSessions(Member $member)
    {
        $logOutEndpoint = LoginSessionController::singleton()->Link('remove');

        $loginSessions = [];
        /** @var LoginSession $loginSession */
        foreach (LoginSession::getCurrentSessions($member) as $loginSession) {
            $loginSessions[] = [
                'ID' => $loginSession->ID,
                'IPAddress' => $loginSession->IPAddress,
                'UserAgent' => $loginSession->getFriendlyUserAgent(),
                'IsCurrent' => $loginSession->isCurrent(),
                'Persistent' => $loginSession->Persistent,
                'Member' => [
                    'Name' => $loginSession->Member()->Name
                ],
                'Created' => $loginSession->Created,
                'LastAccessed' => $loginSession->LastAccessed,
                'LogOutEndpoint' => $logOutEndpoint,
            ];
        }
        return $loginSessions;
    }
}
