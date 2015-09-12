( function($) {
	$( document ).ready( function() {
		L.mapbox.accessToken = $('#map').data('mapboxkey');
		//var map = L.mapbox.map( 'map', 'sarahsemark.mok16607' ).setView( [49.46, -35.35], 3 );
		var map = L.mapbox.map('map', 'mapbox.light');

		$('.wanderlist-place').each(function() {
			var marker = L.marker([$(this).data('lat'), $(this).data('lng')], {
				icon: L.mapbox.marker.icon({
			      // 'marker-color': '#f86767'
			  	})
			}).addTo(map);
		});
	});
})( jQuery );
