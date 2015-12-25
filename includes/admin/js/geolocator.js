( function( $ ) {
	// Grab our access token (we need an error if it isn't set!)
	if ( ! $( '#wanderlist-geolocation-input' ).data( 'mapboxkey' ) ) {
		console.log( 'No key set.' );

	} else {
		// Initialise map variables and settings
		L.mapbox.accessToken = $( '#wanderlist-geolocation-input' ).data( 'mapboxkey' );
		var map = L.mapbox.map( 'wanderlist-geolocation-map', 'mapbox.streets' );
		map.scrollWheelZoom.disable(); // Disable zoom
		var geocoderControl = L.mapbox.geocoderControl( 'mapbox.places', {
	      autocomplete: true,
				keepOpen: true
	  } );

		// When the user selects a place from our drop-down, parse that data into something we can use
		geocoderControl.on( 'select', function( event ) {
			parseLocationData( event.feature );
		} );

		// When we have some kind of error, show an error message
		geocoderControl.on( 'error', function( event ) {
			$( '#wanderlist-geocoder-message' ).removeClass( 'success' );
			$( '#wanderlist-geocoder-message' ).addClass( 'error' );
			console.log( event.error );
			$( '#wanderlist-geocoder-message' ).find( '.place' ).text( 'ERROR' );
		} );

		// Add the control to our map
		map.addControl( geocoderControl );
	}

	// Function to process & store the results of our geocoder.
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

} )( jQuery );
