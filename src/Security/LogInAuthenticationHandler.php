<?php

namespace SilverStripe\SessionManager\Security;

use InvalidArgumentException;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\AuthenticationHandler;
use SilverStripe\Security\Member;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\SessionManager\Models\LoginSession;

/**
 * This is separate to LogOutAuthenticationHandler so that it can be registered with
 * Injector and called *after* the other AuthenticationHandler::logIn() implementations
 */
class LogInAuthenticationHandler implements AuthenticationHandler
{
    /**
     * @var string
     */
    private $sessionVariable;

    /**
     * @var RememberLoginHash
     */
    private $rememberLoginHash;

    /**
     * @return string
     */
    public function getSessionVariable()
    {
        return $this->sessionVariable;
    }

    /**
     * @param string $sessionVariable
     * @return void
     */
    public function setSessionVariable(string $sessionVariable): void
    {
        $this->sessionVariable = $sessionVariable;
    }

    /**
     * @return RememberLoginHash|null
     */
    public function getRememberLoginHash(): ?RememberLoginHash
    {
        return $this->rememberLoginHash;
    }

    /**
     * @param RememberLoginHash $rememberLoginHash
     */
    public function setRememberLoginHash(RememberLoginHash $rememberLoginHash)
    {
        $this->rememberLoginHash = $rememberLoginHash;
    }

    /**
     * @param HTTPRequest $request
     * @return Member|null
     */
    public function authenticateRequest(HTTPRequest $request)
    {
        // noop
    }

    /**
     * @param Member $member
     * @param bool $persistent
     * @param HTTPRequest|null $request
     * @throws InvalidArgumentException
     */
    public function logIn(Member $member, $persistent = false, HTTPRequest $request = null)
    {
        // Fall back to retrieving request from current Controller if available
        if ($request === null) {
            if (!Controller::has_curr()) {
                throw new InvalidArgumentException(
                    "Authentication with SessionManager enabled requires an active HTTPRequest."
                );
            }

            $request = Controller::curr()->getRequest();
        }

        $loginSession = LoginSession::find($member, $request);
        if (!$loginSession) {
            $loginSession = LoginSession::generate($member, $persistent, $request);
        }

        $loginSession->updateLastAccessed($request);

        if ($persistent && $rememberLoginHash = $this->getRememberLoginHash()) {
            $rememberLoginHash->LoginSessionID = $loginSession->ID;
            $rememberLoginHash->write();
        }

        if ($request) {
            $request->getSession()->set($this->getSessionVariable(), $loginSession->ID);
        }
    }

    /**
     * @param HTTPRequest $request|null
     */
    public function logOut(HTTPRequest $request = null)
    {
        // noop
    }
}
