// Manages rendering SessionManagerFields in lieu of React support in ModelAdmin
import React from 'react';
import ReactDOM from 'react-dom';
import { loadComponent } from 'lib/Injector'; // eslint-disable-line

const FIELD_SELECTOR = '.js-injector-boot [data-field-type="session-manager-field"]';

window.jQuery.entwine('ss', ($) => {
  $(FIELD_SELECTOR).entwine({
    onmatch() {
      const SessionManagerField = loadComponent('SessionManagerField');
      // const { readOnly } = this.data('schema');

      ReactDOM.render(
        <SessionManagerField />,
        this[0]
      );
    }
  });
});
