import i18n from 'i18n';
import PropTypes from 'prop-types';
import React from 'react';
import moment from 'moment';
import confirm from '@silverstripe/reactstrap-confirm';
import Button from 'components/Button/Button';

/**
 * Local date and Local time format. e.g.: `04/15/2021 1:31 PM` for en_US
 * @type {string}
 */
const format = 'L LT';

function LoginSession(props) {
  // This is an async function because 'confirm' requires it
  // https://www.npmjs.com/package/@silverstripe/reactstrap-confirm
  async function attemptLogOut() {
    if (props.submitting) {
      return;
    }
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
    props.logout();
  }

  function createTooltip() {
    moment.locale(i18n.detectLocale());
    const created = moment.utc(props.Created).local();
    const lastAccessed = moment.utc(props.LastAccessed).local();
    const createdElapsed = created.fromNow();
    const lastAccessedElapsed = lastAccessed.fromNow();
    const activityTooltip = i18n.inject(
      i18n._t('Admin.ACTIVITY_TOOLTIP_TEXT', 'Signed in {signedIn}, Last active {lastActive}'),
      {
        signedIn: created.format(format),
        lastActive: lastAccessed.format(format)
      }
    );
    const lastActiveStr = props.IsCurrent ?
      i18n.inject(
        i18n._t('SessionManager.AUTHENTICATED', 'authenticated {createdElapsed}...'),
        { createdElapsed }
      )
      : i18n.inject(
        i18n._t('SessionManager.LAST_ACTIVE', 'last active {lastAccessedElapsed}...'),
        { lastAccessedElapsed }
      );

    return (
      <span data-toggle="tooltip" data-placement="top" title={activityTooltip}>
        , {lastActiveStr}
      </span>
    );
  }

  const currentStr = i18n._t('SessionManager.CURRENT', 'Current');
  const logOutStr = (props.submitting || (props.complete && !props.failed)) ?
    i18n._t('SessionManager.LOGGING_OUT', 'Logging out...')
    : i18n._t('SessionManager.LOG_OUT', 'Log out');

  return (
    <div className={`login-session ${(props.complete && !props.failed) ? 'hidden' : ''}`}>
      <p>{props.UserAgent}</p>
      <p className="text-muted">
        {props.IPAddress}
        {createTooltip()}
      </p>
      <p>
        {props.IsCurrent &&
          <strong className="text-success">{currentStr}</strong>
        }
        {!props.IsCurrent && <Button
          color="link"
          className="login-session__logout"
          onClick={() => attemptLogOut()}
        >{logOutStr}</Button>}
      </p>
    </div>
  );
}

LoginSession.propTypes = {
  IPAddress: PropTypes.string.isRequired,
  IsCurrent: PropTypes.bool,
  UserAgent: PropTypes.string,
  Created: PropTypes.string.isRequired,
  LastAccessed: PropTypes.string.isRequired,
  submitting: PropTypes.bool.isRequired,
  complete: PropTypes.bool.isRequired,
  failed: PropTypes.bool.isRequired,
};

export default LoginSession;
