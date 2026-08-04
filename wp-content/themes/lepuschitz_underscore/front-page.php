<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package lepuschitz
 */
get_header();

?>
<div id="container">

    <div class="clear-both"></div>

    <div class="breadcrumbs hide">

    </div>

    <div class="left-and-right">
        <div class="desktop left-column">
            <?php include "template-parts/_accordion.php"; ?>
        </div>

        <div class="right-column padding-left-right">
            <div class="archive">
                <h1 class="text-left">
                    Alle <span class="bold">Produktkategorien</span>:
                </h1>
                <div class="gallery-container">
                    <div class="gallery">
                        <?php

                        $query = new WP_Query(array(
                            'post_type' => 'product_category',
                            'post_status' => 'publish'
                        ));

                        while ($query->have_posts()) : $query->the_post(); ?>

                            <a href="<?php echo get_permalink() . "?category=" . get_the_ID(); ?>" class="gallery-element">
                                <div class="gallery-image" style="background-image: url('<?php the_field('source_image'); ?>')"></div>
                                <span><?php the_title(); ?></span>
                            </a>

                        <?php endwhile; ?>

                    </div>
                </div>
            </div>

            <div class="bottomtext">
                <div class="banner-right">
                    <h2 class="text-left mobi-text-justify">
                        Willkommen in der LP-Werbewelt!
                    </h2>
                    <p class="text mobi-text text-left mobi-text-justify">
                        Lepuschitz-Promotion ist Ihr Fachmann für Werbemittel. Über 50.000 Werbeartikel stellen wir bereit. Ob Sie die Werbegeschenke, Arbeitsbekleidung, Feuerzeug, Kugelschreiber zum Bedrucken, Besticken, Gravieren oder einfach nur so haben möchten, bei uns können Sie Ihr Werbemittel gleich Online anfragen. Wir bieten Ihnen als Unternehmen, Verein oder Institution, Streuartikel, Giveaways oder hochwertige Werbegeschenke für jede Gelegenheit. Unser Ziel ist, Ihre Wünsche zu erfüllen. Es gibt unendlich viele Möglichkeiten Ihre Werbung zu gestalten. Damit Ihnen das alles ins Konzept passt, beraten wir Sie rund um Fragen zu unseren Produkten. Dabei finden wir für ungewöhnliche Ideen auch außergewöhnliche Lösungen! Unser Plus ist, über 25 Jahre Erfahrung in der Werbemittelbranche, die persönliche Beratung, kleine und große Mengen mit kurzer Lieferzeit.
                        Unser Motto „Geht nicht – gibt’s nicht“
                    </p>
                </div>
            </div>
        </div>
    </div>


<?php
get_footer();
