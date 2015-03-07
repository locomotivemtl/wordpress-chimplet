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
			console.group( 'Condition' );

			var $trigger, $targets, $shown, $hidden, condition, value;

			$trigger = $( event.currentTarget );

			condition = 'data-condition-' + $trigger.data( 'condition-key' );

			value = $trigger.val();

			$targets = $( '[' + condition + ']' );

			$shown  = $targets.filter('[' + condition + '="' + value + '"]').removeClass( 'hidden' );

			$hidden = $targets.not('[' + condition + '="' + value + '"]').addClass( 'hidden' );

			console.groupEnd();
		}
	};

	$('.chimplet-wrap').on( 'change.chimplet.condition', '[data-condition-key]', Condition.toggle );

}(jQuery));
