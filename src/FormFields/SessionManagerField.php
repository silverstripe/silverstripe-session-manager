<?php

namespace SilverStripe\SessionManager\FormFields;

use SilverStripe\Control\Director;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\SessionManager\Controllers\LoginSessionController;
use SilverStripe\SessionManager\Models\LoginSession;
use SilverStripe\View\ViewableData;

class SessionManagerField extends FormField
{
    /**
     * @var string
     */
    private $titleLinkText = '';

    /**
     * @var string
     */
    private $titleLinkHref = '';

    /**
     * {@inheritDoc}
     *
     * @param string $name Field name
     * @param null|string|ViewableData $title Field title
     * @param mixed $value Member ID to apply this field to
     * @param string $titleLinkText Title link text
     * @param string $titleLinkHref Title link href
     */
    public function __construct(
        string $name,
        $title = null,
        $value = null,
        string $titleLinkText = '',
        string $titleLinkHref = ''
    ) {
        parent::__construct($name, $title, $value);
        $this->titleLinkText = $titleLinkText;
        $this->titleLinkHref = $titleLinkHref;
    }

    /**
     * Returns the field titleLinkText.
     *
     * @return string
     */
    public function getTitleLinkText(): string
    {
        return $this->titleLinkText;
    }

    /**
     * Set the field titleLinkText.
     *
     * @param string $titleLinkText
     * @return $this
     */
    public function setTitleLinkText(string $titleLinkText)
    {
        $this->titleLinkText = $titleLinkText;
        return $this;
    }

    /**
     * Returns the field titleLinkHref.
     *
     * @return string
     */
    public function getTitleLinkHref(): string
    {
        return $this->titleLinkHref;
    }

    /**
     * Set the field titleLinkHref.
     *
     * @param string $titleLinkHref
     * @return $this
     */
    public function setTitleLinkHref(string $titleLinkHref)
    {
        $this->titleLinkHref = $titleLinkHref;
        return $this;
    }

    public function Field($properties = array())
    {
        return $this->renderWith(SessionManagerField::class);
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
        $logOutEndpoint = LoginSessionController::singleton()->Link();

        $loginSessions = [];
        foreach (LoginSession::getCurrentSessions($member) as $loginSession) {
            if (!$loginSession->canView()) {
                continue;
            }


            $loginSessions[] = [
                'ID' => $loginSession->ID,
                'IPAddress' => $loginSession->IPAddress,
                'UserAgent' => $loginSession->getFriendlyUserAgent(),
                'IsCurrent' => $loginSession->isCurrent(),
                'Persistent' => $loginSession->Persistent,
                'Member' => [
                    'Name' => Member::get_by_id($loginSession->MemberID)->Name ?? ''
                ],
                'Created' => $this->addUtcOffset($loginSession->Created),
                'LastAccessed' => $this->addUtcOffset($loginSession->LastAccessed),
                'LogOutEndpoint' => $logOutEndpoint,
            ];
        }
        return $loginSessions;
    }

    /**
     * Will suffix a timezone offset, based on the timezone the server is configured for
     * e.g.'+13:00' if server timezone is set to Pacific/Auckland
     *
     * @param string $mysqlDatetime
     * @return string
     */
    private function addUtcOffset(string $mysqlDatetime)
    {
        return $mysqlDatetime . date('P');
    }
}
