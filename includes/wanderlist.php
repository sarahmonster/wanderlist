<?php
/**
 * Our core plugin code, available to both our public and admin views.
 *
 * This includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @package Wanderlist
 */

function wanderlist_get_country() {
  // We need to figure out our place's country. Here we go.
  $countries = wp_get_object_terms( get_the_ID(), 'wanderlist-country', array( 'fields' => 'names' ) );
  if ( $countries ) :
    foreach ( $countries as $country => $name ) :
      $the_country = ', ' . $name;
    endforeach;
  else :
    $the_country = '';
  endif;
  return $the_country;
}
