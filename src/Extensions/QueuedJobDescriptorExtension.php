<?php

namespace SilverStripe\SessionManager\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\SessionManager\Jobs\GarbageCollectionJob;
use Symbiote\QueuedJobs\DataObjects\QueuedJobDescriptor;

/**
 * @extends Extension<QueuedJobDescriptor>
 */
class QueuedJobDescriptorExtension extends Extension
{
    /**
     * Called by DbBuild
     */
    protected function onAfterBuild(): void
    {
        GarbageCollectionJob::singleton()->requireDefaultJob();
    }
}
