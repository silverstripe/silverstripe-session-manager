// Manages rendering SessionManagerFields in lieu of React support in ModelAdmin
import React from 'react';
import { createRoot } from 'react-dom/client';
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

  const root = createRoot(field[0]);
  root.render(<SessionManagerField loginSessions={loginSessions} />);
}

$.entwine('ss', jQuery => {
  jQuery(FIELD_SELECTOR).entwine({
    onmatch() {
      // inject the react session manager field
      injectReactSessionManagerField(this);
    }
  });
});
