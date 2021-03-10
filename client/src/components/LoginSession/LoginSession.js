/* global window */
import React, { useState } from 'react';
import confirm from '@silverstripe/reactstrap-confirm';
import Config from 'lib/Config'; // eslint-disable-line
import backend from 'lib/Backend';
import moment from 'moment';
import i18n from 'i18n';
import PropTypes from 'prop-types';

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
            .then(response => response.json())
            .then(output => {
                setLoading({ complete: true, failed: !!output.error, submitting: false });
            })
            .catch(() => {
                setLoading({ complete: true, failed: true, submitting: false });
            });
    }

    if (loading.complete || loading.submitting) {
        return null;
    }

    const created = moment(props.Created);
    const lastAccessed = moment(props.LastAccessed);
    const lastAccessedElapsed = moment.utc(props.LastAccessed).fromNow();
    const currentStr = i18n._t('SessionManager.CURRENT', 'Current');
    const lastActiveStr = i18n._t('SessionManager.LAST_ACTIVE', 'last active');
    const logOutStr = i18n._t('SessionManager.LOG_OUT', 'Log Out');

    const activityTooltip = i18n.inject(
        i18n._t('Admin.ACTIVITY_TOOLTIP_TEXT', 'Signed in {signedIn}, Last active {lastActive}'),
        {
            signedIn: created.format('L LT'),
            lastActive: lastAccessed.format('L LT')
        }
    );

    return (
        <div className="login-session">
            <div>{props.UserAgent}</div>
            {props.IsCurrent &&
                <strong className={'text-success'} data-toggle="tooltip" data-placement="top" title={activityTooltip}>
                    {currentStr}
                </strong>
            }
            <div className="text-muted">
                {props.IPAddress}
                {!props.IsCurrent &&
                    <span data-toggle="tooltip" data-placement="top" title={activityTooltip}>
                        , {lastActiveStr} {lastAccessedElapsed}
                    </span>
                }
            </div>
            {!props.IsCurrent && <a
                role={'button'}
                tabIndex={'0'}
                className={'login-session__logout'}
                onClick={logOut}
            >{logOutStr}</a>}
        </div>
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
