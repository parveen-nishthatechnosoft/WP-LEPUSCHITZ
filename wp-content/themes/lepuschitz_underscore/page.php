<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package lepuschitz
 */

get_header();
?>

    <main id="primary" class="site-main">
        <article class="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <?php
        while ( have_posts() ) :
            the_post();

            $post_name = get_post_field('post_name');

            //get_template_part( 'template-parts/content', get_post_type() );
            if ($post_name == 'products'){
                get_template_part( 'template-parts/content_page_products', get_post_type() );
            } else 
            if ($post_name == 'ueber-uns'){
                get_template_part( 'template-parts/content_page_ueber_uns', get_post_type() );
            } else
            if($post_name == "kontakt"){
                get_template_part( 'template-parts/content_page_contact', get_post_type() );
            } else 

            if($post_name == "faq") {
                get_template_part( 'template-parts/content_page_faq', get_post_type() );
            } else 

            if($post_name == "katalogschnittstelle"){
                get_template_part( 'template-parts/content_catalogue_interface', get_post_type() );
            } else 

            if($post_name == "tutorial"){
                get_template_part( 'template-parts/content_page_tutorial', get_post_type() );
            } else {
                get_template_part( 'template-parts/content-page' );
            }



        endwhile; // End of the loop.
        ?>

        </article><!-- #post-<?php the_ID(); ?> -->

    </main><!-- #main -->

<?php
get_footer();
