( function( $ ) {
	// Grab our access token (we need an error if it isn't set!)
	if ( ! $( '#wanderlist-geolocation-input' ).data( 'mapboxkey' ) ) {
		console.log( 'No key set.' );

	} else {
		// Initialise map variables and settings
		L.mapbox.accessToken = $( '#wanderlist-geolocation-input' ).data( 'mapboxkey' );
		//var geocoderURL = 'https://api.mapbox.com/geocoding/v5/mapbox.places/{lon},{lat}.json?access_token=' + L.mapbox.accessToken;

		var map = L.mapbox.map( 'wanderlist-geolocation-map', 'mapbox.streets', {
			zoomControl: false,
			attributionControl: false
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
			console.log( e.feature );
		} );

		// When we have some kind of error, show an error message
		geocoderControl.on( 'error', function( e ) {
			$( '#wanderlist-geocoder-message' ).removeClass( 'success' );
			$( '#wanderlist-geocoder-message' ).addClass( 'error' );
			console.log( e.error );
			$( '#wanderlist-geocoder-message' ).find( '.place' ).text( 'ERROR' );
		} );

		// Add the geocoder control to our map
		map.addControl( geocoderControl );
	}

	/*
	 * Given a result (featureset) from the Mapbox geocoder
	 * this parses the data and outputs it to our HTML inputs,
	 * allowing the data to be stored in the database.
	 *
	 * It also gives the user a confirmation message.
	 */
	function parseLocationData( feature ) {
		// If the context object doesn't exist, we're dealing with a limited dataset. Let's get what we can.
		if ( !feature.context ) {
			if ( feature.id.match( /^country/ ) ) {
				var country = feature.text;
			} else if ( feature.id.match( /^region/ ) ) {
				var region = feature.text;
			}
		// Otherwise, we have a full result set, so let's parse its data
		} else {
			var city = feature.text;
			for (var i = 0; i < feature.context.length; i++) {
				if ( feature.context[i].id.match( /^country/ ) ) {
					var country = feature.context[i].text;
				} else if ( feature.context[i].id.match( /^region/ ) ) {
					var region = feature.context[i].text;
				}
			}
		}
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

		// Show a friendly success message
		$( '#wanderlist-geocoder-message' ).removeClass( 'error' );
		$( '#wanderlist-geocoder-message' ).addClass( 'success' );
		$( '#wanderlist-geocoder-message' ).find( '.place' ).text( placeName );

	}

	/*
	 * Get the user's current location.
	 * This uses the HTML5 geolocation API, available on
 	 * most mobile browsers and modern browsers
	 * http://caniuse.com/#feat=geolocation
	 */
	 function getCurrentLocation( button ) {
		 // If our browser doesn't support geolocation, hide the button
		 if ( !navigator.geolocation ) {
			 button.innerHTML = 'Geolocation is not available';
			 $( button ).hide();
		 // Otherwise, locate user when the button is clicked
		 } else {
			 button.onclick = function( e ) {
				 e.preventDefault();
			   e.stopPropagation();
			   map.locate();
				};
			}

			// Once we've got a position, zoom and center the map
			// on it, and add a single marker.
			map.on( 'locationfound', function( e ) {
				//console.log( e );

				geocodeCoords( e.latlng.lat, e.latlng.lng );

			    map.fitBounds( e.bounds );

			    mapFeatures.setGeoJSON({
			        type: 'Feature',
			        geometry: {
			            type: 'Point',
			            coordinates: [e.latlng.lng, e.latlng.lat]
			        },
			        properties: {
			            'title': 'Here I am!',
			            'marker-color': '#ff8888',
			            'marker-symbol': 'star'
			        }
			    });
			});

			// If the user chooses not to allow their location
			// to be shared, display an error message.
			map.on('locationerror', function() {
			    button.innerHTML = 'Position could not be found';
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
					console.log( response.features[0] );
					parseLocationData( response.features[0] );
				},
				error: function( response ) {
					console.log( response )
				}
			})
		}

} )( jQuery );
