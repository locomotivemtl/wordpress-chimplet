
/**
 * Utilities
 * ==========================================================================
 * @group  Chimplet
 * @author Locomotive
 */

// Escape a string for use in a CSS selector
String.prototype.escapeSelector = function ( find ) {
	find = new RegExp( '([' + (find || '\[\]:') + '])' );
	return this.replace(find, '\\$1');
};

/* global console, jQuery */

/**
 * Conditional Display
 * ==========================================================================
 * @group  Chimplet
 * @author Locomotive
 */

(function ($) {
	'use strict';

	var Condition = {
		toggle: function (event)
		{
			var $trigger, $checkboxes, $scope, $targets, $shown, $hidden, condition, value;

			console.group( 'Condition.toggle()' );

			$trigger = $( event.target );

			condition = 'data-condition-' + $trigger.data( 'condition-key' );

			if ( $trigger.is(':radio') && ! $trigger.prop('checked') ) {
				value = null;
			}
			if ( $trigger.is(':checkbox') ) {
				$scope = $trigger.parents('[data-checkbox-scoped]');

				$checkboxes = $( ':checkbox:checked[name="' + $trigger.attr('name').escapeSelector() + '"]', ( $scope.length ? $scope : null ) ).not(':disabled');

				console.log( '$scope',      $scope );
				console.log( '$checkboxes', $checkboxes );

				value = $checkboxes.map(function(){
					return $(this).val();
				}).toArray().join();
			}
			else {
				value = $trigger.val();
			}

			$targets = $( '[' + condition + ']' );

			console.log( '$trigger',  $trigger );
			console.log( 'condition', condition );
			console.log( 'value',     value );
			console.log( '$targets',  $targets );

			console.log( 'targets', '[' + condition + '="' + value + '"]' );

			$shown  = $targets.filter('[' + condition + '="' + value + '"]').removeClass( 'hidden' );
			$hidden = $targets.not('[' + condition + '="' + value + '"]').addClass( 'hidden' );

			console.log( '$shown',  $shown );
			console.log( '$hidden', $hidden );

			console.groupEnd();
		}
	};

	$('.chimplet-wrap').on( 'change.chimplet.condition', '[data-condition-key]', Condition.toggle );

}(jQuery));

/* global jQuery */

/**
 * Toggle Checkboxes
 * ==========================================================================
 * @group  Chimplet
 * @author Locomotive
 */

(function ($) {
	'use strict';

	var Checkbox = {
		toggle: function (event)
		{
			var $trigger, $checked, $checkboxes, $master, $scope;

			$trigger = $( event.currentTarget );

			$scope = $trigger.parents('[data-checkbox-scoped]');

			$checkboxes = $( ':checkbox[name="' + $trigger.attr('name').escapeSelector() + '"]', ( $scope.length ? $scope : null ) ).not(':disabled');

			if ( 'all' === $trigger.val() ) {
				$master = $trigger;

				$checkboxes = $checkboxes.not( $master );

				$checkboxes.prop( 'checked', $master.prop('checked') );
			}
			else {
				$master = $checkboxes.filter('[value="all"]');
				$checkboxes = $checkboxes.not( $master );

				$checked = $checkboxes.filter(':checked');

				if ( $master.length ) {

					$checkboxes = $checkboxes.not( $master );

					$master
						.prop( 'checked', $checked.length > 0 )
						.prop( 'indeterminate', ( $checked.length > 0 && $checked.length < $checkboxes.length ) );
				}
			}
		}
	};

	$('.chimplet-wrap').on( 'change.chimplet.toggle-checkboxes', ':checkbox[name$="[]"]', Checkbox.toggle );

}(jQuery));

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

			console.log( '$notices', this.$notices );

			if ( this.$checkbox.prop('checked') ) {
				this.sync(0);
			}
			else {
				this.$trigger.attr( 'disabled', 'disabled' );
			}
		},
		showLoader: function()
		{
			this.$notices.remove();
			this.$tableRow.removeClass('form-invalid');

			this.$checkbox.attr( 'disabled', 'disabled' ).hide();
			this.$trigger.attr( 'disabled', 'disabled' ).addClass('chimplet-spinner');
		},
		hideLoader: function()
		{
			this.$trigger.removeAttr('disabled').removeClass('chimplet-spinner');
			this.$checkbox.removeAttr('disabled').show();
		},
		sync: function( offset )
		{
			this.working = true;

			var formData = {
				action : this.$trigger.data('xhr-action'),
				nonce  : this.$trigger.data('xhr-nonce'),
				offset : offset
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
