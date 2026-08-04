<?php
get_header();
?>
    <main id="primary" class="site-main">
        <article class="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php
            // Check if administrator
            if (current_user_can('administrator')) {
                if (have_posts()) {
                    ?>

                    <h1 class="page-title">Kataloge</h1>
                    <?php
                    /* Start the Loop */
                    while (have_posts()) {
                        the_post();
                        get_template_part('template-parts/preview', get_post_type());
                    }
                    ?>
                    <?php
                } else {
                    ?>
                    <p>Keine Kataloge verfügbar!</p>
                    <?php
                }
            } else {
                ?>
                <p>Nicht genügend Rechte!</p>
                <?php
            }
            ?>
        </article>
    </main><!-- #main -->
<?php
get_footer();
