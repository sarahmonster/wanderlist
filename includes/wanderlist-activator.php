<?php
/**
 * All code that runs when our plugin is first activated.
 *
 * We're going to register all the custom post types and taxonomies we'll need.
 * We'll all set up permalinks (@todo) and add some default content (@todo) so we have a country list to start.
 *
 * @package Wanderlist
 */

function wanderlist_custom_data() {
	/**
	* First, we're going to register a 'location' Custom Post Type.
	* This forms the basis of our plugin.
	*/
	$location_labels = array(
		'name'          => _x( 'Places', 'Post Type General Name', 'wanderlist' ),
		'singular_name' => _x( 'Place', 'Post Type Singular Name', 'wanderlist' ),
		'menu_name'     => __( 'Wanderlist', 'wanderlist' ),
		'all_items'     => __( 'Places', 'wanderlist' ),
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
		'supports'             => array(),
		'taxonomies'           => array( 'post_tag', 'wanderlist-trip', 'wanderlist-country' ),
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
		'rewrite'              => array( 'slug' => 'places' ),
		'register_meta_box_cb' => 'wanderlist_extra_metaboxes',
		'supports'             => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
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
add_action( 'init', 'wanderlist_custom_data' );

/*
 * On activation, create a new page called "travels" to show an overview of user's travels.
 * @todo: Delete this page, and menu item, on uninstall, check for binned page maybe, serialise db options
 */
function wanderlist_landing_page() {

	// If a page called "travels" already exists, we don't want to make a new one
	if ( get_page_by_path( 'travels' ) ) :
		return;

	// Otherwise, let's make a new page!
	else :

		// Post status and options
		$post = array(
		  'comment_status' => 'closed',
		  'ping_status'    => 'closed',
		  'post_date'      => date( 'Y-m-d H:i:s' ),
		  'post_name'      => 'travels',
		  'post_status'    => 'publish',
		  'post_title'     => 'Travels',
		  'post_type'      => 'page',
		  'post_content'   => '[wanderlist-overview]',
		);

		// Insert page and save the id
		$new_page_id = wp_insert_post( $post, false );

		// Save the id in the database, so we can delete it on de-activation
		update_option( 'wanderlist_overview_page', $new_page_id );

		// Add to menu
		wanderlist_add_menu_item( $new_page_id );

	endif;
}

/*
 * Adds specified page to the primary menu.
 * This tries to be smart about finding the "primary menu", but isn't anywhere
 * close to foolproof, and requires that a menu location be set already.
 * At some point, this may warrant further investigation, but it's pretty low priority.
 */
function wanderlist_add_menu_item( $post_id ) {
  // First, let's get a menu. We'll make a new one if we don't already have any,
  // but otherwise we'll just use the most logical-seeming menu.
  $locations = get_nav_menu_locations();
  if ( ! $locations ) :
	  $menu_id = wp_create_nav_menu ( 'Primary Menu' );
  elseif ( $locations['primary'] ) :
	  $menu_id = $locations['primary'];
  else :
	  $menu_id = $locations[0];
  endif;

  // Add page to our selected menu
  $new_menu_item_id = wp_update_nav_menu_item ( $menu_id, 0, array(
	  'menu-item-title'     => esc_html__( 'Travels', 'wanderlist' ),
	  'menu-item-object'    => 'page',
	  'menu-item-type'      => 'post_type',
	  'menu-item-object-id' => $post_id,
	  'menu-item-status'    => 'publish',
	  )
  );

  // Save the id in the database, so we can delete it on de-activation
  update_option( 'wanderlist_menu_item', $new_menu_item_id );
}

/*
 * When our plugin is activated, we're going to set everything up.
 */
function wanderlist_activate() {
	// Register the location custom post type and our custom taxonomies
	wanderlist_custom_data();

	// Create a new page to display our travels
	wanderlist_landing_page();

	// Clear the permalinks after the post type has been registered
	flush_rewrite_rules();
}
