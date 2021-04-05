import React from 'react';
import { storiesOf } from '@storybook/react';
import LoginSession from 'components/LoginSession/LoginSession';
import { withKnobs, boolean } from '@storybook/addon-knobs/react';

const createDateMinutesAgo = (m) => {
    const d1 = new Date();
    const d2 = new Date(d1);
    d2.setMinutes(d1.getMinutes() - m);
    return d2.toISOString().replace(/[TZ]/g, ' ').replace(/\.[0-9]+ $/, '');
};

const props = {
    IPAddress: '127.0.0.1',
    UserAgent: 'Chrome on Mac OS X 10.15.7',
    Created: createDateMinutesAgo(120),
    LastAccessed: createDateMinutesAgo(25),
    logout: () => 1
};

storiesOf('SessionManager/LoginSession', module)
    .addDecorator(withKnobs)
    .add('Login session', () => (
      <LoginSession
        {...props}
        IsCurrent={boolean('IsCurrent', false)}
        submitting={boolean('Submitting', false)}
        complete={boolean('Complete', false)}
        failed={boolean('Failed', false)}
      />
    ));
