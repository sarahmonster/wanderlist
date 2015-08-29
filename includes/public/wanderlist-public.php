<?php
/**
 * Our public code, available only to the front-end of the site.
 *
 * This will register our shortcodes, collect some data, and load any
 * scripts and stylesheets we need to make things look pretty.
 *
 * @package Wanderlist
 */

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
function wanderlist_list_locations( $limit = null, $show = future ) {

  if ( 'all' === $show ) :
    $post_status = array( 'future', 'publish' );
    $order = DESC;
  elseif ( 'past' === $show ) :
    $post_status = 'publish';
    $order = DESC;
  else :
    $post_status = 'future';
    $order = ASC;
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

  while ( $location_query->have_posts() ):
    $location_query->the_post();
    $locations .= '<dt>' . esc_html( get_the_date( 'F jS' ) ) . '</dt>';
    $locations .= '<dd>' . esc_html( get_the_title() ) . '</dd>';
  wp_reset_postdata();
  endwhile;

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
 * Register a shortcode for location tasks.
 * This just makes it stupid easy to drop into a page.
 *
 * Usage: [wanderlist-location], by default will show current location.
 *
 * Pass the "show" parameter to show different information.
 * For now, [wanderlist-location show=current] will show current (or most recent) location,
 * [wanderlist-location show=countries] will show total number of countries.
 */
function wanderlist_location_shortcode( $atts, $content = null  ){
  $a = shortcode_atts( array(
      'show' => 'current',
  ), $atts );

  if ( 'current' === $a['show'] ):
    return '<span class="wanderlist-current-location">' . wanderlist_get_current_location() . '</span>';
  endif;

  if ( 'countries' === $a['show'] ):
    return '<span class="wanderlist-country-count">' . count( wanderlist_all_countries() ) . '</span>';
  endif;
}

function wanderlist_register_location_shortcode() {
  add_shortcode( 'wanderlist-location', 'wanderlist_location_shortcode' );
}

add_action( 'init', 'wanderlist_register_location_shortcode' );

/**
 * Display a map of some kind.
 * This will need to be automated to draw countries and show points.
 */
function wanderlist_show_map( $overlay = null ) {
?>
  <div id="map">
    <div class="flare-location-widget">
      <?php if ( 'upcoming' === $overlay ): ?>
        <h3><?php esc_html_e( 'Adventure Ahoy!', 'wanderlist' ); ?></h3>
        <dl>
          <dt>Today</dt>
          <dd><?php echo wanderlist_get_current_location(); ?></dd>
          <?php echo wanderlist_list_locations(); ?>
        </dl>
      <?php endif; ?>
    </div><!-- .flare-location-widget -->
  </div><!-- .map -->
<?php }


/**
 * Register a shortcode to display maps.
 *
 * Usage: [wanderlist-map], by default will show a map of upcoming locations.
 * If there are no upcoming locations, it will default to most-recently-visited locations.
 *
 * Pass the "show" parameter to show different information.
 * For now, [wanderlist-map show=current] will show upcoming (or most recent) locations,
 * [wanderlist-map show=countries] will show all countries visited.
 */
function wanderlist_map_shortcode( $atts, $content = null  ){
  $a = shortcode_atts( array(
      'show' => 'current',
  ), $atts );

  if ( 'current' === $a['show'] ):
    return wanderlist_show_map( 'upcoming' );
  endif;

  if ( 'countries' === $a['show'] ):
    return '<span class="wanderlist-country-count">' . count( wanderlist_all_countries() ) . '</span>';
  endif;
}

function wanderlist_register_map_shortcode() {
  add_shortcode( 'wanderlist-map', 'wanderlist_map_shortcode' );
}

add_action( 'init', 'wanderlist_register_map_shortcode' );


/**
 * Register a shortcode to display an overview of our travels.
 * This shortcode will be used on the primary landing page for our plugin.
 *
 * Usage: [wanderlist-overview], by default will show a dashboard of stats and widgets.
 * If there are no upcoming locations, it will default to most-recently-visited locations.
 *
 * We'll set up a settings page later to fine-tune its output, or
 * potentially allow users to pass parameters. For now, no options.
 */
function wanderlist_overview_shortcode( $atts, $content = null  ){
  $a = shortcode_atts( array(
      'show' => 'current',
  ), $atts );

  $widgets[] = array(
                'content' => wanderlist_list_locations(),
                'title'   => esc_html__( 'Upcoming trips', 'wanderlist' ),
              );

  $widgets[] = array(
              'content' => wanderlist_list_locations( 20, 'past' ),
              'title'   => esc_html__( 'Most recent trips', 'wanderlist' ),
            );

  $widgets[] = array(
                'content' => '<span class="wanderlist-country-count">' . count( wanderlist_all_countries() ) . '</span>',
                'title'   => esc_html__( 'Total countries visited', 'wanderlist' ),
              );

  foreach ( $widgets as $widget ) :
    $return .= '<div class="wanderlist-widget">';
    $return .= '<h3 class="widget-title">' . $widget['title'] . '</h3>';
    $return .= $widget['content'];
    $return .= "</div>";
  endforeach;

  return $return;
}

function wanderlist_register_overview_shortcode() {
  add_shortcode( 'wanderlist-overview', 'wanderlist_overview_shortcode' );
}

add_action( 'init', 'wanderlist_register_overview_shortcode' );
