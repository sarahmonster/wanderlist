( function($) {
	$( document ).ready( function() {
		L.mapbox.accessToken = $('#map').data('mapboxkey');
		var map = L.mapbox.map( 'map', 'sarahsemark.mok16607' ).setView( [49.46, -35.35], 3 );
	});
})( jQuery );
