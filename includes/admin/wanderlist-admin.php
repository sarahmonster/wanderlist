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
	wp_enqueue_script( 'wanderlist-geolocator-js', plugin_dir_url( __FILE__ ) . 'js/geolocator.js', array( 'jquery' ), time(), true );
	wp_enqueue_style( 'wanderlist-geolocator', plugin_dir_url( __FILE__ ) . 'css/geolocator.css' );
	wp_enqueue_script( 'wanderlist-datepicker-js', plugin_dir_url( __FILE__ ) . 'js/datepicker.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ), time(), true );
	wp_enqueue_style( 'jquery-ui' );
	wp_enqueue_style( 'jquery-ui-datepicker', plugin_dir_url( __FILE__ ) . 'css/datepicker.css' );
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

	// Input field
	echo '<input id="wanderlist-geolocation-input" type="text" name="wanderlist-geolocation" value="' . esc_attr( get_post_meta( $post->ID, 'wanderlist-geolocation', true ) ) . '" class="widefat" />';

	// Message field to show what's going on behind-the-scenes
	echo '<div id="wanderlist-geocoder-message">Your location has been set to <strong class="place"></strong>.</div>';

	// Hidden fields into which we can input data returned from our geocoder
	echo '<input id="wanderlist-city" name="wanderlist-city" type="hidden" value="' . esc_attr( get_post_meta( $post->ID, 'wanderlist-city', true ) ) . '" />';
	echo '<input id="wanderlist-region" name="wanderlist-region" type="hidden" value="' . esc_attr( get_post_meta( $post->ID, 'wanderlist-region', true ) ) . '" />';
	echo '<input id="wanderlist-lng" name="wanderlist-lng" type="hidden" value="' . esc_attr( get_post_meta( $post->ID, 'wanderlist-lng', true ) ) . '" />';
	echo '<input id="wanderlist-lat" name="wanderlist-lat" type="hidden" value="' . esc_attr( get_post_meta( $post->ID, 'wanderlist-lat', true ) ) . '" />';

	echo '<input id="wanderlist-country" name="wanderlist-country" type="hidden" value="' . esc_attr( ltrim( wanderlist_get_country(), ', ' ) ) . '"/>';
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

	// Departure
	echo '<label>' . esc_html__( 'Departure date (optional)', 'wanderlist' ) . '</label>';
	echo '<input type="text" name="wanderlist-departure-date" value="' . esc_attr( get_post_meta( $post->ID, 'wanderlist-departure-date', true ) ) . '" class="widefat wanderlist-datepicker" />';
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
 * Add a settings page so users can manage plugin-specific settings in one place.
 */
function wanderlist_settings_menu() {
	add_submenu_page(
		'edit.php?post_type=wanderlist-location',
		esc_attr__( 'Wanderlist Settings', 'wanderlist' ),
		esc_html__( 'Settings', 'wanderlist' ),
		'manage_options',
		'wanderlist-settings-page',
		'wanderlist_settings_page'
	);
}

function wanderlist_settings_page() {
	if ( ! isset( $_REQUEST['settings-updated'] ) ) :
		$_REQUEST['settings-updated'] = false;
	endif;
	echo '<div class="wrap">';
	if ( false !== $_REQUEST['settings-updated'] ) : ?>
		<div class="updated fade"><p><strong><?php _e( 'WPORG Options saved!', 'wporg' ); ?></strong></p></div>
	<?php endif; ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	<form method="post" action="options.php">
	<?php do_settings_sections( 'wanderlist-settings-page' ); ?>
	</form>
	</div>
	<?php
}

add_action( 'admin_menu', 'wanderlist_settings_menu' );

/*
 * Set up our sections and inputs for our custom settings page.
 */

function wanderlist_settings_init() {
	// Add the section to reading settings so we can add our fields to it
	add_settings_section(
		'wanderlist_Mapbox_section',
		esc_html__( 'Mapbox Settings', 'wanderlist' ),
		'wanderlist_Mapbox_section',
		'wanderlist-settings-page'
	);

	// Add the field with the names and function to use for our new settings, put it in our new section
	add_settings_field(
		'wanderlist_MapboxAPIKey_setting',
		'API Key',
		'wanderlist_MapboxAPIKey_setting',
		'wanderlist-settings-page',
		'wanderlist_Mapbox_section'
	);

	// Register the setting!
	register_setting( 'wanderlist-settings-page', 'wanderlist_Mapbox_section' );
}
add_action( 'admin_init', 'wanderlist_settings_init' );

/*
 * Mapbox section. For now, we're just collecting the user's API key.
 */
function wanderlist_Mapbox_section() {
	echo '<p>'. esc_html__( 'Here, you can set up a few details about how you&rsquo;d like Mapbox to interact with your site.', 'wanderlist' ).'</p>';
}

function wanderlist_MapboxAPIKey_setting() {
	$setting = esc_attr( get_option( 'wporg_setting_name' ) );
	echo "<input type='text' name='wporg_setting_name' value='$setting' />";
}
