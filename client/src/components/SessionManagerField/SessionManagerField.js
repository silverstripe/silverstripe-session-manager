/* global window */
import React from 'react';
import LoginSession from '../LoginSession/LoginSession';

function SessionManagerField(props) {
    return (
      <ul className={'session-manager-field list-unstyled'}>
        {props.loginSessions.map((loginSession) =>
          <LoginSession key={loginSession.ID} {...loginSession} />
            )}
      </ul>
    );
}

export default SessionManagerField;
