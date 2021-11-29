// Manages rendering SessionManagerFields in lieu of React support in ModelAdmin
import React from 'react';
import ReactDOM from 'react-dom';
import { loadComponent } from 'lib/Injector';
import $ from 'jquery';

const FIELD_SELECTOR = '.js-injector-boot [data-field-type="session-manager-field"]';

function injectReactSessionManagerField(field) {
  const SessionManagerField = loadComponent('SessionManagerField');
  const {
    schema: {
      loginSessions
    }
  } = field.data('schema');

  ReactDOM.render(<SessionManagerField loginSessions={loginSessions} />, field[0]);
}

$.entwine('ss', ($) => {
  $(FIELD_SELECTOR).entwine({
    onmatch() {
      // inject the react session manager field
      injectReactSessionManagerField(this);
    }
  });
});
