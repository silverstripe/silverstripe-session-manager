<?php

namespace SilverStripe\SessionManager\Tests\Services;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\SessionManager\Extensions\RememberLoginHashExtension;
use SilverStripe\SessionManager\Models\LoginSession;
use SilverStripe\SessionManager\Services\GarbageCollectionService;

class GarbageCollectionServiceTest extends SapphireTest
{
    protected static $fixture_file = 'GarbageCollectionServiceTest.yml';

    protected static $required_extensions = [
        RememberLoginHash::class => [
            RememberLoginHashExtension::class,
        ],
    ];

    public function testGarbageCollection()
    {
        DBDatetime::set_mock_now('2003-05-16 12:00:00');

        $id1 = $this->objFromFixture(LoginSession::class, 'x1')->ID;
        $id2 = $this->objFromFixture(LoginSession::class, 'x2')->ID;
        $id3 = $this->objFromFixture(LoginSession::class, 'x3')->ID;

        $garbageCollectionService = new GarbageCollectionService();
        $garbageCollectionService->collect();

        $this->assertNull(
            LoginSession::get()->byID($id1),
            "Expired login session is deleted"
        );
        // ExpiryDate for the hash is set to '2003-05-15 10:00:00' => it should be deleted
        $this->assertNull(
            LoginSession::get()->byID($id2),
            "Expired persistent login hash session is deleted"
        );
        // LastAccessed is set to '2004-02-15 10:00:00' and it has no hash => it should not be deleted
        $this->assertNotNull(
            LoginSession::get()->byID($id3),
            "Valid login session is not deleted"
        );
        $this->assertEquals(
            0,
            LoginSession::get()->byID($id3)->LoginHash()->ID,
            "There is no hash but session is still valid"
        );

        DBDatetime::set_mock_now('2005-08-15 12:00:00');

        $garbageCollectionService->collect();

        $this->assertNull(
            LoginSession::get()->byID($id3),
            "Persistent Login session is now deleted"
        );
    }
}
