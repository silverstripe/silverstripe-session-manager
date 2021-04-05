/* global jest, jasmine, describe, it, expect, beforeEach, Event, global */

import React from 'react';
import { Component as LoginSessionContainer } from '../LoginSessionContainer';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import MockDate from 'mockdate';

// use the jest global variable to set the value inside jest mock of lib/Backend which is
// unable to access regular variable inside its scope
global.success = undefined;

function setEndpointFetcherSuccess(success) {
    global.success = success;
}

jest.mock('lib/Backend', () => ({
    createEndpointFetcher: () => async () => ({
        error: false,
        success: global.success,
        message: 'message'
    })
}));

Enzyme.configure({ adapter: new Adapter() });

window.ss.config = {
    SecurityID: 1234567890
};

describe('LoginSessionContainer', () => {
    let wrapper;
    let props;

    beforeEach(() => {
        MockDate.set('2021-03-12 03:47:22');
        props = {
            // used in LoginSessionContainer
            ID: 1,
            LogOutEndpoint: '/test',
            displayToastSuccess: jest.fn(() => 1),
            displayToastFailure: jest.fn(() => 1),
            // passed on to LoginSession
            IPAddress: '127.0.0.1',
            IsCurrent: false,
            UserAgent: 'Chrome on Mac OS X 10.15.7',
            Created: '2021-01-20 00:33:41',
            LastAccessed: '2021-03-11 03:47:22',
        };
    });

    it('should call displayToastSuccess on success', async () => {
        setEndpointFetcherSuccess(true);
        wrapper = shallow(<LoginSessionContainer {...props} />);
        await wrapper.instance().logout();
        expect(props.displayToastSuccess).toBeCalledTimes(1);
        expect(props.displayToastFailure).toBeCalledTimes(0);
    });

    it('should call displayToastFailure on failure', async () => {
        setEndpointFetcherSuccess(false);
        wrapper = shallow(<LoginSessionContainer {...props} />);
        await wrapper.instance().logout();
        expect(props.displayToastSuccess).toBeCalledTimes(0);
        expect(props.displayToastFailure).toBeCalledTimes(1);
    });
});
