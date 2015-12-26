<?php
/**
 * Our core plugin code, available to both our public and admin views.
 *
 * This includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @package Wanderlist
 */

/*
 * This is a reusable function that allows us to grab place-specific data and return
 * it in a consistent way across our plugin scripts, and in theme template files as well.
 */
function wanderlist_place_data( $data, $post_ID = null ) {
	switch( $data ) {
		case 'country' :
		 	// We need to figure out our place's country. Here we go.
		 	$countries = wp_get_object_terms( get_the_ID(), 'wanderlist-country', array( 'fields' => 'names' ) );
		 	if ( $countries ) :
				$count = 0;
		 		foreach ( $countries as $country => $name ) :
					if ( $count === 0 ) :
						$output .= $name;
					else :
		 				$output .= ', ' . $name;
					endif;
					$count++;
		 		endforeach;
		 	else :
		 		$output = '';
		 	endif;
			break;
		case 'city' :
			$output = get_post_meta( $post_ID, 'wanderlist-city', true );
			break;
		case 'lat' :
			$output = get_post_meta( $post_ID, 'wanderlist-lat', true );
			break;
		case 'lng' :
			$output .= get_post_meta( $post_ID, 'wanderlist-lng', true );
			break;
	}
	return $output;
}
