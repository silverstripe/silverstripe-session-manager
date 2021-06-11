/* global jest, jasmine, describe, it, expect, beforeEach, Event */

import React from 'react';
import LoginSession from '../LoginSession';
import Enzyme, { shallow } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';
import MockDate from 'mockdate';

jest.mock('@silverstripe/reactstrap-confirm', () => () => Promise.resolve(true));

Enzyme.configure({ adapter: new Adapter() });

describe('LoginSession', () => {
  let props;
  beforeEach(() => {
    MockDate.set('2021-03-12 03:47:22');
    props = {
      IPAddress: '127.0.0.1',
      IsCurrent: false,
      UserAgent: 'Chrome on Mac OS X 10.15.7',
      Created: '2021-01-20 00:33:41',
      LastAccessed: '2021-03-11 03:47:22',
      submitting: false,
      complete: false,
      failed: false
    };
  });
  it('should display details', () => {
    const wrapper = shallow(<LoginSession {...props} />);
    const html = wrapper.html();
    expect(html.indexOf('Chrome on Mac OS X 10.15.7')).not.toBe(false);
    expect(html.indexOf('127.0.0.1')).not.toBe(false);
    // When using jest date will default to US locale
    expect(html.indexOf('Signed in 01/20/2021 12:33 AM')).not.toBe(false);
    expect(html.indexOf('Last active 03/11/2021 3:47 AM')).not.toBe(false);
  });

  it('should display a logout button', () => {
    const wrapper = shallow(<LoginSession {...props} />);
    const button = wrapper.find('.login-session__logout');
    expect(button).not.toBeNull();
    expect(button.html().includes('Log out')).toBe(true);
  });

  it('should display logging out when submitting', () => {
    const newProps = { ...props, submitting: true };
    const wrapper = shallow(<LoginSession {...newProps} />);
    const button = wrapper.find('.login-session__logout');
    expect(button.html().includes('Logging out...')).toBe(true);
  });

  it('should display logging out when complete', () => {
    const newProps = { ...props, submitting: false, complete: true };
    const wrapper = shallow(<LoginSession {...newProps} />);
    const button = wrapper.find('.login-session__logout');
    expect(button.html().includes('Logging out...')).toBe(true);
  });

  it('should be hidden when complete', () => {
    const newProps = { ...props, submitting: false, complete: true };
    const wrapper = shallow(<LoginSession {...newProps} />);
    const els = wrapper.find('.login-session.hidden');
    expect(els.length).toBe(1);
  });

  it('should not be hidden when failed', () => {
    const newProps = { ...props, failed: true };
    const wrapper = shallow(<LoginSession {...newProps} />);
    const els = wrapper.find('.login-session.hidden');
    expect(els.length).toBe(0);
  });
});
