<?php
/**
 * Our public code, available only to the front-end of the site.
 *
 * This will collect some data for use in shortcodes, and load any
 * scripts and stylesheets we need to make things look pretty.
 *
 * @package Wanderlist
 */

/**
 * Enqueuing the scripts and styles we need to implement Mapbox.
 */
function wanderlist_scripts() {
  wp_enqueue_script( 'wanderlist-mapbox', 'https://api.tiles.mapbox.com/mapbox.js/v2.2.1/mapbox.js', array(), '20150719', true );
  wp_enqueue_style( 'wanderlist-mapbox-css', 'https://api.tiles.mapbox.com/mapbox.js/v2.2.1/mapbox.css', array(), '20150719', all );
  wp_enqueue_script( 'wanderlist-map', plugin_dir_url( __FILE__ ) . 'js/map.js', array( 'jquery', 'wanderlist-mapbox' ), '20150719', true );
}
add_action( 'wp_enqueue_scripts', 'wanderlist_scripts' );

/**
 * Get current location.
 * This looks for a location with an arrival date
 * earlier than today, and a departure date
 * later than today.
 * @todo: Default to a "home" location if none are found.
 * @todo: Determine intelligent handling if no departure date exists.
 */
function wanderlist_get_current_location() {
  $locations = get_posts( array(
    'posts_per_page' => 1,
    'post_type'      => 'wanderlist-location',
    'meta_query'     => array(
      array(
        'key'     => 'wanderlist-arrival-date',
        'value'   => wanderlist_today(),
        'compare' => '<=',
      ),
      array(
        'key'     => 'wanderlist-departure-date',
        'value'   => wanderlist_today(),
        'compare' => '>=',
      ),
    ),
  ) );
  return $locations[0]->post_title;
}

/*
 * Get today's date.
 * This also sets our default timezone to whatever is set in our WordPress settings.
 */
function wanderlist_today() {
  date_default_timezone_set( get_option( 'timezone_string' ) );
  $today = date( 'Y-m-d' );
  return $today;
}

/**
 * Show a list of locations.
 *
 * This will default to showing all upcoming locations.
 * Passing the limit and show params will change its output.
 */
function wanderlist_list_locations( $limit = null, $show = 'default' ) {

  if ( 'all' === $show ) :
    $order = DESC;
  elseif ( 'past' === $show ) :
    $order = DESC;
    $compare = '<';
  elseif ( 'upcoming' === $show ):
    $order = ASC;
    $compare = '>';
  elseif ( 'default' === $show ) :
    $order = ASC;
    $compare = '>';
    $limit = $limit - 1;
  endif;

  $args = array(
    'post_type'      => 'wanderlist-location',
    'post_status'    => array( 'future', 'publish' ),
    'posts_per_page' => $limit,
    'orderby'        => 'meta_value post_date',
    'meta_key'       => 'wanderlist-arrival-date',
    'meta_value'     => wanderlist_today(),
    'meta_compare'   => $compare,
    'order'          => $order,
    'tax_query'      => array(
      array(
        'taxonomy' => 'wanderlist-country',
        'field'    => 'slug',
        'terms'    => 'home',
        'operator' => 'NOT IN',
      ),
    ),
  );

  $location_query = new WP_Query( $args );

  $locations = "<dl>";

  // If we're not passing an upcoming/past parameter, show today's location
  if ( 'default' === $show ) :
    $locations .= '<dt>' . esc_html__( 'Today', 'wanderlist' ) . '</dt>';
    $locations .= '<dd>' . wanderlist_get_current_location() . '</dd>';
  endif;

  while ( $location_query->have_posts() ) :
    $location_query->the_post();

    // If we're still visiting somewhere, show that location with "today" as the date in the "recent trips" list
    if ( 'past' === $show && wanderlist_today() <= get_post_meta( get_the_ID(), 'wanderlist-departure-date', true )) :
      $locations .= '<dt>' . esc_html__( 'Today', 'wanderlist' ) . '</dt>';

    // Otherwise, display the date of arrival
    else :
      $locations .= '<dt>' . wanderlist_arrival_date( get_the_ID() ) . '</dt>' ;
    endif;

    $locations .= '<dd>' . esc_html( get_the_title() ) .'<span class="wanderlist-country">' . wanderlist_get_country() . '</span>';

    // Display some icons if the location is home or loved
    if ( wanderlist_is_loved( ) ) :
      $locations .= '<span class="wanderlist-loved">&hearts;</span>';
    endif;
    if ( wanderlist_is_home( ) ) :
      $locations .= '<span class="wanderlist-home">&star;</span>';
    endif;

    $locations .= '</dd>';

  wp_reset_postdata();
  endwhile;

  $locations .= "</dl>";

  return $locations;
}

/**
 * Determine if a location is loved or not.
 *
 * This uses a special tag that the user sets via a settings panel,
 * then assigns to location as desired. It's basically just a way
 * of indicating a place that's close to your heart.
 *
 * @todo Set tag to use via settings panel (like Featured Content).
 */
function wanderlist_is_loved( $location = null ) {

  // First, grab our "loved" tag
  $loved_tag = 93;

  if ( has_term( $loved_tag, 'post_tag', $location ) ) :
    return true;
  else :
    return false;
  endif;
}

/**
 * Determine if a location is home or not.
 *
 * This uses a special tag that the user sets via a settings panel,
 * then assigns to location as required. This allows us to remove
 * the user's home from various calculations, like cities visited.
 *
 * @todo Set tag to use via settings panel (like Featured Content).
 * @todo Add functionality to also set a particular geographic location as home.
 */
function wanderlist_is_home( $location = null ) {

  // First, grab our "home" tag
  $home_tag = 122;

  if ( has_term( $home_tag, 'post_tag', $location ) ) :
    return true;
  else :
    return false;
  endif;
}

/*
 * Determine the arrival date for a particular location.
 *
 * Dates are stored as YYYY-MM-DD for easy ordering, but
 * we'd prefer to display this in a different format.
 * @todo: Allow for a user-configured date format.
 */
function wanderlist_arrival_date( $post ) {
  // If our arrival date isn't entered, use the date the post was published instead
  $date = get_post_meta( $post, 'wanderlist-arrival-date', true );
  if ( !$date) :
    $date = get_the_date( $date_format );
  endif;

  // Format our date according to our preferred format
  $date_format = 'F jS';
  $arrival_date = date( $date_format, strtotime( $date ) );
  return $arrival_date;
}

/**
 * Show a list of trips.
 *
 * This will show a list of all trips completed.
 * Uses the "trip" taxonomy.
 */
function wanderlist_list_trips( $limit = null, $show = 'default' ) {

  $trips = get_terms( 'wanderlist-trip', array(
    'hide_empty'        => true,
    'childless'         => false,
  ) );

  $output = "<ul>";
  foreach ( $trips as $trip ) :
    $output .= '<li><a href="'. esc_url( get_term_link( $trip, 'wanderlist-trip' ) ) . '" title="' . $trip->description . '">' . $trip->name . "</a></li>";
  endforeach;
  $output .= "</ul>";
  return $output;
}

/**
 * Count total countries visited.
 */
function wanderlist_all_countries() {

  // Get our "home" country, so we can be sure it's excluded
  $home = get_term_by( 'name', 'home', 'wanderlist-country' );

  $countries = get_terms( 'wanderlist-country', array(
      'hide_empty'        => false, // At least for now.
      'childless'         => true, // Only count countries that don't have sub-countries
      'exclude'           => $home->term_id, // Exclude "home" country
  ) );
  return $countries;
}

/**
 * Display a map of some kind.
 * This will need to be automated to draw countries and show points.
 */
function wanderlist_show_map( $overlay = null ) {
  $output = '<div id="map">';
  if ( 'upcoming' === $overlay ):
    $output .= '<div class="wanderlist-widget wanderlist-location-widget">';
    $output .= '<h3>' . esc_html__( 'Adventure Ahoy!', 'wanderlist' ) . '</h3>';
    $output .= wanderlist_list_locations( '4' );
    $output .= '</div><!-- .flare-location-widget -->';
  endif;
    $output .= '</div><!-- .map -->';
  return $output;
}

/**
 * For trip overview pages, we want to show all posts.
 * We also want to make sure they're ordered in the order
 * we visited them. These functions modify our query for
 * this taxonomy only. For any other taxonomy, we return
 * the default settings.
 */

// Since our arrival date is a custom meta key, we need to do some
// database-fu in order to order by its value. Here, we're performing
// a database join so we can access the arrival date.
function wanderlist_trip_join( $wp_join ) {
  if( is_tax( 'wanderlist-trip' )) :
    global $wpdb;
    $wp_join .= " LEFT JOIN (
        SELECT post_id, meta_value as arrival_date
        FROM $wpdb->postmeta
        WHERE meta_key =  'wanderlist-arrival-date' ) AS PLACE
        ON $wpdb->posts.ID = PLACE.post_id ";
  endif;
  return $wp_join;
}
add_filter('posts_join', 'wanderlist_trip_join' );

// Order first by our arrival date, and then by our post date
// Ascending order means the place we visited first shows up first.
function wanderlist_trip_order( $orderby ) {
  if( is_tax( 'wanderlist-trip' )) :
    return "PLACE.arrival_date ASC, post_date ASC";
  else :
    return $orderby;
  endif;
}
add_filter('posts_orderby', 'wanderlist_trip_order' );

// Remove default limit for wanderlist-trip taxonomy, show all places!
function wanderlist_trip_limit( $limits ) {
  if( is_tax( 'wanderlist-trip' ) ) :
    return "";
  else :
    return $limits;
  endif;
}
add_filter('post_limits', 'wanderlist_trip_limit' );
