<?php

namespace SilverStripe\SessionManager\Service;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\SessionManager\Model\LoginSession;

class GarbageCollectionService
{
    use Injectable;

    /**
     * Delete expired LoginSession and RememberLoginHash records
     */
    public function collect(): void
    {
        $this->collectExpiredSessions();
        $this->collectImplicitlyExpiredSessions();
        $this->collectExpiredLoginHashes();
    }

    /**
     * Collect all non-persistent LoginSession records that are older than the session lifetime
     */
    private function collectExpiredSessions(): void
    {
        $lifetime = LoginSession::config()->get('default_session_lifetime');
        $sessions = LoginSession::get()->filter([
            'LastAccessed:LessThan' => date('Y-m-d H:i:s', time() - $lifetime),
            'Persistent' => 0
        ]);
        $sessions->removeAll();
    }

    /**
     * Collect all persistent LoginSession records where the associated RememberLoginHash has expired
     */
    private function collectImplicitlyExpiredSessions(): void
    {
        LoginSession::get()->filter([
            'Persistent' => 1,
            'LoginHash.ExpiryDate:LessThan' => date('Y-m-d H:i:s')
        ])->removeAll();
    }

    /**
     * Collect all RememberLoginHash records that have expired
     */
    private function collectExpiredLoginHashes(): void
    {
        RememberLoginHash::get()->filter([
            'ExpiryDate:LessThan' => date('Y-m-d H:i:s')
        ])->removeAll();
    }
}
