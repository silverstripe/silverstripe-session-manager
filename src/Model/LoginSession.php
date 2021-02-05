<?php

namespace SilverStripe\SessionManager\Model;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\Security\Security;
use UAParser\Parser;

class LoginSession extends DataObject
{
    private static $db = [
        'LastAccessed' => 'DBDatetime',
        'IPAddress' => 'Varchar(45)',
        'UserAgent' => 'Text',
        'Persistent' => 'Boolean'
    ];

    private static $has_one = [
        'Member' => Member::class
    ];

    private static $belongs_to = [
        'LoginHash' => RememberLoginHash::class
    ];

    private static $indexes = [
        'LastAccessed' => true
    ];

    private static $table_name = 'LoginSession';

    private static $default_sort = 'LastAccessed DESC';

    private static $summary_fields = [
        'IPAddress' => 'IP Address',
        'LastAccessed' => 'Last Accessed',
        'Created' => 'Signed In',
        'FriendlyUserAgent' => 'User Agent'
    ];

    private static $searchable_fields = [
        'IPAddress',
    ];

    /**
     * The length of time a session can be inactive for before it is discarded and the
     * user is logged out
     *
     * @config
     * @var int
     */
    private static $default_session_lifetime = 3600;

    public function canCreate($member = null, $context = [])
    {
        if (!$member) {
            $member = Security::getCurrentUser();
        }

        // Allow extensions to overrule permissions
        $extended = $this->extendedCan(__FUNCTION__, $member, $context);
        if ($extended !== null) {
            return $extended;
        }

        // Must be logged in to act on login sessions
        if (!$member) {
            return false;
        }

        // Any user with access to SecurityAdmin can create a session
        // @todo Does this even make sense? When would you non-programatically create a session?
        return Permission::checkMember($member, 'CMS_ACCESS_SecurityAdmin');
    }

    public function canView($member = null)
    {
        return $this->handlePermission(__FUNCTION__, $member);
    }

    public function canEdit($member = null)
    {
        return $this->handlePermission(__FUNCTION__, $member);
    }

    public function canDelete($member = null)
    {
        return $this->handlePermission(__FUNCTION__, $member);
    }

    /**
     * @param string $fn Permission method being called - one of canView/canEdit/canDelete
     * @param mixed $member
     * @return bool
     */
    public function handlePermission($fn, $member)
    {
        if (!$member) {
            $member = Security::getCurrentUser();
        }

        // Allow extensions to overrule permissions
        $extended = $this->extendedCan($fn, $member);
        if ($extended !== null) {
            return $extended;
        }

        // Must be logged in to act on login sessions
        if (!$member) {
            return false;
        }

        // Members can manage their own sessions
        if ($this->ID == $member->ID) {
            return true;
        }

        // Access to SecurityAdmin implies session management permissions
        return Permission::checkMember($member, 'CMS_ACCESS_SecurityAdmin');
    }

    /**
     * @param Member $member
     * @param HTTPRequest $request
     * @return static|null
     */
    public static function find(Member $member, HTTPRequest $request)
    {
        $session = static::get()->filter([
            'IPAddress' => $request->getIP(),
            'UserAgent' => $request->getHeader('User-Agent'),
            'MemberID' => $member->ID,
            'Persistent' => true
        ])->first();

        return $session;
    }

    /**
     * @param Member $member
     * @param boolean $persistent
     * @param HTTPRequest $request
     * @return static
     */
    public static function generate(Member $member, bool $persistent, HTTPRequest $request)
    {
        $session = static::create()->update([
            'LastAccessed' => DBDatetime::now()->Rfc2822(),
            'IPAddress' => $request->getIP(),
            'UserAgent' => $request->getHeader('User-Agent'),
            'MemberID' => $member->ID,
            'Persistent' => $persistent
        ]);
        $session->write();

        return $session;
    }

    /**
     * @return string
     */
    public function getFriendlyUserAgent()
    {
        if (!$this->UserAgent) {
            return '';
        }

        $parser = Parser::create();
        $result = $parser->parse($this->UserAgent);

        return sprintf('%s on %s', $result->ua->family, $result->os->toString());
    }
}
