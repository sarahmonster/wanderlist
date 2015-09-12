<?php
/**
 * The code that controls our custom settings page for the plugin.
 *
 * This will collect our user's Mapbox API key, allow them to set a preferred date format,
 * select their "loved" tag, set a home location(s), and choose whether they want links to location posts.
 *
 * @package Wanderlist
 */

/*
 * Add our settings page as a submenu of our existing "Wanderlist" menu, created by the "location" CPT.
 * This means all behaviours for the plugin will be clustered under a single, logical menu.
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

	add_settings_section(
		'wanderlist_mapbox_settings_section',
		esc_html__( 'Mapbox settings', 'wanderlist' ),
		'wanderlist_mapbox_section_description',
		'wanderlist_settings'
	);

	add_settings_section(
		'wanderlist_general_settings_section',
		esc_html__( 'General settings', 'wanderlist' ),
		'wanderlist_general_section_description',
		'wanderlist_settings'
	);

	add_settings_field(
		'wanderlist_mapbox_key',
		esc_html__( 'API Key', 'wanderlist' ),
		'wanderlist_mapbox_key_render',
		'wanderlist_settings',
		'wanderlist_mapbox_settings_section'
	);

	add_settings_field(
		'wanderlist_dateformat',
		esc_html__( 'Date format', 'wanderlist' ),
		'wanderlist_dateformat_render',
		'wanderlist_settings',
		'wanderlist_general_settings_section'
	);

	add_settings_field(
		'wanderlist_link_to_location',
		esc_html__( 'Show links to location posts', 'wanderlist' ),
		'wanderlist_link_to_location_render',
		'wanderlist_settings',
		'wanderlist_general_settings_section'
	);

	add_settings_field(
		'wanderlist_loved_tag',
		esc_html__( 'Select a tag to indicate that a location is "loved"', 'wanderlist' ),
		'wanderlist_loved_tag_render',
		'wanderlist_settings',
		'wanderlist_general_settings_section'
	);
}
add_action( 'admin_init', 'wanderlist_settings_init' );

/*
 * These functions add descriptions for each of our settings sections.
 */
function wanderlist_mapbox_section_description() {
	esc_html_e( 'To use this plugin, you&rsquo;ll need an API key from Mapbox. Don&rsquo;t worry; it&rsquo;s free and super-simple to get!', 'wanderlist' );
}

function wanderlist_general_section_description() {
	esc_html_e( 'For more information on setting these up, check the readme.', 'wanderlist' );
}

/*
 * These functions are used to render each particular option's input field.
 */
function wanderlist_mapbox_key_render() {
	$options = get_option( 'wanderlist_settings' );
	?>
	<input type='text' name='wanderlist_settings[wanderlist_mapbox_key]' value='<?php echo esc_attr( $options['wanderlist_mapbox_key'] ); ?>'>
	<?php
}

function wanderlist_dateformat_render() {
	$options = get_option( 'wanderlist_settings' );
	?>
	<input type='text' name='wanderlist_settings[wanderlist_dateformat]' value='<?php echo esc_attr( $options['wanderlist_dateformat'] ); ?>'>
	<?php
}


function wanderlist_link_to_location_render(  ) {
	$options = get_option( 'wanderlist_settings' );
	?>
	<input type='checkbox' name='wanderlist_settings[wanderlist_link_to_location]' <?php checked( $options['wanderlist_link_to_location'], 1 ); ?> value='1'>
	<?php
}


function wanderlist_loved_tag_render(  ) {
	$options = get_option( 'wanderlist_settings' );
	?>
	<select name='wanderlist_settings[wanderlist_loved_tag]'>
		<option value='1' <?php selected( $options['wanderlist_loved_tag'], 1 ); ?>>Option 1</option>
		<option value='2' <?php selected( $options['wanderlist_loved_tag'], 2 ); ?>>Option 2</option>
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
