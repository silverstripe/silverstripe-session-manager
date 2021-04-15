<?php

namespace SilverStripe\SessionManager\Model;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Security\LogInAuthenticationHandler;
use UAParser\Parser;

/**
 * Tracks a login session for a specific user on a specific device.
 *
 * Deleting the LoginSession object for a device will terminate that session for the user.
 *
 * @property DBDatetime $LastAccessed
 * @property string $IPAddress
 * @property string $UserAgent
 * @property bool $Persistent
 * @property integer $MemberID
 * @method Member Member()
 */
class LoginSession extends DataObject
{
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

        // Only the system is allowed to create new LoginSession.
        return false;
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
        if (!$member) {
            $member = Security::getCurrentUser();
        }

        // Allow extensions to overrule permissions
        $extended = $this->extendedCan(__FUNCTION__, $member);
        if ($extended !== null) {
            return $extended;
        }

        // By default, no one can edit a LoginSession because it's not an object users can directly interact with.
        return false;
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
    private function handlePermission(string $funcName, $member): bool
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
        if ($this->MemberID === $member->ID) {
            return true;
        }

        // By default, members can not see other members' sessions
        return false;
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

        return _t(
            __CLASS__ . '.BROWSER_ON_OS',
            "{browser} on {os}.",
            ['browser' => $result->ua->family, 'os' => $result->os->toString()]
        );
    }

    /**
     * @param Member|null $member
     * @param HTTPRequest|null $request
     */
    public static function getCurrentLoginSession(Member $member = null, HTTPRequest $request = null): ?self
    {
        if (!$member) {
            $member = Security::getCurrentUser();
        }

        // Fall back to retrieving request from current Controller if available
        if ($request === null) {
            if (!Controller::has_curr()) {
                throw new InvalidArgumentException(
                    "A HTTPRequest is required to check if this is the currently used LoginSession."
                );
            }

            $request = Controller::curr()->getRequest();
        }

        $loginHandler = Injector::inst()->get(LogInAuthenticationHandler::class);
        $loginSessionID = $request->getSession()->get($loginHandler->getSessionVariable());
        $loginSession = LoginSession::get()->byID($loginSessionID);
        return $loginSession;
    }

    /**
     * @param Member|null $member
     * @param HTTPRequest|null $request
     */
    public function isCurrent(Member $member = null, HTTPRequest $request = null): bool
    {
        $currentLoginSession = static::getCurrentLoginSession($member, $request);
        if (!$currentLoginSession) {
            return false;
        }

        return $this->ID === $currentLoginSession->ID;
    }

    /**
     * @param Member $member
     * @return DataList|LoginSession[]
     */
    public static function getCurrentSessions(Member $member)
    {
        $sessionLifetime = static::getSessionLifetime();
        $maxAge = DBDatetime::now()->getTimestamp() - $sessionLifetime;
        $currentSessions = $member->LoginSessions()->filterAny([
            'Persistent' => 1,
            'LastAccessed:GreaterThan' => date('Y-m-d H:i:s', $maxAge)
        ]);
        return $currentSessions;
    }

    /**
     * @return int
     */
    public static function getSessionLifetime(): int
    {
        if ($lifetime = Session::config()->get('timeout')) {
            return $lifetime;
        }

        return LoginSession::config()->get('default_session_lifetime');
    }
}
