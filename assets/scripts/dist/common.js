
/**
 * Utilities
 * ==========================================================================
 * @group  Chimplet
 * @author Locomotive
 */

// Escape a string for use in a CSS selector
String.prototype.escapeSelector = function( find )
{
	find = new RegExp( '([' + (find || '\[\]:') + '])' );
	return this.replace(find, '\\$1');
};

Array.prototype.powerSet = function()
{
	var i = 1,
	    j = 0,
	    sets = [],
	    size = this.length,
	    combination,
	    combinationsCount = ( 1 << size );

	for ( i = 1; i < combinationsCount; i++ ) {
		combination = [];

		for ( j = 0; j < size; j++ ) {
			if ( ( i & ( 1 << j ) ) ){
				combination.push( this[ j ] );
			}
		}

		sets.push( combination );
	}

	return sets;
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

/* global console, jQuery, chimpletL10n */

/**
 * Segment Combinations
 * ==========================================================================
 * @group  Chimplet
 * @author Locomotive
 */

(function ($) {
	'use strict';

	var Segmentation = {
		namespace: '.chimplet.count-segments',
		selector: 'chimplet[mailchimp][terms]',

		count: function (event)
		{
			var $checked, terms = [], segments = [];

			console.group( 'Segmentation.count' );

			$checked = $( ':checkbox:checked[name^="' + this.selector + '"]' );

			console.log( '$checked', $checked );

			if ( $checked.length ) {
				terms = $checked.map(function(){
					var $this = $(this);

					return ( $this.prop('indeterminate') ? null : ( 'all' === $this.val() ? null : $this.val() ) );
				}).toArray();

				console.log( 'terms', terms );

				if ( terms.length ) {
					segments = terms.powerSet();

					console.log( 'segments', segments );
				}
			}

			this.display( event, terms.length, segments.length );

			console.groupEnd();
		},
		display: function ( event, groupCount, segmentCount )
		{
			var $container, $ticker, e;

			console.group( 'Segmentation.display' );

			console.log( 'event', event );
			console.log( 'groupCount', groupCount );
			console.log( 'groupCount', segmentCount );

			if ( $.type( groupCount ) !== 'number' || $.type( segmentCount ) !== 'number' ) {
				e = jQuery.Event( 'change' + this.namespace, {
					target: $(':checkbox[name^="' + this.selector + '"]:first').get(0)
				} );
				console.groupEnd();
				return this.count( e );
			}

			$container = $( event.target ).closest('td');
			$ticker = $container.children('.chimplet-counter');

			if ( $ticker.length < 1 ) {
				$ticker = $('<p class="chimplet-counter"></p>').appendTo( $container );
			}

			$ticker.text( chimpletL10n.segmentCount.replace( '%1$d', groupCount ).replace( '%2$d', segmentCount ) );

			console.groupEnd();
		}
	};

	$('.chimplet-wrap').on( 'change' + Segmentation.namespace, ':checkbox[name^="' + Segmentation.selector + '"]', $.proxy( Segmentation.count, Segmentation ) );

	$(document).on( 'ready' + Segmentation.namespace, $.proxy( Segmentation.display, Segmentation ) );

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
