<?php
/**
 * Template-related code.
 *
 * These functions allow us to override the default WordPress templates for
 * custom post types and taxonomies. For increased customisability, if the user
 * has template files of the same name in their theme, the plugin will default
 * to using the theme templates, instead.
 *
 * @package Wanderlist
 */

/**
 * Determine the absolute path for our plugin.
 * Somehow this isn't a built-in WordPress function,
 * or at least doesn't work as expected if it's
 * called from within a sub-directory.
 * I assume because someone wants me to be sad.
 */
function wanderlist_plugin_dir() {
	$dir = plugin_dir_path( dirname( __FILE__ ) );
	$dir = rtrim( $dir, '/includes' );
	return $dir;
}

/**
 * Set up a template for our taxonomies.
 * We have custom templates for countries and for trips.
 * If the user has a template file in their theme, use that one.
 * If not, use our template file instead.
 */
function wanderlist_taxonomy_template( $template ) {
	// Check to see we're on the trip taxonomy page and that the theme hasn't specified its own template
	if ( is_tax( 'wanderlist-trip' ) && ! wanderlist_is_template( $template, '/^taxonomy-wanderlist-trip((-(\S*))?).php/' ) ) {
		$template = wanderlist_plugin_dir() . '/templates/taxonomy-wanderlist-trip.php';
	}
	if ( is_tax( 'wanderlist-country' ) && ! wanderlist_is_template( $template, '/^taxonomy-wanderlist-country((-(\S*))?).php/' ) ) {
		$template = wanderlist_plugin_dir() . '/templates/taxonomy-wanderlist-country.php';
	}
	return $template;
}
add_filter( 'taxonomy_template', 'wanderlist_taxonomy_template' );

/**
 * Set up a template for our 'location' custom post type.
 * If the user has a template file in their theme, use that one.
 * If not, use our template file instead.
 */
function wanderlist_CPT_template( $template ) {
	// Check to see we're on the trip taxonomy page and that the theme hasn't specified its own template
	if ( 'wanderlist-location' == get_post_type(get_queried_object_id())
		&& ! wanderlist_is_template( $template, '/single-wanderlist-location.php/' ) ) {
		$template = wanderlist_plugin_dir() . '/templates/single-wanderlist-location.php';
	}
	return $template;
}
add_filter( 'single_template', 'wanderlist_CPT_template' );

/**
 * Check to see if our theme has custom templates available.
 * This check is used by the above functions to see if we should use the fallback functions
 */
function wanderlist_is_template( $template_path, $match ){
	// Check for template taxonomy-wanderlist-trip.php or taxonomy-wanderlist-trip-{term-slug}.php
	$template = basename( $template_path );
	if ( 1 === preg_match( $match, $template ) ) :
		return true;
	else :
		return false;
	endif;
}
