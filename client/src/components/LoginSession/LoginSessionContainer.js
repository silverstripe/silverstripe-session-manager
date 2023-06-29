import PropTypes from 'prop-types';
import React, { useState } from 'react';
import { connect } from 'react-redux';
import backend from 'lib/Backend';
import Config from 'lib/Config'; // eslint-disable-line
import { success, error } from 'state/toasts/ToastsActions';
import i18n from 'i18n';
import LoginSession from './LoginSession';

function createEndpoint(logOutEndpoint) {
  return backend.createEndpointFetcher({
    url: `${logOutEndpoint}/:id`.replace('//', '/'),
    method: 'delete',
    payloadSchema: {
      id: { urlReplacement: ':id', remove: true },
      SecurityID: { querystring: true }
    }
  });
}

/**
 * Handle communication with  server on logout and toast notifications via redux.
 * @param props
 */
function LoginSessionContainer(props) {
  const [complete, setComplete] = useState(false);
  const [failed, setFailed] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  function logout() {
    setSubmitting(true);
    const endpoint = createEndpoint(props.LogOutEndpoint);
    return endpoint({
      id: props.ID,
      SecurityID: Config.get('SecurityID')
    })
      .then(response => {
        props.displayToastSuccess(response.message);
      })
      .catch(err => {
        setFailed(true);
        return err.response.text().then(json => {
        // Try to parse the error response
          const response = JSON.parse(json);
          if (typeof response !== 'object' || typeof response.message !== 'string') {
            return Promise.reject('No readable error message');
          }
          props.displayToastFailure(response.message);
          return Promise.resolve();
        });
      })
      .catch(() => {
      // Catch all error handler
        props.displayToastFailure(i18n._t(
          'SessionManager.COULD_NOT_LOGOUT',
          'Could not log out of session. Try again later.'
        ));
      })
      .finally(() => {
        setComplete(true);
        setSubmitting(false);
      });
  }

  const { ID, ...loginSessionProps } = props;
  const newProps = { logout, complete, failed, submitting, ...loginSessionProps };
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
