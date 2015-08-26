<?php
/**
 * Our public code, available only to the front-end of the site.
 *
 * This will register our shortcodes, collect some data, and load any
 * scripts and stylesheets we need to make things look pretty.
 *
 * @package Wanderlist
 */

/**
 * Get current location.
 * This assumes that your current location is the most
 * recently-entered location, for obvious reasons.
 */
function wanderlist_get_current_location() {
  $locations = get_posts( array(
    'posts_per_page'   => 1,
    'post_type'        => 'wanderlist_location',
  ) );
  return $locations[0]->post_title;
}

/**
 * Show a list of upcoming locations.
 */
function wanderlist_upcoming_locations() {

  $args = array(
    'post_type'      => 'wanderlist_location',
    'post_status'    => 'future',
    'posts_per_page' => 5,
    'orderby'        => 'date',
    'order'          => 'ASC',
    'tax_query'      => array(
      array(
        'taxonomy' => 'country',
        'field'    => 'slug',
        'terms'    => 'home',
        'operator' => 'NOT IN',
      ),
    ),
  );

  $location_query = new WP_Query( $args );

  while ( $location_query->have_posts() ):
    $location_query->the_post();
    $locations .= "<dt>" . esc_html( get_the_date( 'F jS' ) ) . "</dt>";
    $locations .= "<dd>" . esc_html( get_the_title() ) . "</dd>";
  wp_reset_postdata();
  endwhile;

  return $locations;
}


/**
 * Count total countries visited.
 */
function wanderlist_all_countries() {
$countries = get_terms( 'country', array(
    'hide_empty'        => false, // At least for now.
    'childless'         => true, // Only count countries that don't have sub-countries
    'exclude'           => 102, // Exclude "home"
) );
  return $countries;
}

/**
 * Register a shortcode for location tasks.
 * This just makes it stupid easy to drop into a page.
 *
 * Usage: [wanderlist-location], by default will show current location.
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
