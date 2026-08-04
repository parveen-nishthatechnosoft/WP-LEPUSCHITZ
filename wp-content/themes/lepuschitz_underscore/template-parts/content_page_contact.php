<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package lepuschitz
 */

$ID = get_the_ID();
?>

<div class="breadcrumbs">
    <span class="breadbcrumb-green"><a href="/">Home</a></span>
    <span class="breadcrumb-purple">/</span>&nbsp;
    <span class="breadcrumb-purple">Kontakt</span>
</div>

<div class="left-and-right desktop">
    <div class="half-screen no-margin">
        <div class="div-contact-content">
            <h1 class="text-center mobi-text-center">Lepuschitz Promotion</h1>
            <p>
                <span >
                    Servicetelefon:&nbsp;
                    <a class ="link-underline" href="tel:+432246503330">+43 2246 50333 0</a>
                </span>
                <br>
                <span>Fax: +43 2246 50333 90</span>
                <br>
                <span><a class ="link-underline" href="#">office&#64;lepuschitz-promotion.at</a></span>
            </p>
            <p>
                <span>Bürozeiten</span>
                <br>
                <span>Montag - Donnerstag 8:00 - 17:00</span>
                <br>
                <span>Freitag 8:00 - 14:00</span>
            </p>
            <p>
                <span>Lepuschitz-Promotion</span>
                <br>
                <span>2201 Gerasdorf/Wien, Guido Rütgers Straße 12</span>
                <br>
                <span>UID-ATU 66329323 I PSI Nr. 15702</span>
            </p>
            <p>
                <span>
                    <a class ="link-underline" href="datenschutzerklaerung/">Unsere Datenschutzerklärung</a>&nbsp;
                </span>
            </p>
        </div>
    </div>

    <div class="divider"></div>

    <div class="half-screen">
        <div class="contact-form">
            <h1 class="text-center mobi-text-center">Schreiben Sie uns</h1>
            <?php the_content(); ?>
        </div>
    </div>
</div>

<div class="left-and-right mobi padding-left-right">
    <div class="div-contact-content mobi-text-center">
        <h1 class="text-center mobi-text-center">Lepuschitz Promotion</h1>
        <p>
                <span >
                    Servicetelefon:&nbsp;
                    <a class ="link-underline" href="tel:+432246503330">+43 2246 50333 0</a>
                </span>
            <br>
            <span>Fax: +43 2246 50333 90</span>
            <br>
            <span><a class ="link-underline" href="#">office&#64;lepuschitz-promotion.at</a></span>
        </p>
        <p>
            <span>Bürozeiten</span>
            <br>
            <span>Montag - Donnerstag 8:00 - 17:00</span>
            <br>
            <span>Freitag 8:00 - 14:00</span>
        </p>
        <p>
            <span>Lepuschitz-Promotion</span>
            <br>
            <span>2201 Gerasdorf/Wien, Guido Rütgers Straße 12</span>
            <br>
            <span>UID-ATU 66329323 I PSI Nr. 15702</span>
        </p>
        <p>
                <span>
                    <a class ="link-underline" href="#">Unsere Datenschutzerklärung</a>&nbsp;(PDF, 244kb)
                </span>
        </p>
    </div>

    <div class="contact-form">
        <h1 class="text-center mobi-text-center">Schreiben Sie uns</h1>
        <?php the_content(); ?>
    </div>
</div>

<div class="space-filler-small"></div>
