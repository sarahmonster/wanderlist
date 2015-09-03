<?php
/**
 * Our private code, available only to the admin side of the site.
 *
 * This will register some custom metaboxes, and set up our geo-locator.
 *
 * @package Wanderlist
 */


function wanderlist_extra_metaboxes() {
  add_meta_box( 'wanderlist-geolocation', __( 'Geolocation', 'wanderlist'), 'wanderlist_geolocation_box', 'wanderlist-location', 'side', 'high' );
  //add_meta_box( 'wanderlist-dates', __( 'Dates', 'wanderlist'), 'wanderlist_date_box', 'wanderlist-location', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'wanderlist_extra_metaboxes' );

/*
 * Set up a box to collect our location.
 */
function wanderlist_geolocation_box() {
  global $post;

  // Noncename needed to verify where the data originated
  wp_nonce_field( basename( __FILE__ ), 'meta-box-nonce' );

  // Get the location data if it's already been entered
  $location = get_post_meta( $post->ID, 'wanderlist-geolocation', true );

  // Echo out the field
  echo '<input type="text" name="wanderlist-geolocation" value="' . $location  . '" class="widefat" />';

}

/*
 * Save our data once the user publishes or saves their post.
 *
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

  update_post_meta( $post_id, 'wanderlist-geolocation', $geolocation_value );
}

add_action( 'save_post', 'wanderlist_save_metabox_data', 10, 3 );
