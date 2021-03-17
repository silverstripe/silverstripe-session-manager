import React from 'react';
import {storiesOf} from '@storybook/react';
import LoginSession from 'components/LoginSession/LoginSession';
import {withKnobs, boolean} from '@storybook/addon-knobs/react';
import Toast from "../../../../../../admin/client/src/components/Toasts/Toast";

const props = {
    ID: 1,
    IPAddress: '127.0.0.1',
    UserAgent: 'Chrome on Mac OS X 10.15.7',
    Created: '2021-01-20 00:33:41',
    LastAccessed: '2021-03-11 03:47:22',
    LogOutEndpoint: 'admin/loginsession/remove',
};

storiesOf('SessionManager/LoginSession', module)
    .addDecorator(withKnobs)
    .add('Login session', () => (
        <LoginSession
            {...props}
            IsCurrent={boolean('IsCurrent', false)}
        />
    ));
