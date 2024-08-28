<?php

namespace SilverStripe\SessionManager\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\SessionManager\Models\LoginSession;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class InvalidateAllSessionsTask extends BuildTask
{
    protected static string $commandName = 'InvalidateAllSessions';

    protected string $title = 'Invalidate All Login Sessions Task';

    protected static string $description = 'Removes all login sessions and "remember me" hashes'
                                            . ' (including yours) from the database';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        LoginSession::get()->removeAll();
        RememberLoginHash::get()->removeAll();
        return Command::SUCCESS;
    }
}
