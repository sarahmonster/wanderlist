<?php
/**
 * Our private code, available only to the admin side of the site.
 *
 * This will register some custom metaboxes, and set up our geo-locator.
 *
 * @package Wanderlist
 */

/*
 * Enqueue the jQuery UI datepicker for admin usage.
 * This isn't the prettiest thing ever, but it's already available
 * in core so we'll use it at least until I get sick of it.
 */
function wanderlist_admin_scripts() {
	// Mapbox dependencies
	wp_enqueue_script( 'wanderlist-mapbox', 'https://api.tiles.mapbox.com/mapbox.js/v2.2.1/mapbox.js', array(), '20150719', true );
	wp_enqueue_style( 'wanderlist-mapbox-css', 'https://api.tiles.mapbox.com/mapbox.js/v2.2.1/mapbox.css', array(), '20150719', all );

	// Geolocation scripts
	wp_enqueue_script( 'wanderlist-geolocator-js', plugin_dir_url( __FILE__ ) . 'js/geolocator.js', array( 'jquery' ), time(), true );
	wp_enqueue_style( 'wanderlist-geolocator', plugin_dir_url( __FILE__ ) . 'css/geolocator.css' );

	// Date picker scripts
	wp_enqueue_style( 'jquery-ui' );
	wp_enqueue_style( 'jquery-ui-datepicker', plugin_dir_url( __FILE__ ) . 'css/datepicker.css' );
	wp_enqueue_script( 'wanderlist-moment-js', plugin_dir_url( __FILE__ ) . 'js/moment.js', array(), time(), true );
	wp_enqueue_script( 'wanderlist-datepicker-js', plugin_dir_url( __FILE__ ) . 'js/datepicker.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'wanderlist-moment-js' ), time(), true );
}
add_action( 'admin_enqueue_scripts', 'wanderlist_admin_scripts' );

/*
 * Register extra metaboxes on our "place" custom post type screens.
 */
function wanderlist_extra_metaboxes() {
	add_meta_box( 'wanderlist-geolocation', __( 'Geolocation', 'wanderlist' ), 'wanderlist_geolocation_box', 'wanderlist-location', 'side', 'high' );
	add_meta_box( 'wanderlist-dates', __( 'Dates', 'wanderlist' ), 'wanderlist_date_box', 'wanderlist-location', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'wanderlist_extra_metaboxes' );

/*
 * Create a custom metabox to input the location for this post.
 */
function wanderlist_geolocation_box() {
	global $post;

	// Nonce
	wp_nonce_field( basename( __FILE__ ), 'meta-box-nonce' );

	// Input field (also includes our user's Mapbox key so JS can grab it)
	// @todo: Spit out an error message if the API key isn't properly set.
	$options = get_option( 'wanderlist_settings' );
	echo '<input id="wanderlist-geolocation-input" data-mapboxkey="' . esc_attr( $options['wanderlist_mapbox_key'] ) . '" type="hidden" name="wanderlist-geolocation">';
	echo '<div id="wanderlist-geolocation-map"></div>';

	// Message field to show what's going on behind-the-scenes
	echo '<div id="wanderlist-geocoder-message" class="wanderlist-message">Your location has been set to <strong class="place"></strong>.</div>';

	// Hidden fields into which we can input data returned from our geocoder
	echo '<input id="wanderlist-city" name="wanderlist-city" type="hidden" value="' . esc_attr( get_post_meta( $post->ID, 'wanderlist-city', true ) ) . '" />';
	echo '<input id="wanderlist-region" name="wanderlist-region" type="hidden" value="' . esc_attr( get_post_meta( $post->ID, 'wanderlist-region', true ) ) . '" />';
	echo '<input id="wanderlist-lng" name="wanderlist-lng" type="hidden" value="' . esc_attr( get_post_meta( $post->ID, 'wanderlist-lng', true ) ) . '" />';
	echo '<input id="wanderlist-lat" name="wanderlist-lat" type="hidden" value="' . esc_attr( get_post_meta( $post->ID, 'wanderlist-lat', true ) ) . '" />';
	echo '<input id="wanderlist-country" name="wanderlist-country" type="hidden" value="' . esc_attr( ltrim( wanderlist_place_data( 'country' ), ', ' ) ) . '"/>';
}

/*
 * Create a custom metabox to input the date range.
 */
function wanderlist_date_box() {
	global $post;

	// Nonce
	wp_nonce_field( basename( __FILE__ ), 'meta-box-nonce' );

	// Arrival
	echo '<label>' . esc_html__( 'Arrival date', 'wanderlist' ) . '</label>';
	echo '<input type="text" name="wanderlist-arrival-date" value="' . esc_attr( get_post_meta( $post->ID, 'wanderlist-arrival-date', true ) ) . '" class="widefat wanderlist-datepicker" />';

	// Message field to show what's going on behind-the-scenes
	echo '<div class="wanderlist-message"></div>';

	// Departure
	echo '<label for="wanderlist-departure-date">' . esc_html__( 'Departure date (optional)', 'wanderlist' ) . '</label>';
	echo '<input type="text" name="wanderlist-departure-date" value="' . esc_attr( get_post_meta( $post->ID, 'wanderlist-departure-date', true ) ) . '" class="widefat wanderlist-datepicker" />';

	// Message field to show what's going on behind-the-scenes
	echo '<div class="wanderlist-message"></div>';
}

/*
 * Save our data once the user publishes or saves their post.
 */
function wanderlist_save_metabox_data( $post_id, $post, $update ) {
	// Make sure our nonce has been correctly set
	if ( ! isset( $_POST['meta-box-nonce'] ) || ! wp_verify_nonce( $_POST['meta-box-nonce'], basename( __FILE__ ) ) ) :
		return $post_id;
	endif;

	// Make sure the user has permissions to edit this post
	if ( ! current_user_can( 'edit_post', $post_id ) ) :
		return $post_id;
	endif;

	// If we're autosaving, don't worry about it right now
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) :
		return $post_id;
	endif;

	// Check to ensure that our post type is the same as our meta box post type
	if ( 'wanderlist-location' !== $post->post_type ) :
		return $post_id;
	endif;

	// If we've passed all those checks, we should be good to save! Let's go.
	if ( isset( $_POST['wanderlist-geolocation'] ) ) :
		$geolocation_value = sanitize_text_field( $_POST['wanderlist-geolocation'] );
		// Store city name, lat and long as post meta
		$lng_value = sanitize_text_field( $_POST['wanderlist-lng'] );
		$lat_value = sanitize_text_field( $_POST['wanderlist-lat'] );
		$city_value = sanitize_text_field( $_POST['wanderlist-city'] );
		$region_value = sanitize_text_field( $_POST['wanderlist-region'] );

		// Store our country as a term in our custom "country" taxonomy
		$country_value = sanitize_text_field( $_POST['wanderlist-country'] );
		// This creates a term if one doesn't already exist, or selects the existing term.
		$country_term = wp_create_term( $country_value, 'wanderlist-country' );
		// Make sure our term id is a string (because nobody wants to go to "148" country)
		if ( is_string( $country_term['term_id'] ) ) {
			$country_term['term_id'] = (int) $country_term['term_id'];
		}
		// Remove all country associations and set our new country.
		wp_set_object_terms( $post_id, $country_term['term_id'], 'wanderlist-country' );

		// Update post meta
		update_post_meta( $post_id, 'wanderlist-location-string', $geolocation_value );
		update_post_meta( $post_id, 'wanderlist-city', $city_value );
		update_post_meta( $post_id, 'wanderlist-region', $region_value );
		update_post_meta( $post_id, 'wanderlist-lat', $lat_value );
		update_post_meta( $post_id, 'wanderlist-lng', $lng_value );

	else :
		$geolocation_value = '';
		$lng_value = '';
		$lat_value = '';
		$city_value = '';
	endif;

	if ( isset( $_POST['wanderlist-arrival-date'] ) ) :
		$arrival_date_value = sanitize_text_field( $_POST['wanderlist-arrival-date'] );
	else :
		$arrival_date_value = '';
	endif;

	if ( isset( $_POST['wanderlist-departure-date'] ) ) :
		$departure_date_value = sanitize_text_field( $_POST['wanderlist-departure-date'] );
	else :
		$depature_date_value = '';
	endif;

	update_post_meta( $post_id, 'wanderlist-arrival-date', $arrival_date_value );
	update_post_meta( $post_id, 'wanderlist-departure-date', $departure_date_value );
}

add_action( 'save_post', 'wanderlist_save_metabox_data', 10, 3 );

/*
 * Include our settings page
*/
require plugin_dir_path( __FILE__ ) . 'wanderlist-settings.php';
