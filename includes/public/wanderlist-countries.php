<?php
/**
 * Functions that deal with country data.
 *
 * @package Wanderlist
 */

/**
 * Create an array of all countries visited.
 * This excludes countries we are going to visit, but haven't yet.
 */
function wanderlist_visited_countries() {

	// Get all countries in the database.
	$countries = get_terms( 'wanderlist-country', array(
		'hide_empty'        => true,
		'childless'         => true,
	 ) );

	 // Get an array of places we're visiting in the future.
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

	// Finally, remove all our future countries from our array of countries.
	foreach( $countries as $key => $country ) :
		if ( ( array_search( $country->name, $future_countries ) ) !== false ) :
			unset( $countries[$key] );
		endif;
	endforeach;

	return $countries;
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
	if ( $country_names[ $name ] ) :
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
 * Get extra country data.
 * This is extracted from JSON files copied from https://github.com/mledoze/countries.
 */
function wanderlist_get_country_data( $country_name, $data ) {
	$content = file_get_contents( plugin_dir_path( __FILE__ ) . '/js/country_data/countries.json' );
	$countries = json_decode( $content, true );

	// Iterate through our countries until we find one that matches the name.
	foreach( $countries as $country ) :
		if ( $country_name === $country['name']['common'] ) :
			switch ( $data ) {
				case 'continent':
					return $country['region'];
					break;
				case 'flag' :
					//return file_get_contents( plugin_dir_path( __FILE__ ) . 'custom/'. $country['cca3'] .'.svg' );
					return '<img class="flag" src="http://triggersandsparks:8888/wp-content/plugins/wanderlist/includes/public/svg/flags/' .  $country['cca3'] . '.svg' . '"/>';
					break;
				default :
					return $country['cca3'];
				}
			break;
		endif;
	endforeach;
}

/**
 * Show tag cloud or list of countries.
 * @todo: Use a manual function to arrange by time spent in each!
 * @todo: Maybe use continents as well!
 */
function wanderlist_list_countries() {
	$countries = wanderlist_visited_countries();
	foreach ( $countries as $country ) :
		$country_list .= '<li data-country-code="' . wanderlist_get_country_data( $country->name, 'code' ) . '">';
		$country_list .= wanderlist_get_country_data( $country->name, 'flag' );
		$country_list .= '<a href="' . esc_url( get_term_link( $country ) ) . '">' . $country->name;
		$country_list .=' <span>' . $country->count . '</span></a></li>';
	endforeach;
	return '<ul>' . $country_list . '</ul>';
}

/* Used to show better tooltips for country cloud. */
function wanderlist_count_places_callback( $count ) {
	return sprintf( _n( '%s place', '%s places', $count, 'wanderlist' ), number_format_i18n( $count ) );
}
