<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package lepuschitz
 */

$ID = get_the_ID();

$args = array(
    'post_type' => 'page',
    'post_parent' => $ID
);

$new_query = new WP_Query($args);

while($new_query -> have_posts() ) {
    $new_query->the_post();

    array_push($children, get_the_ID());
}
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

	<?php lepuschitz_post_thumbnail(); ?>

	<div class="entry-content">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'lepuschitz' ),
				'after'  => '</div>',
			)
		);
		?>
	</div><!-- .entry-content -->

	<?php if ( get_edit_post_link() ) : ?>
		<div class="footer">
			<?php
			edit_post_link(
				sprintf(
					wp_kses(
						/* translators: %s: Name of current post. Only visible to screen readers */
						__( 'Edit <span class="screen-reader-text">%s</span>', 'lepuschitz' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				),
				'<span class="edit-link">',
				'</span>'
			);
			?>
		</div><!-- .footer -->
	<?php endif; ?>
</article><!-- #post-<?php the_ID(); ?> -->
