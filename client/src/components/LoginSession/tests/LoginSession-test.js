/* global jest, test, describe, it, expect, beforeEach, Event */

import React from 'react';
import LoginSession from '../LoginSession';
import { render } from '@testing-library/react';

jest.useFakeTimers().setSystemTime(new Date('2021-03-12 03:47:22'));

function makeProps(obj = {}) {
  return {
    IPAddress: '127.0.0.1',
    IsCurrent: false,
    UserAgent: 'Chrome on Mac OS X 10.15.7',
    Created: '2021-01-20 00:33:41',
    LastAccessed: '2021-03-11 03:47:22',
    submitting: false,
    complete: false,
    failed: false,
    ...obj,
  };
}

test('LoginSession should display details ', () => {
  const { container } = render(
    <LoginSession {...makeProps()}/>
  );
  expect(container.querySelector('.login-session p').textContent).toBe('Chrome on Mac OS X 10.15.7');
  expect(container.querySelector('.login-session .text-muted').firstChild.nodeValue).toBe('127.0.0.1');
  // See jest-global-setup - timezone is set to UTC
  expect(container.querySelector('.login-session span').getAttribute('title')).toBe('Signed in 01/20/2021 12:33 AM, Last active 03/11/2021 3:47 AM');
});

test('LoginSession should display a logout button', () => {
  const { container } = render(
    <LoginSession {...makeProps()}/>
  );
  expect(container.querySelector('.login-session__logout').textContent).toBe('Log out');
});

test('LoginSession should display logging out when submitting', () => {
  const { container } = render(
    <LoginSession {...makeProps({
      submitting: true
    })}
    />
  );
  expect(container.querySelector('.login-session__logout').textContent).toBe('Logging out...');
});

test('LoginSession should display logging out when complete', () => {
  const { container } = render(
    <LoginSession {...makeProps({
      submitting: false,
      complete: true
    })}
    />
  );
  expect(container.querySelector('.login-session__logout').textContent).toBe('Logging out...');
});

test('LoginSession should be hidden when complete', () => {
  const { container } = render(
    <LoginSession {...makeProps({
      submitting: false,
      complete: true
    })}
    />
  );
  expect(container.querySelectorAll('.login-session.hidden')).toHaveLength(1);
});

test('LoginSession should not be hidden when failed', () => {
  const { container } = render(
    <LoginSession {...makeProps({
      failed: true
    })}
    />
  );
  expect(container.querySelector('.login-session.hidden')).toBeNull();
});
