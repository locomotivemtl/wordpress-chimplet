
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
