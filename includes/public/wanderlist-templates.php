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
 * Somehow this isn't a built-in WordPress function.
 * I assume because someone wants me to be sad.
 */
function wanderlist_plugin_dir() {
  $dir = ABSPATH . 'wp-content/plugins/wanderlist';
  return $dir;
}

/**
 * Set up a template for our trip taxonomy.
 * If the user has a template file in their theme, use that one.
 * If not, use our template file instead.
 */
function wanderlist_taxonomy_template( $template ) {
  // Check to see we're on the trip taxonomy page and that the theme hasn't specified its own template
  if ( is_tax( 'wanderlist_trip' ) && !wanderlist_is_template( $template ) ) {
    $template = wanderlist_plugin_dir() . '/templates/taxonomy-wanderlist_trip.php';
  }
  return $template;
}
add_filter( 'taxonomy_template', 'wanderlist_taxonomy_template' );

/**
 * Check to see if our current theme uses a custom taxonomy template.
 */
function wanderlist_is_template( $template_path ){
    // Check for template taxonomy-wanderlist-trip.php of taxonomy-wanderlist-trip-{term-slug}.php
    $template = basename( $template_path );
    if ( 1 == preg_match( '/^taxonomy-wanderlist_trip((-(\S*))?).php/', $template ) ) :
      return true;
    else:
      return false;
    endif;
}
