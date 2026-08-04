<?php
$lDebug = '';
if (isset($_GET['debug']))
    $lDebug = '&debug=1';

get_header();
$lId = get_the_ID();
?>
    <main id="primary" class="site-main">
        <article class="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php
            // Check if administrator
            if (current_user_can('administrator')) {
                ?>
                <h1><?php the_title() ?> Katalog-Tools</h1>
            <?php
                $lMandateId = get_field('catalog_mandantId', $lId);
                $lMandator = new LMandator('', '', get_field('mandate-type', $lMandateId));
                $lMandator->RenderTools($lId);
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
