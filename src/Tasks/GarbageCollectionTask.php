<?php

namespace SilverStripe\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\SessionManager\Services\GarbageCollectionService;

class GarbageCollectionTask extends BuildTask
{
    /**
     * @var string
     */
    private static $segment = 'LoginSessionGarbageCollectionTask';

    /**
     * @var string
     */
    protected $title = 'Login Session Garbage Collection Task';

    /**
     * @var string
     */
    protected $description = 'Removes expired login sessions and “remember me” hashes from the database';

    /**
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        GarbageCollectionService::singleton()->collect();
        echo "Garbage collection completed successfully\n";
    }
}
