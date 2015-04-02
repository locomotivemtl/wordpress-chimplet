/* global jQuery */

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
			var $trigger, $targets, $shown, $hidden, condition, value;

			$trigger = $( event.currentTarget );

			condition = 'data-condition-' + $trigger.data( 'condition-key' );

			if ( ( $trigger.is(':checkbox') || $trigger.is(':radio') ) && ! $trigger.prop('checked') ) {
				value = '';
			}
			else {
				value = $trigger.val();
			}

			$targets = $( '[' + condition + ']' );

			console.group( 'Condition.toggle()' );

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
