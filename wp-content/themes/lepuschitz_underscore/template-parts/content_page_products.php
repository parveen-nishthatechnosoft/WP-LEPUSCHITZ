<?php
$category_name = get_post_field('post_name');
$category_title = "Unsere Produkte";
$description = get_field('description');
?>

<div class="breadcrumbs mobi">
    <span class="breadbcrumb-green"><a href="/">Home</a></span>
    <span class="breadcrumb-purple">/</span>&nbsp;
    <span class="breadcrumb-purple">Produkte</span>
</div>

<div class="banners">
    <div class="banner-left text-dark" style='background-image: none;'>
        <?php echo $category_title;?>
    </div>
    <div class="banner-right">
        <div>
            <p class="text mobi-text text-left mobi-text-justify">
                Wir beraten und beliefern Werbegeschenke seit 25 Jahren und arbeiten mit mehr als 150 Partnern weltweit zusammen.
            </p>
            <p class="text mobi-text text-left mobi-text-justify">
                Daher können wir sämtliche Kundenwünsche in die Praxis umsetzen. Unter dem Motto „Geht nicht - gibt‘s nicht“ stehen Ihnen alle unsere Mitarbeiter gerne zur Verfügung. Damit auch Klein- u. Mittelbetriebe Ihre Werbung gut umsetzen können, führen viele Artikel keine oder kleine Mindestmengen.
            </p>
            <p class="text mobi-text text-left mobi-text-justify">
                Wir freuen uns auf Ihre Anfragen und stehen Ihnen jederzeit gerne zur Verfügung. Ihr Lepuschitz-Promotion Team
            </p>
        </div>
    </div>
</div>

<div class="breadcrumbs desktop">
    <span class="breadbcrumb-green"><a href="/">Home</a></span>
    <span class="breadcrumb-purple">/</span>&nbsp;
    <span class="breadcrumb-purple">Produkte</span>
</div>

<div class="left-and-right">
    <div class="left-column desktop">
        <?php include "_accordion.php"; ?>
    </div>
    <div class="right-column padding-left-right">
        <div class="archive">
            <div class="gallery-container">
                <div class="gallery">
                    <?php

                    $query = new WP_Query(array(
                        'post_type' => 'product_category',
                        'post_status' => 'publish',
                        'orderby' => 'title',
                        'order' => 'ASC',
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
    </div>
</div>
