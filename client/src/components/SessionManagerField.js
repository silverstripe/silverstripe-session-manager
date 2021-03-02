/* global window */
import React, {Component} from 'react';
import LoginSession, {LoginSessionShape} from './LoginSession';

class SessionManagerField extends Component {
    render() {
        return (
            <div className={'SessionManagerField'}>
                {this.props.loginSessions.map(function (loginSession, index) {
                    return <LoginSession key={loginSession.ID} {...loginSession}/>;
                })}
            </div>
        );
    }
}

SessionManagerField.propTypes = {
    loginSessions: PropTypes.arrayOf(LoginSessionShape),
};

SessionManagerField.defaultProps = {};

export default SessionManagerField;
