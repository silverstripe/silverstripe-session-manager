import LoginSession from 'components/LoginSession/LoginSession';

const createDateMinutesAgo = (m) => {
    const d1 = new Date();
    const d2 = new Date(d1);
    d2.setMinutes(d1.getMinutes() - m);
    return d2
        .toISOString()
        .replace(/[TZ]/g, ' ')
        .replace(/\.[0-9]+ $/, '');
};

const props = {
    IPAddress: '127.0.0.1',
    UserAgent: 'Chrome on Mac OS X 10.15.7',
    Created: createDateMinutesAgo(120),
    LastAccessed: createDateMinutesAgo(25),
    logout: () => 1,
};

export default {
    title: 'SessionManager/LoginSession',
    component: LoginSession
};

export const _LoginSession = {
    name: 'Login session',
    args: {
        ...props,
        IsCurrent: false,
        submitting: false,
        complete: false,
        failed: false,
    }
};
