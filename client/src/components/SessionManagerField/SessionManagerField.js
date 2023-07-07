/* global window */
import React from 'react';
import PropTypes from 'prop-types';
import LoginSessionContainer from '../LoginSession/LoginSessionContainer';

function SessionManagerField(props) {
  return (
    <ul className="session-manager-field list-unstyled">
      {props.loginSessions.map((loginSession) => (
        <li key={loginSession.ID} className="list-unstyled">
          <LoginSessionContainer {...loginSession} />
        </li>
      ))}
    </ul>
  );
}

SessionManagerField.propTypes = {
  loginSessions: PropTypes.arrayOf(LoginSessionContainer.propTypes)
};

export default SessionManagerField;
