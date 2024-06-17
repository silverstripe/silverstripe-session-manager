<?php

namespace SilverStripe\SessionManager\Jobs;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SessionManager\Services\GarbageCollectionService;
use Symbiote\QueuedJobs\DataObjects\QueuedJobDescriptor;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJobService;

if (!class_exists(QueuedJobDescriptor::class)) {
    return;
}

class GarbageCollectionJob extends AbstractQueuedJob
{
    use Configurable;
    use Injectable;

    /**
     * Number of seconds between job runs. Defaults to 1 day.
     *
     * @var int
     */
    private static $seconds_between_jobs = 86400;

    /**
     * @return string
     */
    public function getTitle()
    {
        return _t(__CLASS__ . '.TITLE', 'Session manager garbage collection');
    }

    public function process()
    {
        $this->queueNextJob();
        GarbageCollectionService::singleton()->collect();
        $this->isComplete = true;
    }

    public function requireDefaultJob(): void
    {
        $filter = [
            'Implementation' => GarbageCollectionJob::class,
            'JobStatus' => [
                QueuedJob::STATUS_NEW,
                QueuedJob::STATUS_INIT,
                QueuedJob::STATUS_RUN
            ]
        ];
        if (QueuedJobDescriptor::get()->filter($filter)->count() > 0) {
            return;
        }
        $this->queueNextJob();
    }

    private function queueNextJob(): void
    {
        $timestamp = time() + static::config()->get('seconds_between_jobs');
        QueuedJobService::singleton()->queueJob(
            Injector::inst()->create(GarbageCollectionJob::class),
            DBDatetime::create()->setValue($timestamp)->Rfc2822()
        );
    }
}
