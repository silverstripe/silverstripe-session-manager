<?php

namespace SilverStripe\SessionManager\Model;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\Security\Security;
use UAParser\Parser;

class LoginSession extends DataObject
{
    use Configurable;

    /**
     * @var array
     */
    private static $db = [
        'LastAccessed' => 'DBDatetime',
        'IPAddress' => 'Varchar(45)',
        'UserAgent' => 'Text',
        'Persistent' => 'Boolean'
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Member' => Member::class
    ];

    /**
     * @var array
     */
    private static $belongs_to = [
        'LoginHash' => RememberLoginHash::class
    ];

    /**
     * @var array
     */
    private static $indexes = [
        'LastAccessed' => true
    ];

    /**
     * @var string
     */
    private static $table_name = 'LoginSession';

    /**
     * @var string
     */
    private static $default_sort = 'LastAccessed DESC';

    /**
     * @var array
     */
    private static $summary_fields = [
        'IPAddress' => 'IP Address',
        'LastAccessed' => 'Last Accessed',
        'Created' => 'Signed In',
        'FriendlyUserAgent' => 'User Agent'
    ];

    /**
     * @var array
     */
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

    /**
     * @param Member $member
     * @param array $context
     * @return boolean
     */
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

    /**
     * @param Member $member
     * @return boolean
     */
    public function canView($member = null)
    {
        return $this->handlePermission(__FUNCTION__, $member);
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canEdit($member = null)
    {
        return $this->handlePermission(__FUNCTION__, $member);
    }

    /**
     * @param Member $member
     * @return boolean
     */
    public function canDelete($member = null)
    {
        return $this->handlePermission(__FUNCTION__, $member);
    }

    /**
     * @param string $funcName
     * @param Member $member
     * @return bool
     */
    public function handlePermission(string $funcName, $member): bool
    {
        if (!$member) {
            $member = Security::getCurrentUser();
        }

        // Allow extensions to overrule permissions
        $extended = $this->extendedCan($funcName, $member);
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
     * @return LoginSession|null
     */
    public static function find(Member $member, HTTPRequest $request): ?LoginSession
    {
        return static::get()->filter([
            'IPAddress' => $request->getIP(),
            'UserAgent' => $request->getHeader('User-Agent'),
            'MemberID' => $member->ID,
            'Persistent' => 1
        ])->first();
    }

    /**
     * @param Member $member
     * @param boolean $persistent
     * @param HTTPRequest $request
     * @return LoginSession
     */
    public static function generate(Member $member, bool $persistent, HTTPRequest $request): LoginSession
    {
        $session = static::create()->update([
            'LastAccessed' => DBDatetime::now()->Rfc2822(),
            'IPAddress' => $request->getIP(),
            'UserAgent' => $request->getHeader('User-Agent'),
            'MemberID' => $member->ID,
            'Persistent' => intval($persistent)
        ]);
        $session->write();

        return $session;
    }

    /**
     * @return string
     */
    public function getFriendlyUserAgent(): string
    {
        if (!$this->UserAgent) {
            return '';
        }

        $parser = Parser::create();
        $result = $parser->parse($this->UserAgent);

        return sprintf('%s on %s', $result->ua->family, $result->os->toString());
    }
}
