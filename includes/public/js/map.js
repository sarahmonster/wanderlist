( function($) {
	$( document ).ready( function() {
		L.mapbox.accessToken = $('#map').data('mapboxkey');

		// Get our user-determined options, and fall back to defaults if they haven't been set
		var mapID = $('#map').data('mapid');
		if ( !mapID ) {
			var mapID = 'mapbox.light';
		}
		var markerColour = $('#map').data('markercolour');
		if ( !markerColour ) {
			var markerColour = '#3ca0d3';
		}
		var lineColour = $('#map').data('linecolour');
		if ( !lineColour ) {
			var lineColour = '#3ca0d3';
		}

		// Initialise our map!
		var map = L.mapbox.map('map', mapID);

		// Create a GeoJSON array of all markers to be displayed on the map
		var markers = [];
		var count = 1;
		$('.wanderlist-place').each(function() {
			markers.push ( {
			  'type': 'Feature',
			  'geometry': {
					'type': 'Point',
					'coordinates': [$(this).data('lng'), $(this).data('lat')]
			  },
			  'properties': {
			      'title': $(this).data('city'),
			      'description': $(this).data('description'),
			      'marker-color': markerColour,
			      'marker-size': 'medium',
			      'marker-symbol': count + ""
			    }
		  })
		  count++;
		});

		// Add a new feature layer to show our markers
		var featureLayer = L.mapbox.featureLayer().setGeoJSON(markers).addTo(map);

		// Fit map to markers shown
		map.fitBounds(featureLayer.getBounds());

		// Add a line to map to show our basic path
		var line = [];

		featureLayer.eachLayer(function(marker) {
		  line.push(marker.getLatLng());
		});

		var polyline_options = {
		  color: lineColour,
		  weight: '3',
		  opacity: '0.9',
		  dashArray: "5, 10"
		};

		var polyline = L.polyline(line, polyline_options).addTo(map);

	});
})( jQuery );
