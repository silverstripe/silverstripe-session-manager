<?php

namespace SilverStripe\SessionManager\Services;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\SessionManager\Models\LoginSession;

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
        $now = DBDatetime::now()->getTimestamp() - $lifetime;
        LoginSession::get()->filter([
            'LastAccessed:LessThan' => date('Y-m-d H:i:s', $now),
            'Persistent' => 0
        ])->removeAll();
    }

    /**
     * Collect all persistent LoginSession records where the associated RememberLoginHash has expired
     */
    private function collectImplicitlyExpiredSessions(): void
    {
        $now = DBDatetime::now()->getTimestamp();
        LoginSession::get()->filter([
            'Persistent' => 1,
            'LoginHash.ExpiryDate:LessThan' => date('Y-m-d H:i:s', $now),
        ])->removeAll();

        $lifetime = LoginSession::config()->get('default_session_lifetime');
        $now = DBDatetime::now()->getTimestamp() - $lifetime;
        // If a persistent session has no login hash, use LastAccessed
        LoginSession::get()->filter([
            'LastAccessed:LessThan' => date('Y-m-d H:i:s', $now),
            'Persistent' => 1,
            'LoginHash.ExpiryDate' => null,
        ])->removeAll();
    }

    /**
     * Collect all RememberLoginHash records that have expired
     */
    private function collectExpiredLoginHashes(): void
    {
        $now = DBDatetime::now()->getTimestamp();
        RememberLoginHash::get()->filter([
            'ExpiryDate:LessThan' => date('Y-m-d H:i:s', $now),
        ])->removeAll();
    }
}
