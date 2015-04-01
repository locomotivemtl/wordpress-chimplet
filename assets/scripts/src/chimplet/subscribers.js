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
        working: false,

        toggle: function( event )
        {
            if ( this.working ) {
                return;
            }

            this.$checkbox = $( event.target );
            this.$fieldset = this.$checkbox.closest('fieldset');
            this.$notices  = this.$fieldset.children('.chimplet-notice');
            this.$tableRow = this.$fieldset.closest('tr');

            if ( this.$checkbox.prop('checked') ) {
                this.prepareLoader();
                this.sync(0);
            }
        },
        prepareLoader: function()
        {
            if ( ! this.$spinner ) {
                this.$spinner = $('<div class="spinner alignleft"></div>');
                this.$spinner.prependTo( this.$checkbox.parent() );
            }
        },
        showLoader: function()
        {
            this.$tableRow.removeClass('form-invalid');

            this.$tableRow.addClass('chimplet-loading');
            this.$checkbox.attr( 'disabled', 'disabled' ).hide();
            this.$spinner.show();
        },
        hideLoader: function()
        {
            this.$spinner.hide();
            this.$checkbox.removeAttr('disabled').show();
            this.$tableRow.removeClass('chimplet-loading');
        },
        sync: function( offset )
        {
            this.working = true;

            var formData = {
                action: chimpletCommon.action,
                subscribersNonce: chimpletCommon.subscriberSyncNonce,
                offset: offset
            };

            this.jqxhr = $.ajax({
                type       : 'GET',
                url        : ajaxurl,
                dataType   : 'json',
                data       : formData,
                beforeSend : $.proxy( this.beforeRequest, this )
            })
            .done( $.proxy( this.requestDone, this ) )
            .fail( $.proxy( this.requestFailed, this ) )
            .always( $.proxy( this.requestComplete, this ) );
        },
        beforeRequest: function( jqXHR, settings )
        {
            console.group( 'beforeRequest' );

            console.log( 'settings', settings );
            console.log( 'jqXHR', jqXHR );

            this.showLoader();

            console.groupEnd();
        },
        requestDone: function( response, textStatus, jqXHR )
        {
            console.group( 'requestDone' );

            var errorThrown;

            console.log( 'response', response );
            console.log( 'textStatus', textStatus );
            console.log( 'jqXHR', jqXHR );

            if ( $.type( response ) !== 'object' || ! response.success ) {
                console.groupEnd();

                if ( response.data && !! response.data.message ) {
                    errorThrown = response.data.message.text || ( $.type( response.data.message ) === 'string' ? response.data.message : 'Unsuccessful Response' );
                }
                else {
                    errorThrown = 'Unsuccessful Response';
                }

                return this.requestFailed( jqXHR, textStatus, errorThrown, response );
            }

            if ( response.success ) {
                console.log( 'continue', ( response.data && 'next' in response.data && response.data.next > 0 ) );
                if ( response.data && 'next' in response.data && response.data.next > 0 ) {
                    this.sync( response.data.next );
                } else {
                    this.working = false;
                }
            }

            console.groupEnd();
        },
        requestFailed: function( jqXHR, textStatus, errorThrown, response )
        {
            console.group( 'requestFailed' );

            console.log( 'errorThrown', errorThrown );
            console.log( 'textStatus', textStatus );
            console.log( 'jqXHR', jqXHR );
            console.log( 'response', response );

            this.working = false;

            this.$tableRow.addClass('form-invalid');
            this.$checkbox.prop( 'checked', true );

            console.groupEnd();
        },
        requestComplete: function() // data|jqXHR, textStatus, jqXHR|errorThrown
        {
            console.group( 'requestComplete' );

            var jqXHR, response, textStatus, errorThrown, failed, args;

            args = this.parseRequestArgs.apply( this, arguments );

            console.log( 'args', args );

            this.resolveNotices( args.response );

            if ( this.working ) {
                return;
            }

            this.hideLoader();

            console.groupEnd();
        },
        hasNotices: function( response )
        {
            return ( $.type( response ) === 'object' && response.data.message );
        },
        resolveNotices: function( response )
        {
            console.group('resolveNotices');

            var message, type, text;

            this.$notices.remove();

            console.log( 'response', response );

            if ( this.hasNotices( response ) ) {
                message = response.data.message;
                type = ( message.type || 'info' );
                text = ( message.text || ( $.type( message ) === 'string' ? message : false ) );

                if ( text ) {
                    this.$notices = $('<p class="chimplet-notice panel-' + type + '" for="' + this.$checkbox.attr('id') + '">' + text + '</p>').appendTo( this.$fieldset );
                }
            }

            console.groupEnd();
        },
        parseRequestArgs: function()
        {
            var args = {
                failed      : true,
                jqXHR       : {},
                textStatus  : '',
                errorThrown : '',
                response    : null
            };

            // If the third argument is a string, the request failed
            // and the value is an error message: errorThrown;
            // otherwise it's probably the XML HTTP Request Object.

            // Error
            if ( arguments[2] && $.type( arguments[2] ) === 'string' ) {
                args.jqXHR       = arguments[0] || null;
                args.textStatus  = arguments[1] || null;
                args.errorThrown = arguments[2] || null;
                args.response    = arguments[3] || null;
                args.failed      = ( args.response && 'success' in args.response ? args.response.success : true );
            }
            // Done
            else {
                args.response    = arguments[0] || null;
                args.textStatus  = arguments[1] || null;
                args.jqXHR       = arguments[2] || null;
                args.errorThrown = null;
                args.failed      = ( args.response && 'success' in args.response ? ! args.response.success : false );
            }

            return args;
        }
    };

    $('.chimplet-wrap').on( 'change.chimplet.toggle-subscribers-automation', ':checkbox[name$="[subscribers][automate]"]', $.proxy(Subscribers.toggle, Subscribers) );

}(jQuery));
