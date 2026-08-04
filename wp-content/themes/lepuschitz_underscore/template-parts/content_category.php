<?php
$category = $_GET['category'];

?>

<div class="breadcrumbs mobi">
    <span class="breadbcrumb-green"><a href="/">Home</a></span>
    <span class="breadcrumb-purple">/</span>&nbsp;
    <span class="breadbcrumb-green"><a href="/products">Produkte</a></span>
    &nbsp;<span class="breadcrumb-purple">/</span>&nbsp;
    <span class="breadcrumb-purple"><?php get_post_field('post_title', $category); ?></span>
</div>

<div class="banners">
    <div class="banner-left text-dark">
        <?php echo get_post_field('post_title', $category);?>
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
    <span class="breadbcrumb-green"><a href="/products">Produkte</a></span>
    &nbsp;<span class="breadcrumb-purple">/</span>&nbsp;
    <span class="breadcrumb-purple"><?php echo get_post_field('post_title', $category); ?></span>
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
                        'post_type' => 'product_subcategory',
                        'post_status' => 'publish',
                        'posts_per_page' => -1,
                        'meta_key' => 'parent',
                        'meta_value' => $category,
                        'orderby' => 'title',
                        'order' => 'ASC',
                    ));

                    while ($query->have_posts()) {
                        $query->the_post();

                        $lSubCatsProducts = get_option('ProductsInSubCat_' . get_the_ID());
                        if ($lSubCatsProducts > 0) {
                            ?>
                            <a href="<?php echo get_permalink() . "?subcategory=" . get_the_ID(); ?>" class="gallery-element">
                                <div class="gallery-image" style="background-image: url('<?php the_field('source_image'); ?>')"></div>
                                <span><?php the_title(); ?></span>
                            </a>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
