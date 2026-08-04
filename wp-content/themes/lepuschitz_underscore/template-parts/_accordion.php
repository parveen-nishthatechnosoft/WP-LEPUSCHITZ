<h3 class="accordion-title">Kategorien</h3>
<?php

$query = new WP_Query(array(
    'post_type' => 'product_category',
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC'
));

while ($query->have_posts()):
    $query->the_post();

    $active = null;
    $accordion_id = null;
    $parent = get_the_ID();


    global $post;
    $backup = $post;

    if (isset($lCategoryId)) {
        if (get_the_ID() == $lCategoryId) {
            $active = "accordion-active";
            $accordion_id = "current";
            $parent = $lCategoryId;
        }
    }
    ?>

    <button id="<?php echo $accordion_id; ?>" class="accordion <?php echo $active; ?>"><?php the_title(); ?></button>
    <div class="accordion-panel">
        <?php

        $subQuery = new WP_Query(array(
            'post_type' => 'product_subcategory',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => 'parent',
            'meta_value' => $parent,
            'orderby' => 'title',
            'order' => 'ASC'
        ));

        while ($subQuery->have_posts()) {
            $subQuery->the_post();

            $lSubCatsProducts = get_option('ProductsInSubCat_' . get_the_ID());
            if ($lSubCatsProducts > 0) {
                ?>
                <button class="accordion-element">
                    <a href="<?php echo get_permalink() . "?subcategory=" . get_the_ID(); ?>"><?php the_title(); ?></a>
                </button>
                <?php
            }
        }
        $subQuery->reset_postdata();
        $post = $backup;
        ?>
    </div>

<?php endwhile;
$query->reset_postdata();
?>
