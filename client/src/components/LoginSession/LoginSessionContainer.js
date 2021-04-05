import PropTypes from 'prop-types';
import React, { Component } from 'react';
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
class LoginSessionContainer extends Component {
    //
    constructor(props) {
        super(props);
        this.createEndpoint = this.createEndpoint.bind(this);
        this.logout = this.logout.bind(this);
    }

    componentWillMount() {
        this.setState({
            complete: false,
            failed: false,
            submitting: false
        });
    }

    createEndpoint() {
        return backend.createEndpointFetcher({
            url: `${this.props.LogOutEndpoint}/:id`,
            method: 'delete',
            payloadSchema: {
                id: { urlReplacement: ':id', remove: true },
                SecurityID: { querystring: true }
            }
        });
    }

    logout() {
        this.setState({
            ...this.state,
            submitting: true
        });
        const endpoint = this.createEndpoint();
        endpoint({
            id: this.props.ID,
            SecurityID: Config.get('SecurityID')
        })
        .then(response => {
            const failed = !response.success;
            this.setState({
                complete: true,
                failed,
                submitting: false
            });
            if (failed) {
                this.props.displayToastFailure(response.message);
            } else {
                this.props.displayToastSuccess(response.message);
            }
        })
        .catch(() => {
            this.setState({
                complete: true,
                failed: true,
                submitting: false
            });
        });
    }

    render() {
        const { ID, LogoutEndPoint, ...loginSessionProps } = this.props;
        const newProps = { logout: this.logout, ...this.state, ...loginSessionProps };
        return <LoginSession {...newProps} />;
    }
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
