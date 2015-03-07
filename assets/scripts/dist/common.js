
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
			var $trigger, $checkboxes, $master;

			$trigger = $( event.currentTarget );

			if ( 'all' === $trigger.val() ) {
				$master = $trigger;

				$checkboxes = $(':checkbox[name="' + $trigger.attr('name').escapeSelector() + '"]').not(':disabled').not( $trigger );

				$checkboxes.prop( 'checked', $master.prop('checked') );
			}
			else {
				$checkboxes = $(':checkbox[name="' + $trigger.attr('name').escapeSelector() + '"]').not(':disabled');

				$master = $checkboxes.filter('[value="all"]');

				if ( $master.length ) {

					$checkboxes = $checkboxes.not( $master );

					$master.prop( 'checked', ( $checkboxes.length === $checkboxes.filter(':checked').length ) );
				}
			}
		}
	};

	$('.chimplet-wrap').on( 'change.chimplet.toggle-checkboxes', ':checkbox[name$="[]"]', Checkbox.toggle );

}(jQuery));
