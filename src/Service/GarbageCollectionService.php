<?php

namespace Kinglozzer\SessionManager\Service;

use Kinglozzer\SessionManager\Model\LoginSession;
use SilverStripe\Core\Config\Config;
use SilverStripe\Security\RememberLoginHash;

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
            'Persistent' => false
        ]);
        $sessions->removeAll();
    }

    /**
     * Collect all persistent LoginSession records where the associated RememberLoginHash has expired
     */
    protected function collectImplicitlyExpiredSessions()
    {
        $sessions = LoginSession::get()->filter([
            'Persistent' => true,
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
