<?php

namespace SilverStripe\SessionManager\Controllers;

use Exception;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\SecurityToken;
use SilverStripe\SessionManager\Models\LoginSession;

class LoginSessionController extends Controller
{
    private static $url_segment = 'loginsession';

    private static $url_handlers = [
        'DELETE remove/$ID' => 'remove',
    ];

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
        return $this->removeLoginSession($request);
    }

    private function removeLoginSession(HTTPRequest $request): HTTPResponse
    {
        $failureMessage = _t(__CLASS__ . '.REMOVE_FAILURE', 'Something went wrong.');
        try {
            // Ensure CSRF protection
            if (!SecurityToken::inst()->checkRequest($request)) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => $failureMessage
                ]);
            }

            $id = $request->param('ID');
            $loginSession = LoginSession::get()->byID($id);
            if (!$loginSession) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => $failureMessage
                ]);
            }

            if (!$loginSession->canDelete()) {
                $message = _t(__CLASS__ . '.REMOVE_PERMISSION', 'You do not have permission to delete this record.');
                return $this->jsonResponse([
                    'success' => false,
                    'message' => $message
                ]);
            }
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => $failureMessage
            ]);
        }

        $this->extend('onBeforeRemoveLoginSession', $loginSession);

        $loginSession->delete();

        return $this->jsonResponse([
            'success' => true,
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
        return HTTPResponse::create(json_encode($response))
            ->addHeader('Content-Type', 'application/json')
            ->setStatusCode($code);
    }
}
