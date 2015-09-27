<?php
/**
 * The template for displaying single location posts.
 *
 * @package Wanderlist
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php while ( have_posts() ) : the_post(); ?>


			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<header class="entry-header">
					<div class="entry-meta">
						<span class="posted-on">
							<?php echo esc_html( wanderlist_date( get_the_ID(), 'arrival' ) ); ?>
							<?php
							if ( wanderlist_date( get_the_ID(), 'departure' ) ) :
								echo esc_html_x( ' to ', 'Between two dates', 'wanderlist' );
								echo esc_html( wanderlist_date( get_the_ID(), 'departure' ) );
							endif;
							?>
						</span>
						<span class="tags-links">
							<?php the_terms( get_the_ID(), 'wanderlist-trip', 'Trip: ', ', ', ' &middot;' ); ?>
							<?php the_terms( get_the_ID(), 'wanderlist-country', '', ', ', $after ); ?>
						</span>
					</div><!-- .entry-meta -->
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
				</header><!-- .entry-header -->

				<div class="entry-content">
					<?php the_content(); ?>
					<?php
						wp_link_pages( array(
							'before' => '<div class="page-links">' . __( 'Pages:', 'wanderlist' ),
							'after'  => '</div>',
						) );
					?>
				</div><!-- .entry-content -->

				<footer class="entry-footer">
					<?php the_post_navigation( array(
						'prev_text'          => '<span>Previous</span> %title',
						'next_text'          => '<span>Next</span> %title',
					) );
					?>
				</footer><!-- .entry-footer -->
			</article><!-- #post-## -->

			<?php
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
			?>

		<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
