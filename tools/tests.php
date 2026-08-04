<?php
require_once('../wp-load.php');

global $wpdb;

$results = $wpdb->get_results("
    SELECT posts.ID
    FROM ".$wpdb->prefix."posts as posts
    LEFT OUTER JOIN ".$wpdb->prefix."postmeta as meta ON
            posts.ID = meta.post_id
        AND meta.meta_key = 'code'
    WHERE
        posts.post_type = 'technology'
    AND posts.post_status = 'publish'
    AND meta.meta_value = 'P0'
 ");

print_r($results[0]);