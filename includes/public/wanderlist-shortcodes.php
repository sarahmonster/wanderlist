<?php
/**
 * This plugin makes pretty enthusiastic use of shortcodes,
 * so we're collecting them all here in one place.
 *
 * These shortcodes rely on functions defined in wanderlist-public.php.
 *
 * @package Wanderlist
 */

/**
 * Register a shortcode for location tasks.
 * This just makes it stupid easy to drop into a page.
 *
 * Usage: [wanderlist-location], by default will show current location.
 *
 * Pass the "show" parameter to show different information.
 * For now, [wanderlist-location show=current] will show current (or most recent) location,
 * [wanderlist-location show=countries] will show total number of countries.
 */
function wanderlist_location_shortcode( $atts, $content = null  ){
  $a = shortcode_atts( array(
      'show' => 'current',
  ), $atts );

  if ( 'current' === $a['show'] ):
    return '<span class="wanderlist-current-location">' . wanderlist_get_current_location() . '</span>';
  endif;

  if ( 'countries' === $a['show'] ):
    return '<span class="wanderlist-country-count">' . count( wanderlist_all_countries() ) . '</span>';
  endif;
}

function wanderlist_register_location_shortcode() {
  add_shortcode( 'wanderlist-location', 'wanderlist_location_shortcode' );
}

add_action( 'init', 'wanderlist_register_location_shortcode' );

/**
 * Register a shortcode to display maps.
 *
 * Usage: [wanderlist-map], by default will show a map of upcoming locations.
 * If there are no upcoming locations, it will default to most-recently-visited locations.
 *
 * Pass the "show" parameter to show different information.
 * For now, [wanderlist-map show=current] will show upcoming (or most recent) locations,
 * [wanderlist-map show=countries] will show all countries visited.
 */
function wanderlist_map_shortcode( $atts, $content = null  ){
  $a = shortcode_atts( array(
      'show' => 'current',
  ), $atts );

  if ( 'current' === $a['show'] ):
    return wanderlist_show_map( 'upcoming' );
  endif;

  if ( 'countries' === $a['show'] ):
    return '<span class="wanderlist-country-count">' . count( wanderlist_all_countries() ) . '</span>';
  endif;
}

function wanderlist_register_map_shortcode() {
  add_shortcode( 'wanderlist-map', 'wanderlist_map_shortcode' );
}

add_action( 'init', 'wanderlist_register_map_shortcode' );


/**
 * Register a shortcode to display an overview of our travels.
 * This shortcode will be used on the primary landing page for our plugin.
 *
 * Usage: [wanderlist-overview], by default will show a dashboard of stats and widgets.
 * If there are no upcoming locations, it will default to most-recently-visited locations.
 *
 * We'll set up a settings page later to fine-tune its output, or
 * potentially allow users to pass parameters. For now, no options.
 */
function wanderlist_overview_shortcode( $atts, $content = null  ){
  $a = shortcode_atts( array(
      'show' => 'current',
  ), $atts );

  $widgets[] = array(
                'class'   => 'wanderlist-location-widget',
                'title'   => esc_html__( 'Upcoming trips', 'wanderlist' ),
                'content' => wanderlist_list_locations(),
              );

  $widgets[] = array(
              'class'   => 'wanderlist-location-widget',
              'title'   => esc_html__( 'Most recent trips', 'wanderlist' ),
              'content' => wanderlist_list_locations( 20, 'past' ),
            );

  $widgets[] = array(
                'class'   => 'wanderlist-number-widget',
                'title'   => esc_html__( 'Total countries visited', 'wanderlist' ),
                'content' => '<span class="wanderlist-country-count">' . count( wanderlist_all_countries() ) . '</span>',
              );

  foreach ( $widgets as $widget ) :
    $return .= '<div class="wanderlist-widget ' . $widget['class'] . '">';
    $return .= '<h3 class="widget-title">' . $widget['title'] . '</h3>';
    $return .= $widget['content'];
    $return .= "</div>";
  endforeach;

  return $return;
}

function wanderlist_register_overview_shortcode() {
  add_shortcode( 'wanderlist-overview', 'wanderlist_overview_shortcode' );
}

add_action( 'init', 'wanderlist_register_overview_shortcode' );
