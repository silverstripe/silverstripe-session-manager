<?php

namespace SilverStripe\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\SessionManager\Service\GarbageCollectionService;

/**
 * Class GarbageCollectionTask
 * @package SilverStripe\Tasks
 * @codeCoverageIgnore
 */
class GarbageCollectionTask extends BuildTask
{
    private static $segment = 'LoginSessionGarbageCollectionTask';

    protected $title = 'Login Session Garbage Collection Task';

    protected $description = 'Removes expired login sessions and “remember me” hashes from the database';

    public function run($request)
    {
        GarbageCollectionService::singleton()->collect();
        echo "Garbage collection completed successfully";
    }
}
