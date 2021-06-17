<?php

namespace SilverStripe\SessionManager\Controllers;

use Exception;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\Security\Security;
use SilverStripe\Security\SecurityToken;
use SilverStripe\SessionManager\Models\LoginSession;

/**
 * Handles session revocation AJAX request
 */
class LoginSessionController extends Controller
{
    private static $url_handlers = [
        'DELETE $ID' => 'remove',
    ];

    private static $url_segment = 'loginsession';

    private static $allowed_actions = [
        'remove',
    ];

    /**
     * Remove the specified login session
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function remove(HTTPRequest $request): HTTPResponse
    {
        if (empty(Security::getCurrentUser())) {
            return $this->jsonResponse(
                [
                    'message' => _t(
                        __CLASS__ . '.YOUR_SESSION_HAS_EXPIRED',
                        'Your session has expired'
                    )
                ],
                401
            );
        }

        // Validate CSRF token and ID parameter
        $id = $request->param('ID');
        if (!SecurityToken::inst()->checkRequest($request) || !is_numeric($id)) {
            return $this->jsonResponse(
                [
                    'message' => _t(
                        __CLASS__ . '.INVALID_REQUEST',
                        'Invalid request'
                    )
                ],
                400
            );
        }

        $loginSession = LoginSession::get()->byID($id);
        if (!$loginSession || !$loginSession->canDelete()) {
            // We're not making a difference between a non-existent session and session you can not revoke
            // to prevent an adversary from scanning LoginSession IDs
            return $this->jsonResponse(
                [
                    'message' => _t(
                        __CLASS__ . '.SESSION_COULD_NOT_BE_FOUND_OR_NO_LONGER_ACTIVE',
                        'This session could not be found or is no longer active.'
                    )
                ],
                404
            );
        }

        $this->extend('onBeforeRemoveLoginSession', $loginSession);

        $loginSession->delete();

        return $this->jsonResponse([
            'message' => _t(__CLASS__ . '.REMOVE_SUCCESS', 'Successfully removed session.')
        ]);
    }

    /**
     * Respond with the given array as a JSON response
     *
     * @param array $response
     * @param int $code The HTTP response code to set on the response
     * @return HTTPResponse
     */
    private function jsonResponse(array $response, int $code = 200): HTTPResponse
    {
        HTTPCacheControlMiddleware::singleton()->disableCache();
        return HTTPResponse::create(json_encode($response))
            ->addHeader('Content-Type', 'application/json')
            ->setStatusCode($code);
    }
}
