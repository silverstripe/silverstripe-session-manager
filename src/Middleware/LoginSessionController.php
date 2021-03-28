<?php

namespace SilverStripe\SessionManager\Control;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\SecurityToken;
use SilverStripe\SessionManager\Model\LoginSession;

// TODO: move this to Controllers folder and extend Controller instead of LeftAndMain
class LoginSessionController extends LeftAndMain
{
    private static $url_segment = 'loginsession';

    // TODO: remove this when no longer extending LeftAndMain
    private static $ignore_menuitem = true;

    private static $url_handlers = [
        'DELETE remove/$ID' => 'removeLoginSession',
    ];

    private static $allowed_actions = [
        'removeLoginSession',
    ];

    /**
     * Remove the specified login session
     *
     * @param HTTPRequest $request
     * @return HTTPResponse
     */
    public function removeLoginSession(HTTPRequest $request): HTTPResponse
    {
        // Ensure CSRF protection
        if (!SecurityToken::inst()->checkRequest($request)) {
            return $this->jsonResponse(
                ['errors' => 'Request timed out, please try again'],
                400
            );
        }

        $id = $request->param('ID');
        $loginSession = LoginSession::get()->byID($id);
        if (!$loginSession) {
            return $this->jsonResponse(
                ['errors' => 'Something went wrong.'],
                400
            );
        }

        if (!$loginSession->canDelete()) {
            return $this->jsonResponse(
                ['errors' => 'You do not have permission to delete this record.'],
                400
            );
        }

        $this->extend('onBeforeRemoveLoginSession', $loginSession);

        $loginSession->delete();

        return $this->jsonResponse([
            'success' => true,
        ]);
    }

    /**
     * Respond with the given array as a JSON response
     *
     * @param array $response
     * @param int $code The HTTP response code to set on the response
     * @return HTTPResponse
     */
    protected function jsonResponse(array $response, int $code = 200): HTTPResponse
    {
        return HTTPResponse::create(json_encode($response))
            ->addHeader('Content-Type', 'application/json')
            ->setStatusCode($code);
    }
}
