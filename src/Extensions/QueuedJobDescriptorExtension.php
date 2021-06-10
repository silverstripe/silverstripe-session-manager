<?php

namespace SilverStripe\SessionManager\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\SessionManager\Jobs\GarbageCollectionJob;

class QueuedJobDescriptorExtension extends Extension
{
    /**
     * Called on dev/build by DatabaseAdmin
     */
    public function onAfterBuild(): void
    {
        GarbageCollectionJob::singleton()->requireDefaultJob();
    }
}
