<?php
/**
 * The code that controls our custom settings page for the plugin.
 *
 * This will collect our user's Mapbox API key, and preferred map style.
 * It also allows them to set a preferred date format, select their "loved" tag,
 * set a home location(s), and choose whether they want links to location posts.
 *
 * @todo: Setting for home location, input validation & helpful errors.
 *
 * @package Wanderlist
 */

/*
 * Add our settings page as a submenu of our existing "Wanderlist" menu, created
 * by the "location" CPT.
 * This means all behaviours for the plugin will be clustered under a single,
 * logical menu.
 */
function wanderlist_add_admin_menu() {
	add_submenu_page(
		'edit.php?post_type=wanderlist-location',
		esc_attr__( 'Wanderlist Settings', 'wanderlist' ),
		esc_html__( 'Settings', 'wanderlist' ),
		'manage_options',
		'wanderlist',
		'wanderlist_options_page'
	);
}
add_action( 'admin_menu', 'wanderlist_add_admin_menu' );

/*
 * Register our settings sections and fields.
 */
function wanderlist_settings_init() {

	register_setting( 'wanderlist_settings', 'wanderlist_settings' );

	// Mapbox settings
	add_settings_section(
		'wanderlist_mapbox_settings_section',
		esc_html__( 'Mapbox Settings', 'wanderlist' ),
		'wanderlist_mapbox_section_description',
		'wanderlist_settings'
	);

	add_settings_field(
		'wanderlist_mapbox_key',
		esc_html__( 'Mapbox API Key', 'wanderlist' ),
		'wanderlist_mapbox_key_render',
		'wanderlist_settings',
		'wanderlist_mapbox_settings_section'
	);

	add_settings_field(
		'wanderlist_map_ID',
		esc_html__( 'Map ID', 'wanderlist' ),
		'wanderlist_map_id_render',
		'wanderlist_settings',
		'wanderlist_mapbox_settings_section'
	);

	add_settings_field(
		'wanderlist_marker_colour',
		esc_html__( 'Marker Colour', 'wanderlist' ),
		'wanderlist_marker_colour_render',
		'wanderlist_settings',
		'wanderlist_mapbox_settings_section'
	);

	add_settings_field(
		'wanderlist_line_colour',
		esc_html__( 'Line Colour', 'wanderlist' ),
		'wanderlist_line_colour_render',
		'wanderlist_settings',
		'wanderlist_mapbox_settings_section'
	);

	// General settings
	add_settings_section(
		'wanderlist_general_settings_section',
		esc_html__( 'General Settings', 'wanderlist' ),
		'wanderlist_general_section_description',
		'wanderlist_settings'
	);

	add_settings_field(
		'wanderlist_dateformat',
		esc_html__( 'Date Format', 'wanderlist' ),
		'wanderlist_dateformat_render',
		'wanderlist_settings',
		'wanderlist_general_settings_section'
	);

	add_settings_field(
		'wanderlist_hide_link_to_location',
		esc_html__( 'Hide links to place posts', 'wanderlist' ),
		'wanderlist_hide_link_to_location_render',
		'wanderlist_settings',
		'wanderlist_general_settings_section'
	);

	// Places settings
	add_settings_section(
		'wanderlist_places_settings_section',
		esc_html__( 'Places', 'wanderlist' ),
		'wanderlist_places_section_description',
		'wanderlist_settings'
	);

	add_settings_field(
		'wanderlist_home',
		esc_html__( 'Your home', 'wanderlist' ),
		'wanderlist_home_render',
		'wanderlist_settings',
		'wanderlist_places_settings_section'
	);

	add_settings_field(
		'wanderlist_home_tag',
		esc_html__( 'Tag for additional "home" places', 'wanderlist' ),
		'wanderlist_home_tag_render',
		'wanderlist_settings',
		'wanderlist_places_settings_section'
	);

	add_settings_field(
		'wanderlist_loved_tag',
		esc_html__( 'Tag for "loved" places', 'wanderlist' ),
		'wanderlist_loved_tag_render',
		'wanderlist_settings',
		'wanderlist_places_settings_section'
	);

}
add_action( 'admin_init', 'wanderlist_settings_init' );

/*
 * These functions add descriptions for each of our settings sections.
 */
function wanderlist_mapbox_section_description() {
	printf(
		wp_kses( __( '<a href="%1$s">Sign up for a free Mapbox account here</a>. Generate a new <strong>public</strong> API key from <a href="%2$s">Account → Apps</a> and enter it below.', 'wanderlist' ),
			array( 'a' => array( 'href' => array() ) )
		),
		esc_url( 'https://www.mapbox.com/signup/' ),
		esc_url( 'https://www.mapbox.com/account/apps/' )
	);
}

function wanderlist_general_section_description() {
	esc_html_e( 'For more information on setting these up, check the readme.', 'wanderlist' );
}

function wanderlist_places_section_description() {
	esc_html_e( 'You can tag certain places with "loved" or "home" tags in order to indicate that you loved that place, or that it was your home at the time.', 'wanderlist' );
}

/*
 * These functions are used to render each particular option's input field.
 * @todo: Add data validation to ensure correct user input.
 */
function wanderlist_mapbox_key_render() {
	$options = get_option( 'wanderlist_settings' );
	?>
	<input type='text' name='wanderlist_settings[wanderlist_mapbox_key]' value='<?php echo esc_attr( $options['wanderlist_mapbox_key'] ); ?>'>
	<?php
}

// @todo: Show preview & auto-selection of default Mapbox styles
function wanderlist_map_id_render() {
	$options = get_option( 'wanderlist_settings' );
	?>
	<input type='text' name='wanderlist_settings[wanderlist_map_id]' value='<?php echo esc_attr( $options['wanderlist_map_id'] ); ?>'>
	<?php
}

function wanderlist_marker_colour_render() {
	$options = get_option( 'wanderlist_settings' );
	?>
	<input type='text' name='wanderlist_settings[wanderlist_marker_colour]' value='<?php echo esc_attr( $options['wanderlist_marker_colour'] ); ?>'>
	<?php
}

function wanderlist_line_colour_render() {
	$options = get_option( 'wanderlist_settings' );
	?>
	<input type='text' name='wanderlist_settings[wanderlist_line_colour]' value='<?php echo esc_attr( $options['wanderlist_line_colour'] ); ?>'>
	<?php
}

// @todo: Show a full array of options, to mimic general settings.
function wanderlist_dateformat_render() {
	$options = get_option( 'wanderlist_settings' );
	?>
	<input type='text' name='wanderlist_settings[wanderlist_dateformat]' value='<?php echo esc_attr( $options['wanderlist_dateformat'] ); ?>'>
	<?php
	printf(
		wp_kses( __( '<br /><a href="%s">Documentation on date and time formatting.</a>', 'wanderlist' ),
			array(
				'a' => array( 'href' => array() ),
				'br' => array(),
			)
		),
		esc_url( 'https://codex.wordpress.org/Formatting_Date_and_Time' )
	);
}

function wanderlist_hide_link_to_location_render(  ) {
	$options = get_option( 'wanderlist_settings' );
	$checked = ''; // Our default is unchecked.

	// Override the user has opted-out of links.
	if ( $options && array_key_exists( 'wanderlist_hide_link_to_location', $options ) ) :
		if ( '1' === $options['wanderlist_hide_link_to_location'] ) :
			$checked = 'checked';
		endif;
	endif;
	?>
	<input type='checkbox' name='wanderlist_settings[wanderlist_hide_link_to_location]' <?php echo $checked; ?> value='1'>
	<?php
}

function wanderlist_home_render() {
	$options = get_option( 'wanderlist_settings' );
	?>
	<input id='wanderlist-geolocation-input' type='text' name='wanderlist_settings[wanderlist_home]' value='<?php echo esc_attr( $options['wanderlist_home'] ); ?>'>
	<p class="description">Where you live right now.</p>
	<?php
}

function wanderlist_home_tag_render(  ) {
	$options = get_option( 'wanderlist_settings' );
	$tags = get_tags();
	?>
	<select name='wanderlist_settings[wanderlist_home_tag]'>
		<option value='0' <?php selected( $options['wanderlist_home_tag'], 0 ); ?>>None selected</option>
		<?php foreach ( $tags as $tag ) : ?>
			<option value="<?php echo esc_attr( $tag->term_id ); ?>" <?php selected( $options['wanderlist_home_tag'], $tag->term_id ); ?>><?php echo esc_html( $tag->name ); ?></option>
		<?php endforeach; ?>
	</select>
<?php
}

function wanderlist_loved_tag_render(  ) {
	$options = get_option( 'wanderlist_settings' );
	$tags = get_tags();
	?>
	<select name='wanderlist_settings[wanderlist_loved_tag]'>
		<option value='0' <?php selected( $options['wanderlist_loved_tag'], 0 ); ?>>None selected</option>
		<?php foreach ( $tags as $tag ) : ?>
			<option value="<?php echo esc_attr( $tag->term_id ); ?>" <?php selected( $options['wanderlist_loved_tag'], $tag->term_id ); ?>><?php echo esc_html( $tag->name ); ?></option>
		<?php endforeach; ?>
	</select>
<?php
}

/*
 * Set up the actual settings page itself.
 */
function wanderlist_options_page(  ) {
	?>
	<div class="wrap">
		<?php if ( isset( $_REQUEST['settings-updated'] ) ) : ?>
			<div class="updated fade"><p><strong><?php esc_html_e( 'Your settings have been saved!', 'wanderlist' ); ?></strong></p></div>
		<?php endif; ?>

		<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

		<form action='options.php' method='post'>
			<?php
			settings_fields( 'wanderlist_settings' );
			do_settings_sections( 'wanderlist_settings' );
			submit_button();
			?>
	</div>
	<?php
}
