<?php
/**
 * Our private code, available only to the admin side of the site.
 *
 * This will register some custom metaboxes, and set up our geo-locator.
 *
 * @package Wanderlist
 */

/*
 * Enqueue the jQuery UI datepicker for admin usage.
 * This isn't the prettiest thing ever, but it's already available
 * in core so we'll use it at least until I get sick of it.
 */
function wanderlist_admin_scripts() {
  wp_enqueue_script( 'wanderlist-datepicker-js', plugin_dir_url( __FILE__ ) . 'js/datepicker.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), time(), true );
  wp_enqueue_style( 'jquery-ui' );
  wp_enqueue_style( 'jquery-ui-datepicker', plugin_dir_url( __FILE__ ) . 'css/datepicker.css' );
}
add_action( 'admin_enqueue_scripts', 'wanderlist_admin_scripts' );

/*
 * Register extra metaboxes on our "place" custom post type screens.
 */
function wanderlist_extra_metaboxes() {
  add_meta_box( 'wanderlist-geolocation', __( 'Geolocation', 'wanderlist'), 'wanderlist_geolocation_box', 'wanderlist-location', 'side', 'high' );
  add_meta_box( 'wanderlist-dates', __( 'Dates', 'wanderlist'), 'wanderlist_date_box', 'wanderlist-location', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'wanderlist_extra_metaboxes' );

/*
 * Create a custom metabox to input the location for this post.
 */
function wanderlist_geolocation_box() {
  global $post;

  // Nonce
  wp_nonce_field( basename( __FILE__ ), 'meta-box-nonce' );

  // Input field
  echo '<input type="text" name="wanderlist-geolocation" value="' . get_post_meta( $post->ID, 'wanderlist-geolocation', true ) . '" class="widefat" />';
}

/*
 * Create a custom metabox to input the date range.
 */
function wanderlist_date_box() {
  global $post;

  // Nonce
  wp_nonce_field( basename( __FILE__ ), 'meta-box-nonce' );

  // Arrival
  echo '<label>' . esc_html__( 'Arrival date', 'wanderlist' ) . '</label>';
  echo '<input type="text" name="wanderlist-arrival-date" value="' . get_post_meta( $post->ID, 'wanderlist-arrival-date', true ) . '" class="widefat wanderlist-datepicker" />';

  // Departure
  echo '<label>' . esc_html__( 'Departure date (optional)', 'wanderlist' ) . '</label>';
  echo '<input type="text" name="wanderlist-departure-date" value="' . get_post_meta( $post->ID, 'wanderlist-departure-date', true ) . '" class="widefat wanderlist-datepicker" />';
}

/*
 * Save our data once the user publishes or saves their post.
 */
function wanderlist_save_metabox_data( $post_id, $post, $update ) {
  // Make sure our nonce has been correctly set
  if ( !isset( $_POST['meta-box-nonce'] ) || !wp_verify_nonce( $_POST['meta-box-nonce'], basename( __FILE__ ) ) ) :
    return $post_id;
  endif;

  // Make sure the user has permissions to edit this post
  if( !current_user_can( 'edit_post', $post_id ) ) :
    return $post_id;
  endif;

  // If we're autosaving, don't worry about it right now
  if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) :
    return $post_id;
  endif;

  // Check to ensure that our post type is the same as our meta box post type
  if('wanderlist-location' != $post->post_type) :
    return $post_id;
  endif;

  // If we've passed all those checks, we should be good to save! Let's go.
  if( isset( $_POST['wanderlist-geolocation'] ) ) :
    $geolocation_value = $_POST['wanderlist-geolocation'];
  else :
    $geolocation_value = "";
  endif;

  if( isset( $_POST['wanderlist-arrival-date'] ) ) :
    $arrival_date_value = $_POST['wanderlist-arrival-date'];
  else :
    $arrival_date_value = "";
  endif;

  if( isset( $_POST['wanderlist-departure-date'] ) ) :
    $departure_date_value = $_POST['wanderlist-departure-date'];
  else :
    $depature_date_value = "";
  endif;

  update_post_meta( $post_id, 'wanderlist-geolocation', $geolocation_value );
  update_post_meta( $post_id, 'wanderlist-arrival-date', $arrival_date_value );
  update_post_meta( $post_id, 'wanderlist-departure-date', $departure_date_value  );
}

add_action( 'save_post', 'wanderlist_save_metabox_data', 10, 3 );
