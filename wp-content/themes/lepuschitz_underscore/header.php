<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package lepuschitz
 */

// Calculate menu entries
$lCategoriesQuery = new WP_Query(array(
    'post_type' => 'product_category',
    'post_status' => 'publish',
    'posts_per_page' => -1,
));

while ($lCategoriesQuery->have_posts()) {
    $lCategoriesQuery->the_post();

    $lCategoryId = get_the_ID();

    // Check if there is already a number for counted elements in the options
    $lProductsInCat = get_option('ProductsInCat_' . $lCategoryId);
    if (!$lProductsInCat) {
        $lProductsInCat = 0;

        // Query subcategories
        $lSubCategoriesQuery = new WP_Query(array(
            'post_type' => 'product_subcategory',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => 'parent',
            'meta_value' => $lCategoryId
        ));

        while ($lSubCategoriesQuery->have_posts()) {
            $lSubCategoriesQuery->the_post();
            $lSubCategoryId = get_the_ID();

            // Query number of products in subcategory
            $lSubCatQueryArgs = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'meta_key' => 'parent',
                'posts_per_page' => -1,
                'meta_value' => $lSubCategoryId
            );
            $lSubCatsProducts = new WP_Query($lSubCatQueryArgs);
            $lProductsInSubCat = $lSubCatsProducts->post_count;
            $lProductsInCat += $lProductsInSubCat;

            if ($lProductsInSubCat > 0) {
                update_option('ProductsInSubCat_' . $lSubCategoryId, $lProductsInSubCat);
            }
        }

        wp_reset_postdata();
        update_option('ProductsInCat_' . $lCategoryId, $lProductsInCat);
    }
    wp_reset_postdata();
}

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <link rel="icon" href="https://www.lepuschitz-promotion.at/fileadmin/site/favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" href="https://www.lepuschitz-promotion.at/fileadmin/site/favicon.ico" type="image/x-icon"/>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
    <div class="desktop">
        <header>
            <div class="inner">
                <h1>Lepuschitz - Promotion Werbeartikel</h1>
                <?php get_search_form(); ?>
                <div id="more-info" class="more-info">
                    <a class="hover-underline" href="tel: +432246503330">+43 (0) 2246 50333 0</a>
                    <a class="hover-highlight" href="#">Hilfe</a>
                </div>
            </div>
        </header>

        <nav class="nav-container">
            <a href="/" class="main-logo"><img src="https://www.lepuschitz-promotion.at/fileadmin/site/img/lepuschitz-logo.png" alt="Lepuschitz-Promotion"></a>
            <div id="main-nav" class="main-nav-container">
                <div class="menu-nav">
                    <ul class="main-nav">
                        <li class="nav-element">
                            <a href="/products" class="ddLink">Produkte</a>
                            <ul class="ddContent hide product-nav">

                                <?php
                                $query = new WP_Query(array(
                                    'post_type' => 'product_category',
                                    'post_status' => 'publish',
                                    'posts_per_page' => -1,
                                    'orderby' => 'title',
                                    'order' => 'ASC',
                                ));

                                while ($query->have_posts()):
                                    $query->the_post();

                                    $parent = get_the_ID();
                                    global $post;
                                    $backup = $post;

                                    ?>

                                    <li>
                                        <a href="<?php echo get_permalink() . "?category=" . get_the_ID(); ?>"><?php the_title(); ?></a>
                                        <ul>
                                            <?php

                                            $subQuery = new WP_Query(array(
                                                'post_type' => 'product_subcategory',
                                                'post_status' => 'publish',
                                                'posts_per_page' => -1,
                                                'meta_key' => 'parent',
                                                'meta_value' => $parent,
                                                'orderby' => 'title',
                                                'order' => 'ASC',
                                            ));

                                            $n = 0;

                                            while ($subQuery->have_posts()) {
                                                $subQuery->the_post();

                                                $lSubCatsProducts = get_option('ProductsInSubCat_' . get_the_ID());
                                                if ($lSubCatsProducts > 0) {
                                                    $n++;
                                                    ?>

                                                    <li>
                                                        <a href="<?php echo(get_the_permalink() . "?subcategory=" . get_the_ID()); ?>"><?php the_title(); ?></a>
                                                    </li>

                                                    <?php
                                                    if ($n == 4): ?>

                                                        <li>
                                                            <a href="<?php echo(get_permalink($parent) . "?category=" . get_the_ID()); ?>">mehr...</a>
                                                        </li>

                                                        <?php break; endif;
                                                }
                                            };
                                            $subQuery->reset_postdata();
                                            $post = $backup;
                                            ?>
                                        </ul>
                                    </li>

                                <?php endwhile;
                                $query->reset_postdata();
                                ?>

                            </ul>
                        </li>
                        <li class="nav-element">
                            <a href="#">Service</a>
                            <ul class="hide ddRegular">
                                <li><a href="/faq">FAQ</a></li>
                                <li><a href="https://www.lepuschitz-promotion.at/wp-content/uploads/2021/04/Druckdatenblatt.pdf" target="_blank">Druckdaten</a></li>
                                <li><a href="/spezifikationen/">Spezifikationen</a></li>
                            </ul>
                        </li>
                        <li class="nav-element">
                            <a href="/ueber-uns">Über uns</a>
                        </li>
                        <li class="nav-element">
                            <a href="/kontakt">Kontakt</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <div class="mobi">
        <div class="mobi-header">
            <a href="/" class="main-logo"><img src="https://www.lepuschitz-promotion.at/fileadmin/site/img/lepuschitz-logo.png" alt="Lepuschitz-Promotion"></a>
            <div class="burger">
                <div class="line-1"></div>
                <div class="line-2"></div>
                <div class="line-3"></div>
            </div>
        </div>
        <div class="inner">
            <?php get_search_form(); ?>
        </div>
        <div id="mobi-nav-container" class="mobi-nav-container">
            <button class='mobi-accordion-parent'>Kategorien</button>
            <div class='accordion-panel'>

                <?php
                $query = new WP_Query(array(
                    'post_type' => 'product_category',
                    'posts_per_page' => -1,
                    'post_status' => 'publish'
                ));

                while ($query->have_posts()):
                    $query->the_post();

                    $parent = get_the_ID();
                    global $post;
                    $backup = $post;

                    ?>
                    <button class="mobi-accordion"><?php the_title(); ?></button>
                    <div class="accordion-panel">

                        <?php

                        $subQuery = new WP_Query(array(
                            'post_type' => 'product_subcategory',
                            'post_status' => 'publish',
                            'posts_per_page' => -1,
                            'meta_key' => 'parent',
                            'meta_value' => $parent
                        ));

                        while ($subQuery->have_posts()) {
                            $subQuery->the_post();

                            $lSubCatsProducts = get_option('ProductsInSubCat_' . get_the_ID());
                            if ($lSubCatsProducts > 0) {
                                ?>
                                <button class="accordion-element">
                                    <a href="<?php echo get_permalink() . "?subcategory=" . get_the_ID(); ?>"><?php the_title(); ?></a>
                                </button>
                                <?php
                            }
                        }
                        $subQuery->reset_postdata();
                        $post = $backup;
                        ?>

                    </div>

                <?php endwhile;
                $query->reset_postdata();
                ?>

            </div>

            <button class='mobi-accordion'>Service</button>
            <div class='accordion-panel'>
                <button class="accordion"><a href="/faq">FAQ</a></button>
                <button class="accordion"><a href="/druckdaten">Druckdaten</a></button>
                <button class="accordion"><a href="/spezifikationen">Spezifikationen</a></button>
            </div>

            <button class="accordion-link">
                <a href="/ueber-uns">Über uns</a>
            </button>

            <button class="accordion-link">
                <a href="/kontakt">Kontakt</a>
            </button>
        </div>
    </div>
