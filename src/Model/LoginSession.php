<?php

namespace Kinglozzer\SessionManager\Model;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Security\RememberLoginHash;
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
    public static function generate(Member $member, $persistent = false, HTTPRequest $request)
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
        $parser = Parser::create();
        $result = $parser->parse($this->UserAgent);

        return sprintf('%s on %s', $result->ua->family, $result->os->toString());
    }
}
