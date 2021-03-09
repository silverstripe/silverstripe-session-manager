/* global window */
import React from 'react';
import LoginSession from '../LoginSession/LoginSession';

function SessionManagerField(props) {
    return (
      <div className={'session-manager-field'}>
        {props.loginSessions.map((loginSession) =>
          <LoginSession key={loginSession.ID} {...loginSession} />
            )}
      </div>
    );
}

export default SessionManagerField;
