<?php
require_once('wp-load.php');
require_once('wp-includes/post.php');

if (isset($_POST['technology'])) {
    $technology = get_post($_POST['technology']);
    $ID = $technology->ID;

    ?>

    <div class="technology" data-id="<?php echo $ID; ?>">
        <div class="technology_code" id="technology_code">
            <?php echo get_post_field('post_title', $ID) ?>
        </div>
        <div class="technology_name" id="technology_name">
            <?php echo get_field('name', $ID) ?>
        </div>
        <div class="ranges" id="ranges">
            <?php if (have_rows('ranges', $ID)):

                while (have_rows('ranges', $ID)):
                    the_row();?>

                    <div class="range" data-colors="<?php echo get_sub_field('number_of_colors'); ?>" data-from="<?php echo get_sub_field('quantity_from'); ?>" data-to="<?php echo get_sub_field('quantity_to'); ?>" data-price="<?php echo get_sub_field('unit_price'); ?>" data-setup="<?php echo get_sub_field('setup_cost'); ?>"></div>

                <?php endwhile;

            endif;?>
        </div>
    </div>

<?php

} elseif (isset($_POST['position']) && isset($_POST['id'])){
    $position = $_POST['position'];
    $post_id = $_POST['id'];
    $product = get_post($post_id);

    echo "<option disabled selected value> -- Select -- </option>";

    if (have_rows('positions', $post_id)){
        while (have_rows('positions', $post_id)){
            the_row();

            $position_name = get_sub_field('position_name');

            if ($position == $position_name){
                if (have_rows('technologies', $post_id)){
                    while (have_rows('technologies', $post_id)){
                        the_row();

                        echo "<option id='".get_sub_field('technology_code')."' max-colors='".get_sub_field('max_colors')."'>";
                        echo get_sub_field('technology_name') . " (" . get_post_field('post_title', get_sub_field('technology_code')) . ")";
                        echo "</option>";
                    }
                }

            }
        }
    }
} else {
    echo "There is no Tech like this!";
}
