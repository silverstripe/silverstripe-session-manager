<?php

use Kinglozzer\SessionManager\Service\GarbageCollectionService;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;

class GarbageCollectionTask extends BuildTask
{
    private static $segment = 'LoginSessionGarbageCollectionTask';

    protected $title = 'Login Session Garbage Collection Task';

    protected $description = 'Removes expired login sessions and “remember me” hashes from the database';

    public function run($request)
    {
        $service = Injector::inst()->get(GarbageCollectionService::class);
        $service->collect();
        echo "Garbage collection completed successfully";
    }
}
