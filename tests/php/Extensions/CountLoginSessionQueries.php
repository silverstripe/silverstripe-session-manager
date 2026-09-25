<?php

namespace SilverStripe\SessionManager\Tests\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

/**
 * This extension is meant to be applied to LoginSession so tests can count how often it is queried.
 */
class CountLoginSessionQueries extends Extension implements TestOnly
{
    public static int $count = 0;

    protected function augmentSQL()
    {
        CountLoginSessionQueries::$count++;
    }
}
