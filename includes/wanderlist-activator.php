<?php
/**
 * All code that runs when our plugin is first activated.
 *
 * We're going to register all the custom post types and taxonomies we'll need.
 * We'll all set up permalinks (@todo) and add some default content (@todo) so we have a country list to start.
 *
 * @package Wanderlist
 */

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
