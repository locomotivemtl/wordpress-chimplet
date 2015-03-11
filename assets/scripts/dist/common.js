
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
        init: function(event)
        {
            this.$checkbox = $(event.target);
            if ( this.$checkbox.prop('checked') ) {
                // Let's hide the field and put a spinner
                this.$checkbox.hide();
                this.$spinner = $('<div class="spinner"></div>');
                this.$spinner.css('float', 'left');
                this.$spinner.prependTo(this.$checkbox.parent());
                this.$spinner.show();
                this.sync(0);
            }
        },
        sync: function (offset)
        {
            var t = this;
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
                    if ( response.success ) {
                        if ( response.next !== undefined && ! response.next ) {
                            t.sync( response.next );
                        } else {
                            t.$spinner.hide();
                            t.$checkbox.show();
                        }
                    }
                },
                error: function( error ){
                    console.log( error );
                }
            });
        }
    };

    $('.chimplet-wrap').on( 'change.chimplet.toggle-subscribers-automation', ':checkbox[name$="[subscribers][automate]"]', $.proxy(Subscribers.init, Subscribers) );

}(jQuery));