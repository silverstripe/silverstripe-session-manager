/* global jest, test, describe, it, expect, beforeEach, Event, global */

import React from 'react';
import { Component as LoginSessionContainer } from '../LoginSessionContainer';
import { fireEvent, render, screen } from '@testing-library/react';

let doResolve;
let doReject;

jest.mock('lib/Backend', () => ({
  createEndpointFetcher: () => () => (
    new Promise((resolve, reject) => {
      doResolve = resolve;
      doReject = reject;
    })
  )
}));

jest.useFakeTimers().setSystemTime(new Date('2021-03-12 03:47:22'));

window.ss.config = {
  SecurityID: 1234567890
};

const sessionData = {
  IPAddress: '127.0.0.1',
  IsCurrent: false,
  UserAgent: 'Chrome on Mac OS X 10.15.7',
  Created: '2021-01-20 00:33:41',
  LastAccessed: '2021-03-11 03:47:22'
};

function makeProps(obj = {}) {
  return {
    ID: 1,
    LogOutEndpoint: '/test',
    displayToastSuccess: () => {},
    displayToastFailure: () => {},
    ...sessionData,
    ...obj,
  };
}

test('LoginSessionContainer should call displayToastSuccess on success', async () => {
  const displayToastSuccess = jest.fn();
  const displayToastFailure = jest.fn();
  const { container } = render(
    <LoginSessionContainer {...makeProps({
      displayToastSuccess,
      displayToastFailure
    })}
    />
  );
  fireEvent.click(container.querySelector('.login-session__logout'));
  await screen.findByText('Logging out...');
  doResolve({
    message: 'a great success'
  });
  await new Promise((resolve) => setTimeout(resolve(), 0));
  expect(displayToastSuccess).toHaveBeenCalledWith('a great success');
  expect(displayToastFailure).not.toHaveBeenCalled();
  // The weirdness below is to stop the "act() warning" that comes from the finally() block in
  // LoginSessionContainer::logout()
  // https://kentcdodds.com/blog/fix-the-not-wrapped-in-act-warning#how-to-fix-the-act-warning
  await new Promise((resolve) => setTimeout(resolve(), 0));
  await screen.findByText('Logging out...');
});

test('LoginSessionContainer should call displayToastFailure on failure', async () => {
  const displayToastSuccess = jest.fn();
  const displayToastFailure = jest.fn();
  const { container } = render(
    <LoginSessionContainer {...makeProps({
      displayToastSuccess,
      displayToastFailure
    })}
    />
  );
  fireEvent.click(container.querySelector('.login-session__logout'));
  await screen.findByText('Logging out...');
  doReject({
    response: {
      text: () => Promise.resolve(JSON.stringify({ message: 'horrible failure' }))
    }
  });
  await new Promise((resolve) => setTimeout(resolve(), 0));
  await screen.findByText('Log out');
  expect(displayToastSuccess).not.toHaveBeenCalled();
  expect(displayToastFailure).toHaveBeenCalledWith('horrible failure');
});

test('LoginSessionContainer Handles HTTP Request failure', async () => {
  const displayToastSuccess = jest.fn();
  const displayToastFailure = jest.fn();
  const { container } = render(
    <LoginSessionContainer {...makeProps({
      displayToastSuccess,
      displayToastFailure
    })}
    />
  );
  fireEvent.click(container.querySelector('.login-session__logout'));
  await screen.findByText('Logging out...');
  doReject({
    response: {
      text: () => Promise.resolve('Horrible HTTP Failure')
    }
  });
  await new Promise((resolve) => setTimeout(resolve(), 0));
  await screen.findByText('Log out');
  expect(displayToastSuccess).not.toHaveBeenCalled();
  expect(displayToastFailure).toHaveBeenCalledWith('Could not log out of session. Try again later.');
});
