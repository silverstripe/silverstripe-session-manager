/* global window */
import React, { Component } from 'react';
import confirm from '@silverstripe/reactstrap-confirm';
import Config from 'lib/Config'; // eslint-disable-line
import backend from 'lib/Backend';
import moment from 'moment';
import i18n from 'i18n';
import PropTypes from 'prop-types';

class LoginSession extends Component {
    constructor(props) {
        super(props);

        this.state = {
            complete: false,
            failed: false,
            submitting: false,
        };

        this.logOut = this.logOut.bind(this);
    }

    async logOut() {
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

        this.setState({ submitting: true });

        const endpoint = backend.createEndpointFetcher({
            url: `${this.props.LogOutEndpoint}/:id`,
            method: 'delete',
            payloadSchema: {
                id: { urlReplacement: ':id', remove: true }
            },
            defaultData: {
                SecurityID: Config.get('SecurityID')
            }
        });

        endpoint({
            id: this.props.ID
        })
            .then(response => response.json())
            .then(output => {
                const failed = !!output.error;
                this.setState({ complete: true, failed, submitting: false });
            })
            .catch(() => {
                this.setState({ complete: true, failed: true, submitting: false });
            });
    }

    render() {
        const lastAccessedElapsed = moment.utc(this.props.LastAccessed).fromNow();

        if (this.state.complete || this.state.submitting) {
            return null;
        }

        return (
          <div className="login-session">
            <div>{this.props.UserAgent}</div>
            {this.props.IsCurrent && <strong className={'text-success'}>{i18n._t(
                    'SessionManager.CURRENT',
                    'Current'
                )}</strong>}
            <div className="text-muted">
              {this.props.IPAddress}
              {!this.props.IsCurrent && `, ${i18n._t(
                        'SessionManager.LAST_ACTIVE',
                        'last active'
                    )} ${lastAccessedElapsed}`}
            </div>
            {!this.props.IsCurrent && <a
              href="javascript:void(0);" // eslint-disable-line
              onClick={this.logOut}
            >{i18n._t(
                'SessionManager.LOG_OUT',
                    'Log Out'
                )}</a>}
          </div>
        );
    }
}

const LoginSessionShape = PropTypes.shape({
    ID: PropTypes.number.isRequired,
    IPAddress: PropTypes.string,
    IsCurrent: PropTypes.bool,
    UserAgent: PropTypes.string,
    Persistent: PropTypes.number,
    Member: PropTypes.object,
    LastAccessed: PropTypes.string,
    LogOutEndpoint: PropTypes.string,
});

LoginSession.propTypes = LoginSessionShape;

LoginSession.defaultProps = {};

export default LoginSession;

export { LoginSessionShape };
