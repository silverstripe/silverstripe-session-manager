// Manages rendering SessionManagerFields in lieu of React support in ModelAdmin
import React from 'react';
import ReactDOM from 'react-dom';
import {loadComponent} from 'lib/Injector'; // eslint-disable-line

const FIELD_SELECTOR = '.js-injector-boot [data-field-type="session-manager-field"]';

function addLearnMoreLink() {
    $('#title-Form_ItemEditForm_SessionManagerField').append(`
<br/>
<a
href='https://github.com/silverstripe/silverstripe-session-manager'
target="_blank"
>
    Learn More
</a>
`);
}

function injectReactSessionManagerField(field) {
    const SessionManagerField = loadComponent('SessionManagerField');
    const {
        readOnly, schema: {
            loginSessions
        }
    } = field.data('schema');

    ReactDOM.render(
        <SessionManagerField
            loginSessions={loginSessions}
        />,
        field[0]
    );
}

window.jQuery.entwine('ss', ($) => {
    $(FIELD_SELECTOR).entwine({
        onmatch() {
            // add the learn more link
            addLearnMoreLink();

            // inject the react session manager field
            injectReactSessionManagerField(this);
        }
    });
});
