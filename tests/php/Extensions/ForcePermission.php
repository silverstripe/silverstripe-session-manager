<?php

namespace SilverStripe\SessionManager\Tests\Extensions;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Dev\TestOnly;

/**
 * This extension is meant to be applied to LoginSession so we can force permission to scenarios that won't happen
 * natively with a default install of the session manager module.
 */
class ForcePermission extends DataExtension implements TestOnly
{
    private static $canView = null;
    private static $canDelete = null;

    public static function forceCanView($val)
    {
        self::$canView = $val;
    }

    public static function forceCanDelete($val)
    {
        self::$canDelete = $val;
    }

    public static function reset()
    {
        self::$canView = null;
        self::$canDelete = null;
    }

    public function canView($member)
    {
        if (self::$canView !== null) {
            return self::$canView;
        }
    }

    public function canDelete($member)
    {
        if (self::$canDelete !== null) {
            return self::$canDelete;
        }
    }
}
