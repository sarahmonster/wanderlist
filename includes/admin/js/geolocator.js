( function( $ ) {
	// Grab our access token (we need an error if it isn't set!)
	if ( ! $( '#wanderlist-geolocation-input' ).data( 'mapboxkey' ) ) {
		console.log( 'No key set.' );

	} else {

		// Initialise map variables and settings
		L.mapbox.accessToken = $( '#wanderlist-geolocation-input' ).data( 'mapboxkey' );
		var map = L.mapbox.map( 'wanderlist-geolocation-map', 'mapbox.streets', {
			zoomControl: false,
			attributionControl: true
		} );

		// Disable zoom
		map.scrollWheelZoom.disable();

		// Re-add a zoom control at bottom right
		new L.Control.Zoom( {
			position: 'bottomright'
		} ).addTo( map );

		// Add an empty feature layer, so we can dynamically add points as needed
		var mapFeatures = L.mapbox.featureLayer().addTo( map );

		// Add a geocoder control for easier selection of places
		var geocoderControl = L.mapbox.geocoderControl( 'mapbox.places', {
	      autocomplete: true,
				keepOpen: true
	  } );

		// When the user clicks "locate me", find their location
		getCurrentLocation( document.getElementById( 'wanderlist-locate-user' ) );

		// When the user selects a place from our drop-down, parse that data into something we can use
		geocoderControl.on( 'select', function( e ) {
			parseLocationData( e.feature );
			$( '.leaflet-control-mapbox-geocoder.active' ).find( '.leaflet-control-mapbox-geocoder-results' ).hide();
		} );

		// Errors only seem to crop up when you don't enter any text, so we don't need to display them
		geocoderControl.on( 'error', function( e ) {
			console.log( e.error );
		} );

		// Add the geocoder control to our map
		map.addControl( geocoderControl );

		// When the search box regains focus, show the results list again
		$( '.leaflet-control-mapbox-geocoder-form' ).find( 'input' ).focus( function() {
			$( this ).parents( '.leaflet-control-mapbox-geocoder' ).find( '.leaflet-control-mapbox-geocoder-results' ).show();
		} );
	}

	/*
	 * Given a result (featureset) from the Mapbox geocoder
	 * this parses the data and outputs it to our HTML inputs,
	 * allowing the data to be stored in the database.
	 *
	 * It also gives the user a confirmation message.
	 */
	function parseLocationData( feature ) {
		var city;
		var country;
		var region;

		// If the context object doesn't exist, we're dealing with a limited dataset. Let's get what we can.
		if ( ! feature.context ) {
			if ( feature.id.match( /^country/ ) ) {
				country = feature.text;
			} else if ( feature.id.match( /^region/ ) ) {
				region = feature.text;
			}

		// Otherwise, we have a full result set, so let's parse its data
		} else {
			for ( var i = 0; i < feature.context.length; i++ ) {
				if ( feature.context[i].id.match( /^place/ ) ) {
					city = feature.context[i].text;
				}
				if ( feature.context[i].id.match( /^country/ ) ) {
					country = feature.context[i].text;
				}
				if ( feature.context[i].id.match( /^region/ ) ) {
					region = feature.context[i].text;
				}
			}

			// If the city hasn't already been set, we assume that we can get it via the feature name
			if ( ! city ) {
				city = feature.text;
			}
		}

		// Set lat and long
		var lng = feature.center[0];
		var lat = feature.center[1];

		// Output our values to our hidden form fields
		$( '#wanderlist-city' ).val( city );
		$( '#wanderlist-region' ).val( region );
		$( '#wanderlist-country' ).val( country );
		$( '#wanderlist-lng' ).val( lng );
		$( '#wanderlist-lat' ).val( lat );
		var placeName = feature.place_name;

		// Since Geocoder results for Vatican aren't accurate, we'll overwrite them
		if ( 'Città del Vaticano' === city ) {
			$( '#wanderlist-region' ).val( '' );
			$( '#wanderlist-country' ).val( 'Vatican City' );
			placeName = 'Città del Vaticano';
		}

		// Show a friendly success message and show point on map
		$( '#wanderlist-geocoder-message' ).removeClass( 'error' );
		$( '#wanderlist-geocoder-message' ).addClass( 'success' );
		$( '#wanderlist-geocoder-message' ).find( '.place' ).text( placeName );
		showPointOnMap( lat, lng );
	}

	/*
	 * Get the user's current location.
	 * This uses the HTML5 geolocation API, available on
 	 * most mobile browsers and modern browsers
	 * http://caniuse.com/#feat=geolocation
	 */
	 function getCurrentLocation( button ) {
		 // If our browser doesn't support geolocation, hide the button for a more graceful degredation
		 if ( ! navigator.geolocation ) {
			 button.innerHTML = 'Geolocation is not available';
			 $( button ).hide();
		 // Otherwise, locate user when the button is clicked
		 } else {
			 button.onclick = function( e ) {
				 e.preventDefault();
			   e.stopPropagation();
				 $( button ).siblings( '.wanderlist-loader' ).addClass( 'active' );
			   map.locate();
				};
			}

			// Once we've got a position, zoom and center the map on it, and add a single marker.
			map.on( 'locationfound', function( e ) {
				geocodeCoords( e.latlng.lat, e.latlng.lng );
				map.fitBounds( e.bounds );
				showPointOnMap( e.latlng.lat, e.latlng.lng );
				$( button ).siblings( '.wanderlist-loader' ).removeClass( 'active' );
			});

			// If the user chooses not to allow their location to be shared, display an error message.
			map.on( 'locationerror', function() {
				$( '#wanderlist-geocoder-message' ).removeClass( 'success' );
				$( '#wanderlist-geocoder-message' ).addClass( 'error' );
				$( '#wanderlist-geocoder-message' ).find( '.error-message' ).text( 'Please share your location so we can find you!' );
			});
		}

		/*
     * This reverse geocodes a set of lat/long coordinates.
     * Used when we've only been given the coordinates, and we
		 * need some more information.
     */
		 function geocodeCoords( lat, lon ) {
			 var geocodeURI = 'https://api.mapbox.com/geocoding/v5/mapbox.places/' + encodeURI( lon ) + ',' +  + encodeURI( lat ) + '.json?access_token=' + L.mapbox.accessToken;
			 $.ajax( geocodeURI, {
				success: function( response ) {
					parseLocationData( response.features[0] );
				},
				error: function( response ) {
					console.log( response )
				}
			})
		}

		/*
 		 * This shows the selected point on a map.
 	 	 *
 	 	 */
		 function showPointOnMap( lat, lon ) {
			 mapFeatures.setGeoJSON({
					 type: 'Feature',
					 geometry: {
							 type: 'Point',
							 coordinates: [ lon, lat ]
					 },
					 properties: {
							 'marker-color': '#64b450',
							 'marker-symbol': 'star',
							 'marker-size': 'small'
					 }
			 });
		 }

} )( jQuery );
