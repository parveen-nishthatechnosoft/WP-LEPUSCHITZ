<?php
$lDebug = '';
if (isset($_GET['debug']))
    $lDebug = '&debug=1';

get_header();
$lId = get_the_ID();
?>
    <main id="primary" class="site-main catalog-tools-page">
        <article class="article catalog-tools-shell" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php
            // Check if administrator
            if (current_user_can('administrator')) {
                ?>
                <header class="catalog-tools-header">
                    <span class="catalog-tools-eyebrow">Katalogverwaltung</span>
                    <h1><?php the_title() ?></h1>
                    <p>Importieren und aktualisieren Sie die Produktdaten dieses Lieferanten.</p>
                </header>
            <?php
                $lMandateId = get_field('catalog_mandantId', $lId);
                $lMandator = new LMandator('', '', get_field('mandate-type', $lMandateId));
                $lMandator->RenderTools($lId);
            } else {
            ?>
                <div class="catalog-tools-notice catalog-tools-notice--error">
                    <strong>Zugriff nicht möglich</strong>
                    <span>Sie benötigen Administratorrechte, um Katalogdaten zu importieren.</span>
                </div>
                <?php
            }
            ?>
        </article>
    </main><!-- #main -->
<?php
get_footer();
