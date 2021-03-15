/* global window */
import React, { useState } from 'react';
import confirm from '@silverstripe/reactstrap-confirm';
import Config from 'lib/Config'; // eslint-disable-line
import backend from 'lib/Backend';
import moment from 'moment';
import i18n from 'i18n';
import PropTypes from 'prop-types';
import jQuery from 'jquery';

function LoginSession(props) {
    const [loading, setLoading] = useState({ complete: false, failed: false, submitting: false });

    async function logOut() {
        // Confirm with the user
        const confirmMessage = i18n._t(
            'SessionManager.DELETE_CONFIRMATION',
            'Are you sure you want to delete this login session?'
        );
        const confirmTitle = i18n._t('SessionManager.CONFIRMATION_TITLE', 'Are you sure?');
        const buttonLabel = i18n._t('SessionManager.DELETE_CONFIRMATION_BUTTON', 'Remove login session');

        if (!await confirm(confirmMessage, { title: confirmTitle, confirmLabel: buttonLabel })) {
            return;
        }

        setLoading({ ...loading, submitting: true });

        const endpoint = backend.createEndpointFetcher({
            url: `${props.LogOutEndpoint}/:id`,
            method: 'delete',
            payloadSchema: {
                id: { urlReplacement: ':id', remove: true },
                SecurityID: { querystring: true }
            }
        });

        endpoint({
            id: props.ID,
            SecurityID: Config.get('SecurityID')
        })
            .then(response => {
                setLoading({
                    complete: true,
                    failed: !!response.error && !response.success,
                    submitting: false
                });
                if (response.success) {
                    setTimeout(() => {
                        setLoading({
                            complete: true,
                            failed: !!response.error && !response.success,
                            fadeOutComplete: true,
                            submitting: false
                        });
                        jQuery.noticeAdd({
                            text: i18n._t(
                                'SessionManager.LOG_OUT_CONFIRMED',
                                'Successfully logged out of device.'
                            ),
                            stay: false,
                            type: 'success'
                        });
                    }, 2000);
                }
            })
            .catch((error) => {
                setLoading({ complete: true, failed: true, submitting: false });
                error.response.json().then(response => {
                    jQuery.noticeAdd({ text: response.errors, stay: false, type: 'error' });
                });
            });
    }

    if (loading.fadeOutComplete) {
        return null;
    }

    const created = moment(props.Created);
    const createdElapsed = moment.utc(props.Created).fromNow();
    const lastAccessed = moment(props.LastAccessed);
    const lastAccessedElapsed = moment.utc(props.LastAccessed).fromNow();
    const currentStr = i18n._t('SessionManager.CURRENT', 'Current');
    const lastActiveStr = props.IsCurrent ?
        i18n.inject(
            i18n._t('SessionManager.AUTHENTICATED', 'authenticated {createdElapsed}...'),
            { createdElapsed }
        )
        : i18n.inject(
            i18n._t('SessionManager.LAST_ACTIVE', 'last active {lastAccessedElapsed}...'),
            { lastAccessedElapsed }
        );
    const logOutStr = (loading.submitting || (loading.complete && !loading.failed)) ?
        i18n._t('SessionManager.LOGGING_OUT', 'Logging out...')
        : i18n._t('SessionManager.LOG_OUT', 'Log out');

    const activityTooltip = i18n.inject(
        i18n._t('Admin.ACTIVITY_TOOLTIP_TEXT', 'Signed in {signedIn}, Last active {lastActive}'),
        {
            signedIn: created.format('L LT'),
            lastActive: lastAccessed.format('L LT')
        }
    );

    return (
      <li className={`login-session ${(loading.complete && !loading.failed) ? 'hidden' : ''}`}>
        <p>{props.UserAgent}</p>
        <p className="text-muted">
          {props.IPAddress}
          <span data-toggle="tooltip" data-placement="top" title={activityTooltip}>
            , {lastActiveStr}
          </span>
        </p>
        {props.IsCurrent &&
        <p>
            <strong className={'text-success'}>
                {currentStr}
            </strong>
        </p>
            }
        {!props.IsCurrent && <a
          role={'button'}
          tabIndex={'0'}
          className={'login-session__logout'}
          onClick={loading.submitting ? null : logOut}
        >{logOutStr}</a>}
      </li>
    );
}

LoginSession.propTypes = {
    ID: PropTypes.number.isRequired,
    IPAddress: PropTypes.string.isRequired,
    IsCurrent: PropTypes.bool,
    UserAgent: PropTypes.string,
    Created: PropTypes.string.isRequired,
    LastAccessed: PropTypes.string.isRequired,
    LogOutEndpoint: PropTypes.string.isRequired,
};

export default LoginSession;
