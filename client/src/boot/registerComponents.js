import LoginSession from 'components/LoginSession/LoginSession';
import LoginSessionContainer from 'components/LoginSession/LoginSessionContainer';
import SessionManagerField from 'components/SessionManagerField/SessionManagerField';
import Injector from 'lib/Injector';

export default () => {
  Injector.component.registerMany({
    LoginSession,
    LoginSessionContainer,
    SessionManagerField,
  });
};
