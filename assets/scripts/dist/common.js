
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
			var $trigger, $targets, $shown, $hidden, condition, value;

			$trigger = $( event.currentTarget );

			condition = 'data-condition-' + $trigger.data( 'condition-key' );

			value = $trigger.val();

			$targets = $( '[' + condition + ']' );

			$shown  = $targets.filter('[' + condition + '="' + value + '"]').removeClass( 'hidden' );

			$hidden = $targets.not('[' + condition + '="' + value + '"]').addClass( 'hidden' );
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
        sync: function (offset, event)
        {
            $.ajax({
                type: 'GET',
                url: ajaxurl,
                data: {
                    action: chimpletCommon.action,
                    subscribersNonce: chimpletCommon.subscriberSyncNonce,
                    offset: offset
                },
                dataType: 'json',
                success: function ( response ) {
                    console.log( response )
                },
                error: function( error ){
                    console.log( error );
                }
            });
        }
    };

    $('.chimplet-wrap').on( 'change.chimplet.toggle-subscribers-automation', ':checkbox[name$="[subscribers][automate]"]', $.proxy(Subscribers.sync, Subscribers, 0) );

}(jQuery));