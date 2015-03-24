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
