( function( $ ) {
	var accessToken = $('#wanderlist-geolocation-input').data('mapboxkey');
	$( '#wanderlist-geolocation-input' ).on('change', function() {
		var locationString = $( this ).attr( 'value' );
		var geocodeURI = 'https://api.mapbox.com/v4/geocode/mapbox.places/' + encodeURI( locationString ) + '.json?access_token=' + accessToken;
		$.ajax(geocodeURI, {
			success: function(response) {
				var selected = response.features[0].context;
				var city = response.features[0].text;
				for (var i = 0; i < selected.length; i++) {
					if ( selected[i].id.match( /^country/ ) ) {
						var country = selected[i].text;
					} else if ( selected[i].id.match( /^region/ ) ) {
						var region = selected[i].text;
					}
				}
				var lng = response.features[0].center[0];
				var lat = response.features[0].center[1];
				$( '#wanderlist-city' ).val( city );
				$( '#wanderlist-region' ).val( region );
				$( '#wanderlist-country' ).val( country );
				$( '#wanderlist-lng' ).val( lng );
				$( '#wanderlist-lat' ).val( lat );
				$( '#wanderlist-geocoder-message' ).addClass( 'success' );
				$( '#wanderlist-geocoder-message' ).find( '.place' ).text( response.features[0].place_name );
			},
			error: function(response) {
				$( '#wanderlist-geocoder-message' ).addClass( 'error' );
				$( '#wanderlist-geocoder-message' ).find( '.place' ).text( 'ERROR' );
			}
		})
	});
} )( jQuery );
