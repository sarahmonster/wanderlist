<?php
/**
 * The template for displaying trip overview pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Wanderlist
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

      <header class="page-header">
        <?php
    	  the_archive_title( '<h1 class="page-title">', '</h1>' );
    	  the_archive_description( '<div class="taxonomy-description">', '</div>' );
    	?>
      </header><!-- .page-header -->

    <?php if ( have_posts() ) : ?>

      <div class="wanderlist-map-widget">
        <?php echo wanderlist_show_map(); ?>
      </div>

      <?php /* Start the Loop */ ?>
      <div class="wanderlist-widget wanderlist-trip-overview-widget">
        <h3 class="widget-title"><?php esc_html_e( 'Where I went', 'wanderlist' ); ?></h3>
        <dl>
        <?php
        while ( have_posts() ) : the_post();
            echo wanderlist_format_location( get_the_ID() );
        endwhile;
        ?>
        </dl>
      </div>

    	<?php the_posts_navigation(); ?>

    <?php else : ?>

    	<?php get_template_part( 'content', 'none' ); ?>

    <?php endif; ?>

    </main><!-- #main -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
