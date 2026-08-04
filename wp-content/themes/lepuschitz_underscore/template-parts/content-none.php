<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package lepuschitz
 */

?>

<div class="left-and-right">
    <h1 class="page-title"><?php printf('Keine Ergebnisse für: %s', get_search_query()); ?></h1>

    <p>
        <h2><?php esc_html_e( 'Leider gibt es keine Ergebnisse für Ihre Suche. Versuchen Sie es bitte noch einmal mit anderen Stichwörtern.', 'lepuschitz' ); ?></h2>

        <?php get_search_form(); ?>
    </p>

    <div class="space-filler-large"></div>
</div>