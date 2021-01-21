<?php

namespace SilverStripe\SessionManager\Control;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Middleware\HTTPMiddleware;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\Connect\DatabaseException;
use SilverStripe\SessionManager\Service\GarbageCollectionService;

class GarbageCollectionMiddleware implements HTTPMiddleware
{
    use Configurable;

    /**
     * @var int
     * @config
     */
    private static $probability = 50;

    public function process(HTTPRequest $request, callable $delegate)
    {
        if (mt_rand(1, $this->config()->probability) === 1) {
            try {
                $service = Injector::inst()->get(GarbageCollectionService::class);
                $service->collect();
            } catch (DatabaseException $e) {
                // Database isn't ready, carry on.
            }
        }

        return $delegate($request);
    }
}
