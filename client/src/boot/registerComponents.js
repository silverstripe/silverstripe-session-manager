import SessionManagerField from 'components/SessionManagerField';
import Injector from 'lib/Injector';

export default () => {
  Injector.component.registerMany({
      SessionManagerField,
  });
};
