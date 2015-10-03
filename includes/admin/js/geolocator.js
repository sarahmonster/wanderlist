( function( $ ) {
	var accessToken = $('#wanderlist-geolocation-input').data('mapboxkey');
	$( '#wanderlist-geolocation-input' ).on('change', function() {
		var locationString = $( this ).attr( 'value' );
		var geocodeURI = 'https://api.mapbox.com/v4/geocode/mapbox.places/' + encodeURI( locationString ) + '.json?access_token=' + accessToken;
		$.ajax(geocodeURI, {
			success: function(response) {
				var result = response.features[0];
				var selected = result.context;
				// If the context object doesn't exist, we're dealing with a limited dataset. Let's get what we can.
				if (!selected) {
					if ( result.id.match( /^country/ ) ) {
						var country = result.text;
					} else if ( result.id.match( /^region/ ) ) {
						var region = result.text;
					}
				// We have a full result set, so let's parse its data
				} else {
					var city = result.text;
					for (var i = 0; i < selected.length; i++) {
						if ( selected[i].id.match( /^country/ ) ) {
							var country = selected[i].text;
						} else if ( selected[i].id.match( /^region/ ) ) {
							var region = selected[i].text;
						}
					}
				}
				var lng = response.features[0].center[0];
				var lat = response.features[0].center[1];
				$( '#wanderlist-city' ).val( city );
				$( '#wanderlist-region' ).val( region );
				$( '#wanderlist-country' ).val( country );
				$( '#wanderlist-lng' ).val( lng );
				$( '#wanderlist-lat' ).val( lat );
				var placeName = response.features[0].place_name;

				// Vatican doesn't return the correct results, so we're going to do it manually
				if ( 'Città del Vaticano' === city ) {
					$( '#wanderlist-region' ).val( '' );
					$( '#wanderlist-country' ).val( 'Vatican City' );
					placeName = 'Città del Vaticano';
				}

				$( '#wanderlist-geocoder-message' ).addClass( 'success' );
				$( '#wanderlist-geocoder-message' ).find( '.place' ).text( placeName );
			},
			error: function(response) {
				$( '#wanderlist-geocoder-message' ).addClass( 'error' );
				$( '#wanderlist-geocoder-message' ).find( '.place' ).text( 'ERROR' );
			}
		})
	});
} )( jQuery );
