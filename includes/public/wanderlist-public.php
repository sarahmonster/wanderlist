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
 * Show a list of locations.
 *
 * This will default to showing all upcoming locations.
 * Passing the limit and show params will change its output.
 */
function wanderlist_list_locations( $limit = null, $show = 'default' ) {

  if ( 'all' === $show ) :
    $post_status = array( 'future', 'publish' );
    $order = DESC;
  elseif ( 'past' === $show ) :
    $post_status = 'publish';
    $order = DESC;
  elseif ( 'upcoming' === $show ):
    $post_status = 'future';
    $order = ASC;
  elseif ( 'default' === $show ) :
    $post_status = 'future';
    $order = ASC;
    $limit = $limit - 1;
  endif;

  $args = array(
    'post_type'      => 'wanderlist_location',
    'post_status'    => $post_status,
    'posts_per_page' => $limit,
    'orderby'        => 'date',
    'order'          => $order,
    'tax_query'      => array(
      array(
        'taxonomy' => 'wanderlist_country',
        'field'    => 'slug',
        'terms'    => 'home',
        'operator' => 'NOT IN',
      ),
    ),
  );

  $location_query = new WP_Query( $args );

  $locations = "<dl>";

  if ( 'default' === $show ) :
    $locations .= '<dt>' . esc_html__( 'Today', 'wanderlist' ) . '</dt>';
    $locations .= '<dd>' . wanderlist_get_current_location() . '</dd>';
  endif;

  while ( $location_query->have_posts() ) :
    $location_query->the_post();

    // We need to figure out our place's country. Here we go.
    $countries = wp_get_object_terms( get_the_ID(), 'wanderlist_country', array( 'fields' => 'names' ) );
    if ( $countries ) :
      foreach ( $countries as $country => $name ) :
        $the_country = ', ' . $name;
      endforeach;
    else :
      $the_country = '';
    endif;

    $locations .= '<dt>' . esc_html( get_the_date( 'F jS' ) ) . '</dt>';
    $locations .= '<dd>' . esc_html( get_the_title() ) .'<span class="wanderlist-country">' . esc_html( $the_country ) . '</span></dd>';
  wp_reset_postdata();
  endwhile;

  $locations .= "</dl>";

  return $locations;
}


/**
 * Count total countries visited.
 */
function wanderlist_all_countries() {

  // Get our "home" country, so we can be sure it's excluded
  $home = get_term_by( 'name', 'home', 'wanderlist_country' );

  $countries = get_terms( 'wanderlist_country', array(
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
?>
  <div id="map">
    <div class="wanderlist-widget wanderlist-location-widget">
      <?php if ( 'upcoming' === $overlay ): ?>
        <h3><?php esc_html_e( 'Adventure Ahoy!', 'wanderlist' ); ?></h3>
          <?php echo wanderlist_list_locations( '4' ); ?>
      <?php endif; ?>
    </div><!-- .flare-location-widget -->
  </div><!-- .map -->
<?php }

