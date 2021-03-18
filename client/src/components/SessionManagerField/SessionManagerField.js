/* global window */
import React from 'react';
import LoginSession from '../LoginSession/LoginSession';
import PropTypes from 'prop-types';

function SessionManagerField(props) {
    return (
      <ul className={'session-manager-field list-unstyled'}>
        {props.loginSessions.map((loginSession) =>
          (<li key={loginSession.ID} className={'list-unstyled'}>
            <LoginSession {...loginSession} />
          </li>)
            )}
      </ul>
    );
}

SessionManagerField.propTypes = {
    loginSessions: PropTypes.arrayOf(LoginSession.propTypes)
};

export default SessionManagerField;
