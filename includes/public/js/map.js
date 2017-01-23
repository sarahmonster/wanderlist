( function($) {
	$( document ).ready( function() {

		// Make sure we're actually trying to show a map on the page
		if ( $('#wanderlist-map').data('mapboxkey') ) {
			L.mapbox.accessToken = $('#wanderlist-map').data('mapboxkey');

			// Get our user-determined options, and fall back to defaults if they haven't been set
			var mapID = $('#wanderlist-map').data('mapid');
			if ( !mapID ) {
				var mapID = 'mapbox.light';
			}
			var markerColour = $('#wanderlist-map').data('markercolour');
			if ( !markerColour ) {
				var markerColour = '#3ca0d3';
			}
			var lineColour = $('#wanderlist-map').data('linecolour');
			if ( !lineColour ) {
				var lineColour = '#3ca0d3';
			}

			// Initialise our map!
			var map = L.mapbox.map('wanderlist-map', mapID);

			// Disable zoom when scrolling
			map.scrollWheelZoom.disable();

			// Figure out what page we're on. This will determine how we display markers.
			var page;
			if ( $('body').hasClass('page') ) {
				page = 'page';
			} else if ( $('body').hasClass('tax-wanderlist-trip') ) {
				page = 'trip';
			}

			// Create a GeoJSON array of all markers to be displayed on the map
			var markers = [];
			var count = 1;
			$('.wanderlist-place').each(function() {
				var markerSymbol;
				if ( 'trip' === page ) {
					markerSymbol = count + "";
				} else {
					markerSymbol = 'star';
				}
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
				      'marker-size': 'small',
				      'marker-symbol': markerSymbol,
				    }
			  })
			  count++;
			});

			// Add a new feature layer to show our markers
			var featureLayer = L.mapbox.featureLayer().setGeoJSON(markers).addTo(map);

			// If we only show one place, manually set the zoom level so it isn't too close
			if ( count <= 2 ) {
				map.setView([$('.wanderlist-place').last().data('lat'), $('.wanderlist-place').last().data('lng')], 6);
			// Otherwise, fit map to markers displayed
			} else {
				map.fitBounds(featureLayer.getBounds());
			}

			// Add a line to map to show our basic path (only on trip pages!)
			if ( 'trip' === page ) {
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
			}

			// Disable drag & zoom handlers so you can still scroll past a map on a touch device.
			if (map.tap) {
				map.dragging.disable();
				map.touchZoom.disable();
				map.tap.disable();
			}
		}

	});
})( jQuery );
