<?php
/**
 * Filters that act on our post loops.
 *
 * This is primarily for trip and country overview page right now, where we want
 * to show all posts and sort them in the order visited, rather than the order posted.
 *
 * @package Wanderlist
 */

/**
 * Since our arrival date is a custom meta key, we need to do some database-fu
 * in order to sort by its value. Here, we're performing a database join so we
 * can access the arrival date.
 */
function wanderlist_date_join( $wp_join ) {
	if ( is_tax( 'wanderlist-trip' ) || is_tax( 'wanderlist-country' ) ) :
		global $wpdb;
		$wp_join .= " LEFT JOIN (SELECT post_id, meta_value as arrival_date FROM $wpdb->postmeta WHERE meta_key =  'wanderlist-arrival-date' ) AS PLACE ON $wpdb->posts.ID = PLACE.post_id ";
	endif;
	return $wp_join;
}
add_filter( 'posts_join', 'wanderlist_date_join' );

/**
 * Change the post order for certain taxonomies.
 * Order first by our arrival date, and then by our post date.
 * Ascending order means the place we visited first shows up first.
 */
function wanderlist_order_by_arrival_date( $orderby ) {
	if ( is_tax( 'wanderlist-trip' ) || is_tax( 'wanderlist-country' ) ) :
		return 'PLACE.arrival_date ASC, post_date ASC';
	else :
		return $orderby;
	endif;
}
add_filter( 'posts_orderby', 'wanderlist_order_by_arrival_date' );

/**
 * Adjust the post limit so we can display lots of posts on these pages.
 * We're also removing Infinite Scroll support for these specific taxonomies,
 * because IS doesn't handle the adjusted posts_per_page value very well.
 */
function wanderlist_adjust_postlimit( $query ) {
	if ( is_admin() ) :
		return;
	endif;

	if ( is_tax( 'wanderlist-trip' ) || is_tax( 'wanderlist-country' ) ) :
		$query->set( 'posts_per_page', 100 );
		remove_theme_support( 'infinite-scroll' ); // Because otherwise IS just does what it wants.
		return;
	endif;
}
add_action( 'pre_get_posts', 'wanderlist_adjust_postlimit', 2 );
