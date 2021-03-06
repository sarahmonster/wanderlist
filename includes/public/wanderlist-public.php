<?php
/**
 * Our public code, available only to the front-end of the site.
 *
 * This will collect some data for use in shortcodes, and load any
 * scripts and stylesheets we need to make things look pretty.
 *
 * @package Wanderlist
 */

/**
 * Enqueuing our scripts and styles.
 */
function wanderlist_scripts() {
	// Mapbox dependencies
	wp_enqueue_script( 'wanderlist-mapbox', 'https://api.tiles.mapbox.com/mapbox.js/v2.2.1/mapbox.js', array(), '20150719', true );
	wp_enqueue_style( 'wanderlist-mapbox-css', 'https://api.tiles.mapbox.com/mapbox.js/v2.2.1/mapbox.css', array(), '20150719', 'all' );

	// Custom js
	wp_enqueue_script( 'wanderlist-map', plugin_dir_url( __FILE__ ) . 'js/map.js', array( 'jquery', 'wanderlist-mapbox' ), '20150719', true );

	// Styles;
	wp_enqueue_style( 'wanderlist-style', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '20150914', 'all' );
}
add_action( 'wp_enqueue_scripts', 'wanderlist_scripts' );

/**
 * Get current location.
 * This looks for a location with an arrival date
 * earlier than today, and a departure date
 * later than today.
 * @todo: Determine intelligent handling if no departure date exists.
 */
function wanderlist_get_current_location( $output = 'simple' ) {
	$locations = get_posts( array(
		'posts_per_page' => 1,
		'post_type'      => 'wanderlist-location',
		'meta_query'     => array(
			array(
				'key'     => 'wanderlist-arrival-date',
				'value'   => wanderlist_today(),
				'compare' => '<=',
			),
			array(
				'key'     => 'wanderlist-departure-date',
				'value'   => wanderlist_today(),
				'compare' => '>=',
			),
		),
	) );
	// If we didn't find a current location, output our current home location
	if ( ! $locations ) {
		if ( 'text' === $output ) :
			return wanderlist_get_home( wanderlist_today() );
		elseif ( 'coords' == $output ) :
			return wanderlist_format_location( wanderlist_get_home( wanderlist_today(), 'id' ) );
		endif;
	} else {
		if ( 'text' === $output ) :
			//return $locations[0]->post_title;
			$city = get_post_meta( $locations[0]->ID, 'wanderlist-city', true );
			$country = wp_get_object_terms( $locations[0]->ID, 'wanderlist-country' );
			if ( '' !== $city && '' !== $country[0]->name ) :
				return $city . ', ' . $country[0]->name;
			else :
				return $city . $country[0]->name;
			endif;
		elseif ( 'coords' == $output ) :
			return wanderlist_format_location( $locations[0]->ID );
		endif;
	}
}

/*
 * Get our home location for a specific date.
 * This will be determined either by the (plaintext) value entered in the
 * Settings panel, or by a "home"-tagged location for the current date.
 */
function wanderlist_get_home( $date, $output = 'title' ) {

	$options = get_option( 'wanderlist_settings' );

	// Grab our most recent "home"-tagged location
	$homes = get_posts( array(
		'posts_per_page' => 1,
		'post_type'      => 'wanderlist-location',
		'orderby'        => 'meta_value post_date',
		'meta_key'       => 'wanderlist-arrival-date',
		'order'          => 'DESC',
		'tax_query'      => array(
			array(
				'taxonomy' => 'post_tag',
				'field'    => 'term_id',
				'terms'    => $options['wanderlist_home_tag'],
				'operator' => 'IN',
			),
		),
		'meta_query'     => array(
			array(
				'key'     => 'wanderlist-arrival-date',
				'value'   => $date,
				'compare' => '<=',
			),
		),
	) );

	// Show our most recent location tagged "home"
	if ( $homes ) :
		if ( 'title' === $output ) :
			return $homes[0]->post_title;
		elseif ( 'id' === $output ) :
			return $homes[0]->ID;
		endif;
	// Look for a "home" location entered via the plugin settings
	elseif ( $options['wanderlist_home'] ) :
		return $options['wanderlist_home'];
	// If we can't find anything, just default to the word "home"
	else :
		return esc_html__( 'Home', 'wanderlist' );
	endif;
}

/**
 * Show a list of locations.
 *
 * This will default to showing all upcoming locations.
 * Passing the limit and show params will change its output.
 */
function wanderlist_list_locations( $limit = null, $show = 'default' ) {

	$options = get_option( 'wanderlist_settings' );

	if ( 'all' === $show ) :
		$order = 'DESC';
	elseif ( 'past' === $show ) :
		$order = 'DESC';
		$compare = '<=';
	elseif ( 'upcoming' === $show ) :
		$order = 'ASC';
		$compare = '>';
	elseif ( 'default' === $show ) :
		$order = 'ASC';
		$compare = '>';
		$limit = $limit - 1;
	endif;

	$args = array(
		'post_type'      => 'wanderlist-location',
		'post_status'    => array( 'future', 'publish' ),
		'posts_per_page' => $limit,
		'orderby'        => 'meta_value post_date',
		'meta_key'       => 'wanderlist-arrival-date',
		'meta_value'     => wanderlist_today(),
		'meta_compare'   => $compare,
		'order'          => $order,
		'tax_query'      => array(
			array(
				'taxonomy' => 'post_tag',
				'field'    => 'term_id',
				'terms'    => $options['wanderlist_home_tag'],
				'operator' => 'NOT IN',
			),
		),
	);

	$location_query = new WP_Query( $args );

	$locations = '<dl>';

	// If we're not passing an upcoming/past parameter, show today's location
	if ( 'default' === $show ) :
		$locations .= wanderlist_get_current_location( 'coords' );
	endif;

	// Generate a list of posts
	while ( $location_query->have_posts() ) :
		$location_query->the_post();
		$locations .= wanderlist_format_location( get_the_ID() );
		wp_reset_postdata();
	endwhile;
	$locations .= '</dl>';

	return $locations;
}

/*
 * Since we output a list of locations from different spots, let's make a
 * fancy-pants reusable function so we don't write the same code over & over.
 */
function wanderlist_format_location( $id, $options = null ) {

	// Set default value for $today variable. (Is it "today"?)
	$today = false;

	// If we're still visiting somewhere, show that location with "today"
	if ( wanderlist_today() >= get_post_meta( $id, 'wanderlist-arrival-date', true )
	 && wanderlist_today() <= get_post_meta( $id, 'wanderlist-departure-date', true ) ) :
		$today = true;
		$output = '<dt>' . esc_html__( 'Today', 'wanderlist' ) . '</dt>';

	// If this is our home and we haven't entered a departure date, show it as "today" as well
	elseif ( wanderlist_is_home( $id ) && '' == get_post_meta( $id, 'wanderlist-departure-date', true ) ) :
		$output = '<dt>' . esc_html__( 'Today', 'wanderlist' ) . '</dt>';

	// If we've opted to show the date range, show the date range, silly!
	elseif ( 'range' === $options['date_format'] ) :
		$output = '<dt>' . esc_html( wanderlist_date( $id, 'range' ) ) . '</dt>' ;

	else : // Otherwise, display the date of arrival
		$output = '<dt>' . esc_html( wanderlist_date( $id, 'arrival' ) ) . '</dt>' ;
	endif;

	// Spit out coordinates for Mapbox to use
	$output .= '<dd class="wanderlist-place" data-city="' . esc_html( wanderlist_place_data( 'city', $id ) ) .'" data-lat="'. esc_attr( wanderlist_place_data( 'lat', $id ) ) . '" data-lng="' . esc_attr( wanderlist_place_data( 'lng', $id ) ) . '">';

	// Show a link to full post if: a) user hasn't opted out of showing, and
	// b) the place isn't one you're still busy exploring
	$options = get_option( 'wanderlist_settings' );
	$hide_link = false; // Our default is to show links.

	// Override the user has opted-out of links.
	if ( $options && array_key_exists( 'wanderlist_hide_link_to_location', $options ) ) :
		if ( '1' === $options['wanderlist_hide_link_to_location'] ) :
			$hide_link = true;
		endif;
	endif;

	if ( false === $hide_link && true !== $today ) :
		$output .= '<a href="' . esc_url( get_the_permalink( $id ) ) . '">';
	endif;

	// Show the title
	$output .= esc_html( get_the_title( $id ) );

	if ( false === $hide_link && true !== $today ) :
		$output .= '</a>';
	endif;

	// Display some icons if the location is home or loved
	if ( wanderlist_is_loved( $id ) ) :
		//$output .= '<span class="wanderlist-loved">&hearts;</span>';
	endif;

	if ( wanderlist_is_home( $id ) ) :
		//$output .= '<span class="wanderlist-home">&star;</span>';
	endif;

	$output .= '</dd>';
	return $output;
}

/**
 * Determine if a location is loved or not.
 *
 * This uses a special tag that the user sets via a settings panel,
 * then assigns to location as desired. It's basically just a way
 * of indicating a place that's close to your heart.
 *
 */
function wanderlist_is_loved( $location = null ) {

	// First, grab our "loved" tag
	$options = get_option( 'wanderlist_settings' );
	$loved_tag = $options['wanderlist_loved_tag'];

	// Then, check to see if the post has it
	if ( has_term( $loved_tag, 'post_tag', $location ) ) :
		return true;
	else :
		return false;
	endif;
}

/**
 * Determine if a location is home or not.
 *
 * This uses a special tag that the user sets via a settings panel,
 * then assigns to location as required. This allows us to remove
 * the user's home from various calculations, like cities visited.
 *
 */
function wanderlist_is_home( $location = null ) {

	// First, grab our "home" tag
	$options = get_option( 'wanderlist_settings' );
	$home_tag = $options['wanderlist_home_tag'];

	if ( has_term( $home_tag, 'post_tag', $location ) ) :
		return true;
	else :
		return false;
	endif;
}

/**
 * Show a list of trips.
 *
 * This will show a list of all trips completed.
 * Uses the "trip" taxonomy.
 */
function wanderlist_list_trips( $limit = null, $show = 'default' ) {

	$trips = get_terms( 'wanderlist-trip', array(
		'hide_empty'        => true,
		'childless'         => false,
	) );

	$output = '<ul>';
	foreach ( $trips as $trip ) :
		$output .= '<li><a href="'. esc_url( get_term_link( $trip, 'wanderlist-trip' ) ) . '" title="' . $trip->description . '">' . $trip->name . '</a></li>';
	endforeach;
	$output .= '</ul>';
	return $output;
}

/**
 * Count something. Right now, we're using it for countries, continents, and places,
 * but we'll probably expand in the future.
 */
function wanderlist_count( $thing ) {
	switch ( $thing ) {
		// Get every place we've visited up to today.
		case 'places':
			$args = array(
				'post_type'      => 'wanderlist-location',
				'meta_key'       => 'wanderlist-arrival-date',
				'meta_value'     => wanderlist_today(),
				'meta_compare'   => '<=',
				'posts_per_page' => -1,
			);
			$place_query = new WP_Query( $args );

			// Create an array of unique places.
			$places = array();
			while ( $place_query->have_posts() ) :
				$place_query->the_post();
				$city_name = get_post_meta( get_the_ID(), 'wanderlist-city', true );
				// If we've already been to this place, don't add it to our array.
				if ( ! in_array( $city_name, $places ) ) :
					$places[] = get_post_meta( get_the_ID(), 'wanderlist-city', true );
				endif;
				wp_reset_postdata();
			endwhile;
			return count( $places );
			break;

		case 'countries':
			// Count all countries in the database.
			$countries = get_terms( 'wanderlist-country', array(
				'hide_empty'        => true,
				'childless'         => true, // Only count countries that don't have sub-countries, since we may use these to store regional data at a later stage
			) );

			// Get an array of places we're visiting in the future.
			$options = get_option( 'wanderlist_settings' );

			$args = array(
				'posts_per_page' => -1,
				'post_type'      => 'wanderlist-location',
				'meta_key'       => 'wanderlist-arrival-date',
				'meta_value'     => wanderlist_today(),
				'meta_compare'   => '>',
			);

			$future_country_query = new WP_Query( $args );

			// For each place we're visiting in the future, get a list of countries.
			$future_countries = array();
			while ( $future_country_query->have_posts() ) :
				$future_country_query->the_post();
				$country = wanderlist_place_data( 'country', get_the_ID() );
				// Make sure each country only appears once in the array.
				if ( ! in_array( $country, $future_countries ) ) :
					$future_countries[] = $country;
				endif;
				wp_reset_postdata();
			endwhile;

			// For each of these future countries, check to see if we have any places visited in the past.
			foreach( $future_countries as $future_country ) :
				$args = array(
					'posts_per_page' => -1,
					'post_type'      => 'wanderlist-location',
					'meta_key'       => 'wanderlist-arrival-date',
					'meta_value'     => wanderlist_today(),
					'meta_compare'   => '<=',
					'tax_query'      => array(
								array(
									'taxonomy' => 'wanderlist-country',
									'field'    => 'slug',
									'terms'    => sanitize_title( $future_country ),
								),
							),
				);

				$visited_country_query = new WP_Query( $args );

				while ( $visited_country_query->have_posts() ) :
					$visited_country_query->the_post();
					$country = wanderlist_place_data( 'country', get_the_ID() );
					// Remove countries we've already visited from our array of future countries.
					if ( ( $key = array_search( $country, $future_countries ) ) !== false ) :
						unset( $future_countries[$key] );
					endif;

					wp_reset_postdata();
				endwhile;
			endforeach;

			// Finally, return a total count of countries, minus those countries we're only visiting in the future.
			return count( $countries ) - count( $future_countries );
			break;

		// Count all continents visited
		// @todo: Account for edge cases, of which there are many!
		case 'continents':

			// Get our countries to start
			$countries = get_terms( 'wanderlist-country', array(
				'hide_empty'        => true,
				'childless'         => true, // Only count countries that don't have sub-countries, since we may use these to store regional data at a later stage
			) );

			// Create an array of all continents we've visited
			$continents = array();
			foreach ( $countries as $country ) :
				$continent = wanderlist_iso_data( $country->name, 'continent' );
				// If we've found a continent and it's not already in our array, add it
				if ( $continent && ! in_array( $continent, $continents ) ) :
					$continents[] = $continent;
				endif;
			endforeach;
			return count( $continents );
			break;
	}
}

/**
 * Get additional country/continent information from our ISO country data.
 * This dataset was collated from ISO 3166 data sources on September 26, 2015.
 * @todo: Add localisation, test fuzzy matches, test against GeoCoder results
 * http://dev.maxmind.com/geoip/legacy/codes/country_continent/
 * http://dev.maxmind.com/geoip/legacy/codes/iso3166/
 */
function wanderlist_iso_data( $name, $return ) {
	require plugin_dir_path( __FILE__ ) . 'wanderlist-iso-data.php';
	$continent = null;
	if ( $country_names && array_key_exists( $name, $country_names ) ) :
		$country_code = $country_names[ $name ];
		$continent = $country_continents[ $country_code ];
	endif;

	if ( 'continent' === $return ) :
		return $continent;
	elseif ( 'country_code' === $return ) :
		return $country_code;
	endif;
}

/**
 * Show tag cloud of countries.
 * @todo: Use a manual function to arrange by time spent in each!
 * @todo: Maybe use flags, or something more visual?
 * @todo: Maybe use continents as well!
 */
function wanderlist_list_countries( $format = 'cloud' ) {

	if ( 'cloud' === $format ) :
		$args = array(
			'smallest'                  => 10,
			'largest'                   => 20,
			'unit'                      => 'pt',
			'number'                    => 50,
			'format'                    => 'flat',
			'separator'                 => "\n",
			'orderby'                   => 'name',
			'order'                     => 'ASC',
			'taxonomy'                  => 'wanderlist-country',
			'exclude'                   => $home_tag,
			'echo'                      => false,
			'topic_count_text_callback' => 'wanderlist_count_places_callback',
		);
		return wp_tag_cloud( $args );

	elseif ( 'list' === $format ) :
		$args = array(
			'orderby'            => 'name',
			'order'              => 'ASC',
			'style'              => 'none',
			'show_count'         => 1,
			'use_desc_for_title' => 1,
			'hierarchical'       => 0,
			'title_li'           => '',
			'show_option_none'   => esc_html__( 'No countries visited yet!', 'wanderlist' ),
			'number'             => null,
			'echo'               => 0,
			'taxonomy'           => 'wanderlist-country',
		);
		$country_list = wp_list_categories( $args );
		$countries = explode( '<br />', $country_list );
		$new_country_list = '';
		foreach ( $countries as $country ) :
			if ( '' !== trim( $country ) ) :
				$new_country_list .= '<li>' . preg_replace( '/\((\d+)\)$/m', '<span>${1}</span>', $country ) . '</li>';
			endif;
		endforeach;
		return '<ul>' . $new_country_list . '</ul>';
	endif;
}

/* Used to show better tooltips for country cloud. */
function wanderlist_count_places_callback( $count ) {
	return sprintf( _n( '%s place', '%s places', $count, 'wanderlist' ), number_format_i18n( $count ) );
}

/**
 * Display a map of some kind.
 * This will need to be automated to draw countries and show points.
 * @todo: Output a helpful error message if the Mapbox API key isn't set properly.
 */
function wanderlist_show_map( $overlay = null ) {
	$options = get_option( 'wanderlist_settings' );
	$output = '<div id="wanderlist-map" data-mapboxkey="' . esc_attr( $options['wanderlist_mapbox_key'] ) . '" data-mapid="' . esc_attr( $options['wanderlist_map_id'] ) . '" data-markercolour="' . esc_attr( $options['wanderlist_marker_colour'] ) . '" data-linecolour="' . esc_attr( $options['wanderlist_line_colour'] ) . '">';
	if ( 'upcoming' === $overlay ) :
		$output .= '<div class="wanderlist-widget wanderlist-location-widget">';
		$output .= '<h3>' . esc_html__( 'Adventure Ahoy!', 'wanderlist' ) . '</h3>';
		$output .= wanderlist_list_locations( '3' );
		$output .= '</div><!-- .flare-location-widget -->';
	endif;
	$output .= '</div><!-- .map -->';
	return $output;
}
