<?php

namespace SilverStripe\SessionManager\Tasks;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\SessionManager\Service\GarbageCollectionService;
use SilverStripe\CronTask\Interfaces\CronTask;

if (!interface_exists(CronTask::class)) {
    return;
}

class GarbageCollectionCronTask implements CronTask
{
    /**
     * run this task every 5 minutes
     *
     * @return string
     */
    public function getSchedule()
    {
        return "*/5 * * * *";
    }

    /**
     * @return void
     */
    public function process()
    {
        $service = Injector::inst()->get(GarbageCollectionService::class);
        $service->collect();
        echo "Garbage collection completed successfully";
    }
}
