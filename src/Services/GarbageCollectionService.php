<?php

namespace SilverStripe\SessionManager\Services;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\SessionManager\Models\LoginSession;

class GarbageCollectionService
{
    use Configurable;
    use Injectable;

    /**
     * Limit the number of records collected per run.
     */
    private static ?int $batch_remove_limit = null;

    /**
     * Delete expired LoginSession and RememberLoginHash records
     */
    public function collect(): void
    {
        $this->collectExpiredSessions();
        $this->collectImplicitlyExpiredSessions();
        $this->collectExpiredLoginHashes();
    }

    private function batchRemoveAll($datalist)
    {
        $limit = static::config()->get('batch_remove_limit');
        $limitedList = $datalist->limit($limit);
        DB::get_conn()->transactionStart();
        foreach ($limitedList as $record) {
            $record->delete();
        }
        DB::get_conn()->transactionEnd();
    }

    /**
     * Collect all non-persistent LoginSession records that are older than the session lifetime
     */
    private function collectExpiredSessions(): void
    {
        $lifetime = LoginSession::config()->get('default_session_lifetime');
        $maxAge = LoginSession::getMaxAge();
        $sessions = LoginSession::get()->filter([
            'LastAccessed:LessThan' => $maxAge,
            'Persistent' => 0,
        ]);
        $this->batchRemoveAll($sessions);
    }

    /**
     * Collect all persistent LoginSession records where the associated RememberLoginHash has expired
     */
    private function collectImplicitlyExpiredSessions(): void
    {
        $now = DBDatetime::now()->getTimestamp();
        $sessions = LoginSession::get()->filter([
            'Persistent' => 1,
            'LoginHash.ExpiryDate:LessThan' => date('Y-m-d H:i:s', $now),
        ]);
        $this->batchRemoveAll($sessions);

        $lifetime = LoginSession::config()->get('default_session_lifetime');
        $maxAge = LoginSession::getMaxAge();
        // If a persistent session has no login hash, use LastAccessed
        $sessions = LoginSession::get()->filter([
            'LastAccessed:LessThan' => $maxAge,
            'Persistent' => 1,
            'LoginHash.ExpiryDate' => null,
        ]);
        $this->batchRemoveAll($sessions);
    }

    /**
     * Collect all RememberLoginHash records that have expired
     */
    private function collectExpiredLoginHashes(): void
    {
        $now = DBDatetime::now()->getTimestamp();
        $hashes = RememberLoginHash::get()->filter([
            'ExpiryDate:LessThan' => date('Y-m-d H:i:s', $now),
        ]);
        $this->batchRemoveAll($hashes);
    }
}
