<?php

namespace SilverStripe\SessionManager\FormField;

use SilverStripe\Admin\SecurityAdmin;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class SessionManagerField extends FormField
{
    /**
     * {@inheritDoc}
     *
     * @param string      $name  Field name
     * @param string|null $title Field title
     * @param int         $value Member ID to apply this field to
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

        return array_merge($defaults, []);
    }

    /**
     * Get the registered backup method (if any) from the currently logged in user.
     *
     * @return RegisteredMethod|null
     */
    protected function getBackupMethod(): ?RegisteredMethod
    {
        $backupMethod = MethodRegistry::singleton()->getBackupMethod();
        return RegisteredMethodManager::singleton()->getFromMember(Security::getCurrentUser(), $backupMethod);
    }
}
