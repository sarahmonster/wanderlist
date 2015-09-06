<?php
/**
 * All code that runs when our plugin is first activated.
 *
 * We're going to register all the custom post types and taxonomies we'll need.
 * We'll all set up permalinks (@todo) and add some default content (@todo) so we have a country list to start.
 *
 * @package Wanderlist
 */

function wanderlist_setup_custom_data() {
    /**
    * First, we're going to register a 'location' Custom Post Type.
    * This forms the basis of our plugin.
    */
    $location_labels = array(
        'name'          => _x( 'Places', 'Post Type General Name', 'wanderlist' ),
        'singular_name' => _x( 'Place', 'Post Type Singular Name', 'wanderlist' ),
        'menu_name'     => __( 'Places', 'wanderlist' ),
        'all_items'     => __( 'All Places', 'wanderlist' ),
        'new_item_name' => __( 'New Place', 'wanderlist' ),
        'add_new_item'  => __( 'Add New Place', 'wanderlist' ),
        'edit_item'     => __( 'Edit Place', 'wanderlist' ),
        'update_item'   => __( 'Update Place', 'wanderlist' ),
        'view_item'     => __( 'View Place', 'wanderlist' ),
        'not_found'     => __( 'Not Found', 'wanderlist' ),
    );

    $location_args = array(
        'label'                => __( 'place', 'wanderlist' ),
        'description'          => __( 'Places you have been and places you are.', 'wanderlist' ),
        'menu_icon'            => 'dashicons-location',
        'labels'               => $location_labels,
        'supports'             => array( ),
        'taxonomies'           => array( 'post_tag', 'wanderlist-country' ),
        'hierarchical'         => false,
        'public'               => true,
        'show_ui'              => true,
        'show_in_menu'         => true,
        'menu_position'        => 5,
        'show_in_admin_bar'    => true,
        'show_in_nav_menus'    => true,
        'can_export'           => true,
        'has_archive'          => true,
        'exclude_from_search'  => false,
        'publicly_queryable'   => true,
        'capability_type'      => 'post',
        'rewrite'              => array('slug' => 'places'),
        'register_meta_box_cb' => 'wanderlist_extra_metaboxes',
    );
    register_post_type( 'wanderlist-location', $location_args );

    /**
    * Register countries as a taxonomy, so we can count 'em.
    */
    $country_labels = array(
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
    $country_args = array(
        'labels'                     => $country_labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => false,
        'show_admin_column'          => false,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
    );
    register_taxonomy( 'wanderlist-country', array( 'post', 'wanderlist-location' ), $country_args );
    register_taxonomy_for_object_type( 'wanderlist-country', 'wanderlist-location' );

    /**
    * Register a taxonomy for trips, so we can map out a particular trip.
    * This just allows us an easy way to group locations and travels into different trips.
    * At some point, we'll add functionality to show full details of a particular trip.
    */
    $trip_labels = array(
        'name'                       => _x( 'Trips', 'Taxonomy General Name', 'wanderlist' ),
        'singular_name'              => _x( 'Trip', 'Taxonomy Singular Name', 'wanderlist' ),
        'menu_name'                  => __( 'Trips', 'wanderlist' ),
        'all_items'                  => __( 'All Trips', 'wanderlist' ),
        'new_item_name'              => __( 'New Trip Name', 'wanderlist' ),
        'add_new_item'               => __( 'Add New Trip', 'wanderlist' ),
        'edit_item'                  => __( 'Edit Trip', 'wanderlist' ),
        'update_item'                => __( 'Update Trip', 'wanderlist' ),
        'view_item'                  => __( 'View Trip', 'wanderlist' ),
        'separate_items_with_commas' => __( 'Separate trips with commas', 'wanderlist' ),
        'add_or_remove_items'        => __( 'Add or remove trips', 'wanderlist' ),
        'choose_from_most_used'      => __( 'Choose from the most used trips', 'wanderlist' ),
        'popular_items'              => __( 'Popular trips', 'wanderlist' ),
        'search_items'               => __( 'Search trips', 'wanderlist' ),
        'not_found'                  => __( 'Not Found', 'wanderlist' ),
    );
    $trip_args = array(
        'labels'                     => $trip_labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
    );
    register_taxonomy( 'wanderlist-trip', array( 'post', 'wanderlist-location' ), $trip_args );
    register_taxonomy_for_object_type( 'wanderlist-trip', 'wanderlist-location' );

}
add_action( 'init', 'wanderlist_setup_custom_data' );

// Function used to automatically create Music Reviews page.
function wanderlist_create_landing_page() {
    // Post status and options
    $post = array(
      'comment_status' => 'closed',
      'ping_status'    =>  'closed' ,
      'post_date'      => date('Y-m-d H:i:s'),
      'post_name'      => 'travels',
      'post_status'    => 'publish' ,
      'post_title'     => 'Travels',
      'post_type'      => 'page',
      'post_content'   => '[wanderlist-overview]'
    );
    // Insert page and save the id
    $newvalue = wp_insert_post( $post, false );
    // Save the id in the database
    update_option( 'mrpage', $newvalue );
}

/*
 * When our plugin is activated, we're going to set everything up.
 *
 */
function wanderlist_activate() {
    // Register the location custom post type and our custom taxonomies
    wanderlist_setup_custom_data();

    // Create a new page to display our travels
    wanderlist_create_landing_page();

    // Clear the permalinks after the post type has been registered
    flush_rewrite_rules();
}
