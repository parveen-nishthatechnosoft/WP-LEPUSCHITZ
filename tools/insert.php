<?php

require_once('../wp-load.php');

global $wpdb;

$results = $wpdb->get_results("
    SELECT posts.ID
    FROM wp_posts as posts
    LEFT OUTER JOIN ".$wpdb->prefix."postmeta as meta ON
            posts.ID = meta.post_id
        AND meta.meta_key = 'parent'
    WHERE
        posts.post_type = 'product'
    AND posts.post_status = 'publish'
    AND meta.meta_value IS NULL
    AND posts.post_title LIKE '%Sportflasche%'
");

echo "Count: " . count($results) . "<br>";
foreach ($results as $result) {
    $insert = $wpdb->query("
    INSERT INTO ".$wpdb->prefix."postmeta (post_id, meta_key, meta_value)
    VALUES ('" . $result->ID . "', 'parent', '5421')
    ");
}
