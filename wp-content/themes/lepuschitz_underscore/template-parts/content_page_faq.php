<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package lepuschitz
 */
?>

<div class="breadcrumbs">
    <span class="breadbcrumb-green"><a href="/">Home</a></span>
    <span class="breadcrumb-purple">/</span>&nbsp;
    <span class="breadcrumb-purple">FAQ</span>
</div>

<div class="left-and-right">
    <div class="accordion_faq">

        <?php

        $query = new WP_Query(array(
            'post_type' => 'faq_question',
            'post_status' => 'publish'
        ));

        while ($query->have_posts()) : $query->the_post(); ?>

            <button class="accordion"><?php the_field('question'); ?></button>
            <div class="accordion-panel">
                <p><?php the_field('answer'); ?></p>
            </div>
        
        <?php endwhile; ?>

    </div>
</div>

<div class="space-filler-small"></div>
