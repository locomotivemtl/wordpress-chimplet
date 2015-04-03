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
