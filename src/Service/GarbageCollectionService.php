<?php

namespace SilverStripe\SessionManager\Service;

use SilverStripe\Core\Config\Config;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\SessionManager\Model\LoginSession;

class GarbageCollectionService
{
    public function collect()
    {
        $this->collectExpiredSessions();
        $this->collectImplicitlyExpiredSessions();
        $this->collectExpiredLoginHashes();
    }

    /**
     * Collect all non-persistent LoginSession records that are older than the session lifetime
     */
    protected function collectExpiredSessions()
    {
        $lifetime = Config::inst()->get(LoginSession::class, 'default_session_lifetime');
        $sessions = LoginSession::get()->filter([
            'LastAccessed:LessThan' => date('Y-m-d H:i:s', time() - $lifetime),
            'Persistent' => 0
        ]);
        $sessions->removeAll();
    }

    /**
     * Collect all persistent LoginSession records where the associated RememberLoginHash has expired
     */
    protected function collectImplicitlyExpiredSessions()
    {
        $sessions = LoginSession::get()->filter([
            'Persistent' => 1,
            'LoginHash.ExpiryDate:LessThan' => date('Y-m-d H:i:s')
        ]);
        $sessions->removeAll();
    }

    /**
     * Collect all RememberLoginHash records that have expired
     */
    protected function collectExpiredLoginHashes()
    {
        $hashes = RememberLoginHash::get()->filter([
            'ExpiryDate:LessThan' => date('Y-m-d H:i:s')
        ]);
        $hashes->removeAll();
    }
}
