<?php

namespace SilverStripe\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\SessionManager\Services\GarbageCollectionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class GarbageCollectionTask extends BuildTask
{
    protected static string $commandName = 'LoginSessionGarbageCollectionTask';

    protected string $title = 'Login Session Garbage Collection Task';

    protected static string $description = 'Removes expired login sessions and "remember me" hashes from the database';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        GarbageCollectionService::singleton()->collect();
        return Command::SUCCESS;
    }
}
