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
        const confirmTitle = i18n._t(
            'SessionManager.CONFIRMATION_TITLE',
            'Are you sure?'
        );
        const buttonLabel = i18n._t(
            'SessionManager.DELETE_CONFIRMATION_BUTTON',
            'Remove login session'
        );

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

    const lastAccessedElapsed = moment.utc(props.LastAccessed).fromNow();
    const currentStr = i18n._t('SessionManager.CURRENT', 'Current');
    const lastActiveStr = i18n._t('SessionManager.LAST_ACTIVE', 'last active');
    const logOutStr = i18n._t('SessionManager.LOG_OUT', 'Log Out');

    return (
      <div className="login-session">
        <div>{props.UserAgent}</div>
        {props.IsCurrent && <strong className={'text-success'}>{currentStr}</strong>}
        <div className="text-muted">
          {props.IPAddress}
          {!props.IsCurrent && `, ${lastActiveStr} ${lastAccessedElapsed}`}
        </div>
        {!props.IsCurrent && <a
                href="javascript:void(0);" // eslint-disable-line
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
    LastAccessed: PropTypes.string.isRequired,
    LogOutEndpoint: PropTypes.string.isRequired,
};

export default LoginSession;
