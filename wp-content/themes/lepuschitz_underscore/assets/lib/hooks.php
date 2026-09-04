<?php
function clear_productsincatoptions($aPostId, $aPost, $aUpdate) {
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'ProductsInCat_%'");
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'ProductsInSubCat_%'");
}

add_action('save_post_product', 'clear_productsincatoptions', 10, 3);

/**
 * Shows the importer-owned availability information on product edit screens.
 * Editors continue to use WordPress's normal Publish/Draft control for manual
 * decisions; only products carrying the automatic marker are re-published by
 * a later JUNG import.
 */
function lepuschitz_add_product_import_status_metabox() {
    add_meta_box(
        'lepuschitz-product-import-status',
        'Import status',
        'lepuschitz_render_product_import_status_metabox',
        'product',
        'side',
        'default'
    );
}
add_action('add_meta_boxes_product', 'lepuschitz_add_product_import_status_metabox');

function lepuschitz_render_product_import_status_metabox($post) {
    $lStatus = get_post_meta($post->ID, 'availability_status', true);
    $lLastSeenAt = get_post_meta($post->ID, 'import_last_seen_at', true);

    if ($lStatus === 'auto_drafted_missing_from_import') {
        $lStatusLabel = 'Automatically moved to draft because it was missing from the last import.';
    } elseif ($lStatus === 'active') {
        $lStatusLabel = 'Present in the latest import.';
    } else {
        $lStatusLabel = 'Manual / not managed by the JUNG import.';
    }

    echo '<p><strong>Status:</strong> ' . esc_html($lStatusLabel) . '</p>';
    echo '<p><strong>Last seen in import:</strong> ' . esc_html($lLastSeenAt ?: 'Never') . '</p>';
    echo '<p><strong>Last product update:</strong> ' . esc_html($post->post_modified ?: 'Never') . '</p>';
    echo '<p>WordPress status: <strong>' . esc_html($post->post_status) . '</strong></p>';
}
