/**
 * Lazy Widgets
 *
 * Looks for widget placeholders and batches a request to the current page to
 * retrieve their content.
 */
jQuery(function($) {
	var hashes = [];

	// Find widget placehlders with a hash and put them in an arrray
	$('[data-widget]').each( function() {
		var widget = $(this);
		hashes.push( widget.data('widget') );
	});

	if ( hashes.length > 0 ) {
		// Make an AJAX call to the current page
		$.get( '', {
			action: 'get_widgets',
			hashes: hashes
		} ).done( function( data ) {
			if ( 'object' !== typeof data ) {
				throw 'Badly formed widget data received';
			}

			// `data` is an object with keys matching the widget hash and
			// values containing the widget HTML.
			$.each( data, function( hash ) {
				$('[data-widget="' + hash + '"]').replaceWith( data[hash] );
			} );
		});
	}
});