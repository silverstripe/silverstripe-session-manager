<?php

namespace SilverStripe\SessionManager\Tests\Service;

use SilverStripe\CampaignAdmin\SiteTreeExtension;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Middleware\ConfirmationMiddleware\Url;
use SilverStripe\Control\Session;
use SilverStripe\Control\Tests\HttpRequestMockBuilder;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Control\LoginSessionMiddleware;
use SilverStripe\SessionManager\Extensions\RememberLoginHashExtension;
use SilverStripe\SessionManager\Model\LoginSession;
use SilverStripe\SessionManager\Service\GarbageCollectionService;


class GarbageCollectionServiceTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static $fixture_file = 'GarbageCollectionServiceTest.yml';

    protected static $required_extensions = [
        RememberLoginHash::class => [
            RememberLoginHashExtension::class,
        ],
    ];

    public function testGarbageCollection()
    {
        DBDatetime::set_mock_now('2003-08-15 12:00:00');

        $garbageCollectionService = new GarbageCollectionService();
        $garbageCollectionService->collect();

        $loginSession = LoginSession::get()->byID(1);
        $this->assertNull(
            $loginSession,
            "Expired login session is deleted"
        );
        $loginSession = LoginSession::get()->byID(2);
        $this->assertNull(
            $loginSession,
            "Expired persistent login hash session is deleted"
        );
        $loginSession = LoginSession::get()->byID(3);
        $this->assertNotNull(
            $loginSession,
            "Valid login session is not deleted"
        );
    }
}
