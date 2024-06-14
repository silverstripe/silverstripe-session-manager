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
        ForcePermission::$canView = $val;
    }

    public static function forceCanDelete($val)
    {
        ForcePermission::$canDelete = $val;
    }

    public static function reset()
    {
        ForcePermission::$canView = null;
        ForcePermission::$canDelete = null;
    }

    public function canView($member)
    {
        if (ForcePermission::$canView !== null) {
            return ForcePermission::$canView;
        }
    }

    public function canDelete($member)
    {
        if (ForcePermission::$canDelete !== null) {
            return ForcePermission::$canDelete;
        }
    }
}
