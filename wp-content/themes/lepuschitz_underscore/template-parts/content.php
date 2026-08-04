<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package lepuschitz
 */
$post_type = get_post_type();
?>

<article class="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    if ($post_type == 'product_category')
        get_template_part( 'template-parts/content_category', get_post_type() );

    if ($post_type == 'product_subcategory')
        get_template_part( 'template-parts/content_subcategory', get_post_type() );

    if ($post_type == 'product')
        get_template_part( 'template-parts/content_product', get_post_type() );
    ?>
</article><!-- #post-<?php the_ID(); ?> -->
