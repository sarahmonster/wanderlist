<?php
/**
 * Functions that manipulate and display dates.
 *
 * @package Wanderlist
 */

/**
 * Get today's date.
 * This also sets our default timezone to whatever is set in our WordPress settings.
 */
function wanderlist_today() {
	date_default_timezone_set( get_option( 'timezone_string' ) );
	$today = date( 'Y-m-d' );
	return $today;
}

/**
 * Specify allowed HTML for date strings.
 * This allows us to easily use wp_kses in template files without getting complicated.
 */
function wanderlist_date_allowed_html() {
	$allowed_html = array(
		'a' => array(
			'href' => array(),
			'title' => array()
		),
		'span' => array(
			'class' => array(),
		),
		'br' => array(),
		'em' => array(),
		'strong' => array(),
	);
	return $allowed_html;
}

/**
* Determine the arrival/departure date for a particular location.
*
* Dates are stored as YYYY-MM-DD for easy ordering, but we display dates
* according to the user's preference (as much as possible). For date ranges, we
* format the dates according to our own preferences, but otherwise we respect
* the user setting from the plugin settings or the general WordPress settings.
*/
function wanderlist_date( $post, $type ) {

	// Grab our preferred date format
	$options = get_option( 'wanderlist_settings' );
	$date_format = esc_attr( $options['wanderlist_dateformat'] );

	// If the user hasn't specified a plugin-specific date format, we'll use the format from Settings > General
	if ( ! $date_format ) :
		$date_format = get_option( 'date_format' );
	endif;

	// Return the departure date.
	if ( 'departure' === $type ) :
		$date = get_post_meta( $post, 'wanderlist-departure-date', true );

		// If our departure date isn't entered, return early
		if ( ! $date ) :
			return false;
		endif;
		$formatted_date = date( $date_format, strtotime( $date ) );

	// Return a date range. (Currently doesn't much care for user-entered date format.)
	elseif ( 'range' === $type || 'range-ext' === $type ):

		// Determine some format-related variables.
		if ( 'range-ext' === $type ) :
			$separator = '<span class="highlight">' . esc_html_x( ' to ', 'Between two dates', 'wanderlist' ) . '</span>';
			$day = 'jS';
		else :
			$separator = '&ndash;';
			$day = 'j';
		endif;

		// Get our dates.
		$arrival_date = get_post_meta( $post, 'wanderlist-arrival-date', true );
		$departure_date = get_post_meta( $post, 'wanderlist-departure-date', true );

		$arrival_year = date( 'Y', strtotime( $arrival_date ) );
		$departure_year = date( 'Y', strtotime( $departure_date ) );
		$arrival_month = date( 'M', strtotime( $arrival_date ) );
		$departure_month = date( 'M', strtotime( $departure_date ) );

		// If the arrival and departure dates are the same, just show one date.
		if ( $arrival_date === $departure_date ) :
			$formatted_date = date( $date_format, strtotime( $arrival_date ) );

		// Are our months and years the same?
		elseif ( $arrival_month === $departure_month  && $arrival_year === $departure_year ) :
			$formatted_date = date( 'F ' . $day, strtotime( $arrival_date ) );
			$formatted_date .= $separator;
			$formatted_date .= date( $day, strtotime( $departure_date ) );
			$formatted_date .= date( ' Y', strtotime( $departure_date ) );

		// If only the years are the same....
		elseif ( $arrival_year === $departure_year ) :
			$formatted_date = date( 'F ' . $day, strtotime( $arrival_date ) );
			$formatted_date .= $separator;
			$formatted_date .= date( 'F ' . $day, strtotime( $departure_date ) );
			$formatted_date .= date( ' Y', strtotime( $departure_date ) );

		// Otherwise, just show the full date.
		else:
			$formatted_date = date( 'F ' . $day . ' Y', strtotime( $arrival_date ) );
			$formatted_date .= $separator;
			$formatted_date .= date( 'F ' . $day . ' Y', strtotime( $departure_date ) );
		endif;

	// If nothing is specified, return the arrival date.
	else :
		$date = get_post_meta( $post, 'wanderlist-arrival-date', true );
		// If our arrival date isn't entered, use the date the post was published instead
		if ( ! $date ) :
			$date = get_the_date();
		endif;
		$formatted_date = date( $date_format, strtotime( $date ) );
	endif;

	return $formatted_date;
}
