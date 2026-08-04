<?php
function clear_productsincatoptions($aPostId, $aPost, $aUpdate) {
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'ProductsInCat_%'");
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'ProductsInSubCat_%'");
}

add_action('save_post_product', 'clear_productsincatoptions', 10, 3);
