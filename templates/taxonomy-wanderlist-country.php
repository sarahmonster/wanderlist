<?php
/**
 * The template for displaying trip overview pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Wanderlist
 */

get_header(); ?>

<div id="container" class="content-area">
	<div id="content"><!-- Extra wrapper for themes that need it -->
		<main id="main" class="site-main" role="main">

			<header class="page-header">
				<?php
				the_archive_title( '<h1 class="page-title archive-title">', '</h1>' );
				the_archive_description( '<div class="taxonomy-description archive-meta">', '</div>' );
				?>
			</header><!-- .page-header -->

		<?php if ( have_posts() ) : ?>
		<article class="hentry">
				<div class="entry-content">

					<div class="wanderlist-map-widget">
						<?php echo wanderlist_show_map(); ?>
					</div>

					<?php /* Start the Loop */ ?>
					<div class="wanderlist-widget wanderlist-country-overview-widget">
						<h3 class="widget-title"><?php esc_html_e( 'Places I&rsquo;ve Visited', 'wanderlist' ); ?></h3>
						<dl>
						<?php
						while ( have_posts() ) : the_post();
							echo wanderlist_format_location( get_the_ID(), array( 'date_format' => 'range' ) );
						endwhile;
						?>
						</dl>
					</div>

					<?php the_posts_navigation(); ?>
				</div>
		</article>

		<?php else :
			get_template_part( 'content', 'none' );
		endif; ?>

		</main><!-- #main -->
	</div><!-- #content -->
</div><!-- #container -->

<?php //get_sidebar(); ?>
<?php get_footer(); ?>
