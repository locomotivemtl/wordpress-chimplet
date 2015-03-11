/* global jQuery, ajaxurl, chimpletCommon */

/**
 * Toggle Checkboxes
 * ==========================================================================
 * @group  Chimplet
 * @author Locomotive
 */

(function ($) {
    'use strict';

    var Subscribers = {
        init: function(event)
        {
            this.$checkbox = $(event.target);
            if ( this.$checkbox.prop('checked') ) {
                // Let's hide the field and put a spinner
                this.$checkbox.hide();
                this.$spinner = $('<div class="spinner"></div>');
                this.$spinner.css('float', 'left');
                this.$spinner.prependTo(this.$checkbox.parent());
                this.$spinner.show();
                this.sync(0);
            }
        },
        sync: function (offset)
        {
            var t = this;
            $.ajax({
                type: 'GET',
                url: ajaxurl,
                data: {
                    action: chimpletCommon.action,
                    subscribersNonce: chimpletCommon.subscriberSyncNonce,
                    offset: offset
                },
                dataType: 'json',
                success: function ( response ) {
                    if ( response.success ) {
                        if ( response.next !== undefined && ! response.next ) {
                            t.sync( response.next );
                        } else {
                            t.$spinner.hide();
                            t.$checkbox.show();
                        }
                    }
                },
                error: function(){
                    t.$spinner.hide();
                    t.$checkbox.prop('checked', true);
                }
            });
        }
    };

    $('.chimplet-wrap').on( 'change.chimplet.toggle-subscribers-automation', ':checkbox[name$="[subscribers][automate]"]', $.proxy(Subscribers.init, Subscribers) );

}(jQuery));