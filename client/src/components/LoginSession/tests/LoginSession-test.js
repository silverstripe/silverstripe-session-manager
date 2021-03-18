/* global jest, jasmine, describe, it, expect, beforeEach, Event */

import React from 'react';
import LoginSession from '../LoginSession';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import backend from 'lib/Backend';
import MockDate from 'mockdate';

jest.mock('@silverstripe/reactstrap-confirm', () => jest.fn().mockImplementation(
    () => Promise.resolve(true)
));

Enzyme.configure({ adapter: new Adapter() });

describe('LoginSession', () => {
    let props = null;

    beforeEach(() => {
        async function endpointFetcher() {
            return {
                error: false,
                success: true
            };
        }
        backend.createEndpointFetcher = jest.fn().mockImplementation(() => endpointFetcher);

        MockDate.set('2021-03-12 03:47:22');

        // Set window config
        window.ss.config = {
            SecurityID: 1234567890
        };

        props = {
            ID: 1,
            IPAddress: '127.0.0.1',
            UserAgent: 'Chrome on Mac OS X 10.15.7',
            Created: '2021-01-20 00:33:41',
            LastAccessed: '2021-03-11 03:47:22',
            LogOutEndpoint: 'admin/loginsession/remove',
        };
    });

    describe('render()', () => {
        it('should match the snapshot', () => {
            const wrapper = shallow(<LoginSession {...props} />);
            expect(wrapper.html()).toMatchSnapshot();
        });

        it('should log sessions out correctly', done => {
            const wrapper = shallow(<LoginSession {...props} />);
            wrapper.find('.login-session__logout').simulate('click');

            setTimeout(() => {
                expect(wrapper.html()).toMatchSnapshot();
                done();
            });
        });
    });
});
