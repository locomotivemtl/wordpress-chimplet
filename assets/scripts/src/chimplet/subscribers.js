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
        sync: function (offset, event)
        {
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
                    console.log( response );
                },
                error: function( error ){
                    console.log( error );
                }
            });
        }
    };

    $('.chimplet-wrap').on( 'change.chimplet.toggle-subscribers-automation', ':checkbox[name$="[subscribers][automate]"]', $.proxy(Subscribers.sync, Subscribers, 0) );

}(jQuery));