/* global console, jQuery, ajaxurl */

/**
 * Toggle Automation
 * ==========================================================================
 * @group  Chimplet
 * @author Locomotive
 */

(function ($) {
	'use strict';

	var Automation = {
		working: false,

		init: function( event )
		{
			if ( this.working ) {
				return;
			}

			this.$trigger  = $( event.target );
			this.$fieldset = this.$trigger.closest('fieldset');
			this.$notices  = this.$fieldset.children('.chimplet-notice');
			this.$checkbox = this.$fieldset.find('[type="checkbox"]');
			this.$tableRow = this.$fieldset.closest('tr');

			if ( this.$checkbox.prop('checked') ) {
				this.sync(0);
			}
			else {
				this.$trigger.attr( 'disabled', 'disabled' );
			}
		},
		showLoader: function()
		{
			if ( this.$notices.length ) {
				this.$notices.remove();
			}

			this.$tableRow.removeClass('form-invalid');

			this.$checkbox.attr( 'disabled', 'disabled' ).hide();
			this.$trigger.attr( 'disabled', 'disabled' ).addClass('chimplet-spinner');
		},
		hideLoader: function()
		{
			this.$trigger.removeAttr('disabled').removeClass('chimplet-spinner');
			this.$checkbox.removeAttr('disabled').show();
		},
		sync: function( offset, extra )
		{
			this.working = true;

			var formData = {
				action : this.$trigger.data('xhr-action'),
				nonce  : this.$trigger.data('xhr-nonce'),
				offset : offset,
				extra  : ( extra || null )
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
					this.sync( response.data.next, ( response.data.extra || null ) );
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

			console.groupEnd();
		},
		requestComplete: function() // data|jqXHR, textStatus, jqXHR|errorThrown, response
		{
			console.group( 'requestComplete' );

			var args = this.parseRequestArgs.apply( this, arguments );

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

			console.log( 'response', response );

			if ( this.hasNotices( response ) ) {
				if ( this.$notices.length ) {
					this.$notices.remove();
				}

				message = response.data.message;
				type = ( message.type || 'info' );
				text = ( message.text || ( $.type( message ) === 'string' ? message : false ) );

				if ( text ) {
					this.$notices = $('<p class="chimplet-notice panel-' + type + '">' + text + '</p>').appendTo( this.$fieldset );
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

	$('.chimplet-wrap').on( 'click.chimplet.sync', '[data-automation="sync"]', $.proxy( Automation.init, Automation ) );

}(jQuery));
