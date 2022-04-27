<?php

namespace SilverStripe\SessionManager\Tasks;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\SessionManager\Models\LoginSession;

class InvalidateAllSessionsTask extends BuildTask
{
    private static string $segment = 'InvalidateAllSessions';

    /**
     * @var string
     */
    protected $title = 'Invalidate All Login Sessions Task';

    /**
     * @var string
     */
    protected $description = 'Removes all login sessions and "remember me" hashes (including yours) from the database';

    /**
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        LoginSession::get()->removeAll();
        RememberLoginHash::get()->removeAll();
        echo "Session removal completed successfully\n";
    }
}
