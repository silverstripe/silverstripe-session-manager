jQuery.entwine('ss', function($) {
    $('.grid-field .action.gridfield-button-revoke-session').entwine({
        onclick: function(e) {
            var message = (this.attr('data-current-session') ? 'Are you sure you want to revoke this session? This action will log you out immediately' : 'Are you sure you want to revoke this session?');
            if(!confirm(message)) {
                e.preventDefault();
                return false;
            } else {
                this._super(e);
            }
        }
    });
});
