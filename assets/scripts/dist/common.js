
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


/**
 * Toggle Checkboxes
 * ==========================================================================
 * @group  Chimplet
 * @author Locomotive
 */

+function ($) {
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

}(jQuery);
