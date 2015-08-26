<?php
/*
  Plugin Name: Wanderlist
  Plugin URI: http://triggersandsparks.com/wanderlist
  Description: A WordPress plugin for tracking the places you've visited. Display them on a map, brag about your country-count, and show fun status! Because everyone loves stats, right?
  Version: 0.1.0
  Author: sarah semark
  Author URI: http://triggersandsparks.com
  License: GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /languages
  Text Domain: wanderlist
/*

/**
 * Register countries as a taxonomy, so we can count 'em.
 */
function wanderlist_register_country_taxonomy() {

  $labels = array(
    'name'                       => _x( 'Countries', 'Taxonomy General Name', 'wanderlist' ),
    'singular_name'              => _x( 'Country', 'Taxonomy Singular Name', 'wanderlist' ),
    'menu_name'                  => __( 'Countries', 'wanderlist' ),
    'all_items'                  => __( 'All Countries', 'wanderlist' ),
    'parent_item'                => __( 'Parent Country', 'wanderlist' ),
    'parent_item_colon'          => __( 'Parent Country:', 'wanderlist' ),
    'new_item_name'              => __( 'New Country Name', 'wanderlist' ),
    'add_new_item'               => __( 'Add New Country', 'wanderlist' ),
    'edit_item'                  => __( 'Edit Country', 'wanderlist' ),
    'update_item'                => __( 'Update Country', 'wanderlist' ),
    'view_item'                  => __( 'View Country', 'wanderlist' ),
    'separate_items_with_commas' => __( 'Separate countries with commas', 'wanderlist' ),
    'add_or_remove_items'        => __( 'Add or remove countries', 'wanderlist' ),
    'choose_from_most_used'      => __( 'Choose from the most used', 'wanderlist' ),
    'popular_items'              => __( 'Popular Countries', 'wanderlist' ),
    'search_items'               => __( 'Search Countries', 'wanderlist' ),
    'not_found'                  => __( 'Not Found', 'wanderlist' ),
  );
  $args = array(
    'labels'                     => $labels,
    'hierarchical'               => true,
    'public'                     => true,
    'show_ui'                    => true,
    'show_admin_column'          => true,
    'show_in_nav_menus'          => true,
    'show_tagcloud'              => true,
  );
  register_taxonomy( 'country', array( 'post', ' place' ), $args );
}

// Hook into the 'init' action
add_action( 'init', 'wanderlist_register_country_taxonomy', 0 );

/**
 * Register the 'location' Custom Post Type.
 */
function wanderlist_register_location_cpt() {

  $labels = array(
    'name'                => _x( 'Places', 'Post Type General Name', 'wanderlist' ),
    'singular_name'       => _x( 'Place', 'Post Type Singular Name', 'wanderlist' ),
  );

  $args = array(
    'label'               => __( 'place', 'wanderlist' ),
    'description'         => __( 'Places you have been and places you are.', 'wanderlist' ),
    'menu_icon'           => 'dashicons-location',
    'labels'              => $labels,
    'supports'            => array( ),
    'taxonomies'          => array( 'post_tag', 'country' ),
    'hierarchical'        => false,
    'public'              => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'menu_position'       => 5,
    'show_in_admin_bar'   => true,
    'show_in_nav_menus'   => true,
    'can_export'          => true,
    'has_archive'         => true,
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'capability_type'     => 'post',
    'rewrite' => array('slug' => 'places'),
  );
  register_post_type( 'wanderlist_location', $args );
}
add_action( 'init', 'wanderlist_register_location_cpt', 0 );

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
 * Usage: [place], by default will show current location.
 * Pass the "show" parameter to show different information.
 * For now, [place show=current] will show current (or most recent) location,
 * [place show=countries] will show total number of countries.
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
add_shortcode( 'place', 'wanderlist_location_shortcode' );


?>
