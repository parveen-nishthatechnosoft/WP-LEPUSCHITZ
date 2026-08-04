<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package lepuschitz
 */

get_header();
?>

	<main id="primary" class="site-main">

		<section class="error-404 not-found">
            <div class="mainbox">

                    <div class="err">4</div>
                    <i class="far fa-question-circle fa-spin"></i>
                    <div class="err2">4</div>

                <div class="msg">Vielleicht hat sich diese Seite verschoben? Wurde gelöscht? Versteckt es sich in Quarantäne? Nie existiert?
                    <p>Gehen wir zur <a href="http://lepuschitz-promotion.ewebdev2.proman.at/">Homepage</a></p>
                </div>
            </div>
		</section><!-- .error-404 -->


	</main><!-- #main -->

<?php
get_footer();
