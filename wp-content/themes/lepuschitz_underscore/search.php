<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package lepuschitz
 */

get_header();

?>

<main id="primary" class="site-main">

    <article class="article">
        <?php if ( have_posts() ) : ?>

            <div class="breadcrumbs">
                <span class="breadbcrumb-green"><a href="/">Home</a></span>
                <span class="breadcrumb-purple">/</span>&nbsp;
                <span class="breadcrumb-purple"><?php echo 'Suchergebnisse für: ' . get_search_query(); ?></span>
            </div>

            <div class="left-and-right">
                <div class="left-column">
                    <?php include "template-parts/_accordion.php"; ?>
                </div>
                <div class="right-column">

                    <?php
                    /* Start the Loop */

                    $categories = [];
                    $subcategories = [];
                    $products = [];

                    while ( have_posts() ) :
                        the_post();

                        $postType = get_post_type();

                        switch ($postType){
                            case "product_category":
                                $categories[] = get_the_ID();
                                break;

                            case "product_subcategory":
                                $subcategories[] = get_the_ID();
                                break;

                            case "product":
                                $products[] = get_the_ID();
                                break;
                        }

                    endwhile;

                    if (count($categories)): ?>

                        <div class="archive">
                            <h1 class="text-left mobi-text-left">Produktkategorien:</h1>
                            <div class="gallery-container">
                                <div class="gallery">

                                    <?php

                                    foreach ($categories as $category): ?>

                                        <a href="<?php echo get_permalink($category) . "?category=" . $category; ?>" class="gallery-element">
                                            <div class="gallery-image" style="background-image: url('<?php the_field('source_image', $category); ?>')"></div>
                                            <span><?php echo get_the_title($category); ?></span>
                                        </a>

                                    <?php endforeach;

                                    ?>

                                </div>
                            </div>
                        </div>

                    <?php endif;

                    if (count($subcategories)): ?>

                        <div class="archive">
                            <h1 class="text-left mobi-text-left">Produktgruppen:</h1>
                            <div class="gallery-container">
                                <div class="gallery">

                                    <?php

                                    foreach ($subcategories as $subcategory): ?>

                                        <a href="<?php echo get_permalink($subcategory) . "?subcategory=" . $subcategory; ?>" class="gallery-element">
                                            <div class="gallery-image" style="background-image: url('<?php the_field('source_image', $subcategory); ?>')"></div>
                                            <span><?php echo get_the_title($subcategory); ?></span>
                                        </a>

                                    <?php endforeach;

                                    ?>

                                </div>
                            </div>
                        </div>

                    <?php endif;

                    if (count($products)): ?>

                        <div class="archive">
                            <h1 class="text-left mobi-text-left">Produkte:</h1>
                            <div class="gallery-container">
                                <div class="gallery">

                                    <?php

                                    foreach ($products as $product): ?>

                                        <a href="<?php echo get_permalink($product) . "?product_id=" . $product; ?>" class="gallery-element">
                                            <div class="gallery-image" style="background-image: url('<?php the_field('source_image', $product); ?>')"></div>
                                            <span><?php echo get_the_title($product); ?></span>
                                        </a>

                                    <?php endforeach;

                                    ?>

                                </div>
                            </div>
                        </div>

                    <?php endif;

                    ?>

                </div>
            </div>
        <?php
        else :

            get_template_part( 'template-parts/content', 'none' );

        endif;
        ?>
    </article>

</main><!-- #main -->

<?php
get_footer();
