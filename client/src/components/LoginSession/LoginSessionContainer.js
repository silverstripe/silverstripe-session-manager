import PropTypes from 'prop-types';
import React, { useState } from 'react';
import { connect } from 'react-redux';
import backend from 'lib/Backend';
import Config from 'lib/Config'; // eslint-disable-line
import LoginSession from './LoginSession';
import { success, error } from 'state/toasts/ToastsActions';

// Handle communication with  server on logout and toast notifications via redux
// Using a class component rather than function component so that LoginSessionContainer-test.js
// can use wrapper.instance() to test logout().
// .instance() does not work on stateless functional components
// https://enzymejs.github.io/enzyme/docs/api/ReactWrapper/instance.html
function LoginSessionContainer(props) {
    //
    const [revokeRequestState, setRevokeRequestState] = useState({
        complete: false,
        failed: false,
        submitting: false
    });

    function createEndpoint() {
        return backend.createEndpointFetcher({
            url: `${props.LogOutEndpoint}/:id`,
            method: 'delete',
            payloadSchema: {
                id: { urlReplacement: ':id', remove: true },
                SecurityID: { querystring: true }
            }
        });
    }

    function logout() {
        setRevokeRequestState({ submitting: true });
        const endpoint = createEndpoint();
        endpoint({
            id: props.ID,
            SecurityID: Config.get('SecurityID')
        })
        .then(response => {
            const failed = !response.success;
            setRevokeRequestState({
                complete: true,
                failed,
                submitting: false
            });
            if (failed) {
                props.displayToastFailure(response.message);
            } else {
                props.displayToastSuccess(response.message);
            }
        })
        .catch(() => {
            setRevokeRequestState({
                complete: true,
                failed: true,
                submitting: false
            });
        });
    }

    const { ID, LogoutEndPoint, ...loginSessionProps } = props;
    const newProps = { ...loginSessionProps, ...revokeRequestState, logout };
    return <LoginSession {...newProps} />;
}

LoginSessionContainer.propTypes = {
    // LoginSessionContainer specific:
    ID: PropTypes.number.isRequired,
    LogOutEndpoint: PropTypes.string.isRequired,
    displayToastSuccess: PropTypes.func.isRequired,
    displayToastFailure: PropTypes.func.isRequired,
    // Passed on to LoginSession:
    IPAddress: PropTypes.string.isRequired,
    IsCurrent: PropTypes.bool,
    UserAgent: PropTypes.string,
    Created: PropTypes.string.isRequired,
    LastAccessed: PropTypes.string.isRequired,
};

function mapDispatchToProps(dispatch) {
    return {
        displayToastSuccess(message) {
            dispatch(success(message));
        },
        displayToastFailure(message) {
            dispatch(error(message));
        },
    };
}

export { LoginSessionContainer as Component };

export default connect(() => ({}), mapDispatchToProps)(LoginSessionContainer);
