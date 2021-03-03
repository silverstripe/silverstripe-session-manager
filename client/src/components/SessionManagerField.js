/* global window */
import React from 'react';
import LoginSession from './LoginSession';

function SessionManagerField(props) {
    return (
      <div className={'SessionManagerField'}>
        {props.loginSessions.map((loginSession) =>
          <LoginSession key={loginSession.ID} {...loginSession} />
            )}
      </div>
    );
}

export default SessionManagerField;
