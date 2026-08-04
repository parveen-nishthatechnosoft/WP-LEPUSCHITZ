<?php
$lSubcategoryId = $_GET['subcategory'];
$category = get_field('parent', $lSubcategoryId);

?>

<div class="breadcrumbs mobi">
    <span class="breadbcrumb-green"><a href="/">Home</a></span>
    <span class="breadcrumb-purple">/</span>&nbsp;
    <span class="breadbcrumb-green"><a href="/products">Produkte</a></span>
    &nbsp;<span class="breadcrumb-purple">/</span>
    <span class="breadbcrumb-green"><a href="<?php echo get_post_field('guid', $category) . "?category=" . get_post_field('ID', $category); ?>"><?php echo get_post_field('post_title', $category); ?></a></span>
    <span class="breadcrumb-purple">/</span>&nbsp;
    <span class="breadcrumb-purple"><?php echo get_post_field('post_title', $lSubcategoryId); ?></span>
</div>

<div class="banners">
    <div class="banner-left">

        <img src="<?php the_field('source_image', $lSubcategoryId); ?>" alt="">

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
    &nbsp;<span class="breadcrumb-purple">/</span>
    <span class="breadbcrumb-green"><a href="<?php echo get_permalink($category) . "?category=" . get_post_field('ID', $category); ?>"><?php echo get_post_field('post_title', $category); ?></a></span>
    <span class="breadcrumb-purple">/</span>&nbsp;
    <span class="breadcrumb-purple"><?php echo get_post_field('post_title', $lSubcategoryId); ?></span>
</div>

<div class="left-and-right padding-top-0">
    <div class="left-column desktop">
        <?php include "_accordion.php"; ?>
    </div>
    <div class="right-column">
        <div class="archive">
            <div class="gallery-container">
                <div class="gallery">
                    <?php

                    $query = new WP_Query(array(
                        'post_type' => 'product',
                        'post_status' => 'publish',
                        'meta_query' => array(
                            array(
                                'key' => 'parent',
                                'value' => $lSubcategoryId
                            ),
                            array(
                                'key' => 'source_image',
                                'value' => array(''),
                                'compare' => 'NOT IN'
                            )
                        ),
                    ));

                    while ($query->have_posts()) {
                        $query->the_post();

                        // Check if there is a image
                        $lThumbnailImage = getProductThumbnail(get_the_ID());
                        if (!empty($lThumbnailImage)) {
                            ?>

                            <a href="<?php echo get_permalink() . "?product_id=" . get_the_ID(); ?>" class="gallery-element">
                                <div class="gallery-image" style="background-image: url('<?php echo($lThumbnailImage); ?>')"></div>
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
