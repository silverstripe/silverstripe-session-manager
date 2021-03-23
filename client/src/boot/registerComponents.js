import LoginSession from 'components/LoginSession/LoginSession';
import SessionManagerField from 'components/SessionManagerField/SessionManagerField';
import Injector from 'lib/Injector';

export default () => {
  Injector.component.registerMany({
      LoginSession,
      SessionManagerField,
  });
};
