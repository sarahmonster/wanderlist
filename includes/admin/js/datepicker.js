( function( $ ) {
	$( document ).ready(function() {
		// Initialise our datepicker element(s)
		$( '.wanderlist-datepicker' ).datepicker( {
			dateFormat: 'yy-mm-dd',
			showAnim: 'slideDown',
		});

		// When the user clicks out of the input field, we'll want to validate the date
		$('.wanderlist-datepicker').on('change', function() {
			var date = $(this).val();
			var message = $(this).next('.wanderlist-message');

			// Remove any existing notices
			$(message).removeClass('error');
			$(message).removeClass('notice');

			// If date entered is valid, we'll want to ensure it's in the correct format
			if (moment(date).isValid()) {

				// Check to see if the date is in the correct format, and convert if needed
				if (!moment(date, 'YYYY-MM-DD', true).isValid()) {
					var newDate = moment(date).format('YYYY-MM-DD');
					$(this).val(newDate)
					$(message).addClass('notice');
					$(message).text('Your date has been converted to the correct format.');
				}

			// If it's not valid, we'll pop up a quick error message
			} else {
				$(message).addClass('error');
				$(message).text('Please enter a proper date.');
			}
		});
	});
} )( jQuery );
