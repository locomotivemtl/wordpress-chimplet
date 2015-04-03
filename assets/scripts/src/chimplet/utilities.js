
/**
 * Utilities
 * ==========================================================================
 * @group  Chimplet
 * @author Locomotive
 */

// Escape a string for use in a CSS selector
String.prototype.escapeSelector = function( find )
{
	find = new RegExp( '([' + (find || '\[\]:') + '])' );
	return this.replace(find, '\\$1');
};

Array.prototype.powerSet = function()
{
	var i = 1,
	    j = 0,
	    sets = [],
	    size = this.length,
	    combination,
	    combinationsCount = ( 1 << size );

	for ( i = 1; i < combinationsCount; i++ ) {
		combination = [];

		for ( j = 0; j < size; j++ ) {
			if ( ( i & ( 1 << j ) ) ){
				combination.push( this[ j ] );
			}
		}

		sets.push( combination );
	}

	return sets;
};
