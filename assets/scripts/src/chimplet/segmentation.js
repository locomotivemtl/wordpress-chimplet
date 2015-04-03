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
