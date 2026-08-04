<?php

class LVariant {
    public $ColorName;
    public $ColorCode;
    public $ColorImage;
}

class LQuantity {
    public $QuantityFrom;
    public $QuantityTo;
    public $Price;
}

function getCategories() {
    $args = array(
        'post_type' => 'product_category'
    );

    $categories = array();

    $new_query = new WP_Query($args);

    while($new_query -> have_posts() ) {
        $new_query -> the_post();

        $category_name = get_post_field('post_name');

        array_push($categories, $category_name);
    }

    return $categories;
}

function getCategoryTitles() {
    $args = array(
        'post_type' => 'product_category'
    );

    $categories = array();

    $new_query = new WP_Query($args);

    while($new_query -> have_posts() ) {
        $new_query -> the_post();

        $category_name = get_post_field('post_name');
        $category_title = get_the_title();

        $categories[$category_name] = $category_title;
    }

    return $categories;
}

function getSubcategories() {
    $subcategories = array();
    $categories = getCategories();

    for ($i = 0; $i < count($categories); $i++)
        $subcategories[$categories[$i]] = array();

    $args = array(
        'post_type' => 'product_subcategory'
    );

    $new_query = new WP_Query($args);

    while($new_query -> have_posts() ) {
        $new_query -> the_post();

        $subcategory_name = get_post_field('post_name');
        $subcategory_parent = get_post(get_field('parent')) -> post_name;

        array_push($subcategories[$subcategory_parent], $subcategory_name);

    }

    return $subcategories;
}

function getSubcategoryTitles() {
    $args = array(
        'post_type' => 'product_subcategory'
    );

    $subcategories = array();

    $new_query = new WP_Query($args);

    while($new_query -> have_posts() ) {
        $new_query -> the_post();

        $subcategory_name = get_post_field('post_name');
        $subcategory_title = get_the_title();

        $subcategories[$subcategory_name] = $subcategory_title;

    }

    return $subcategories;
}

function getProducts() {
    $categories = getCategories();
    $subcategories = getSubcategories();
    $products = array();

    for ($i = 0; $i < count($categories); $i++)
        for ($j = 0; $j < count($subcategories[$categories[$i]]); $j++)
            $products[$subcategories[$categories[$i]][$j]] = array(

            );

    $args = array(
        'post_type' => 'product'
    );

    $new_query = new WP_Query($args);

    while($new_query -> have_posts() ) {
        $new_query -> the_post();

        $product_name = get_post_field('post_name');
        $product_parent = get_post(get_field('parent')) -> post_name;
        $isActive = get_field('isActive');

        if ($isActive){
            array_push($products[$product_parent], $product_name);
        }
    }

    return $products;
}

function getProductTitles() {
    $args = array(
        'post_type' => 'product'
    );

    $products = array();

    $new_query = new WP_Query($args);

    while($new_query -> have_posts() ) {
        $new_query -> the_post();

        $product_name = get_post_field('post_name');
        $product_title = get_the_title();
        $isActive = get_field('isActive');

        if ($isActive){
            $products[$product_name] = $product_title;
        }
    }

    return $products;
}

function getIMGs() {
    $imgs = array();

    $args_categories = array(
        'post_type' => 'product_category'
    );

    $new_query_categories = new WP_Query($args_categories);

    while($new_query_categories -> have_posts() ) {
        $new_query_categories -> the_post();

        $category_name = get_post_field('post_name');
        $img = get_field('source_image');

        $imgs[$category_name] = $img;
    }

    $args_subcategories = array(
        'post_type' => 'product_subcategory'
    );

    $new_query_subcategories = new WP_Query($args_subcategories);

    while($new_query_subcategories -> have_posts() ) {
        $new_query_subcategories -> the_post();

        $subcategory_name = get_post_field('post_name');
        $img = get_field('source_image');

        $imgs[$subcategory_name] = $img;
    }

    $args_products = array(
        'post_type' => 'product'
    );

    $new_query_products = new WP_Query($args_products);

    while($new_query_products -> have_posts() ) {
        $new_query_products -> the_post();

        $product_name = get_post_field('post_name');
        $img = get_field('source_image');

        $imgs[$product_name] = $img;
    }

    return $imgs;
}

function getPermalinks() {
    $permalinks = array();

    $args_categories = array(
        'post_type' => 'product_category'
    );

    $new_query_categories = new WP_Query($args_categories);

    while($new_query_categories -> have_posts() ) {
        $new_query_categories -> the_post();

        $category_name = get_post_field('post_name');
        $permalink = get_permalink();

        $permalinks[$category_name] = $permalink;
    }

    $args_subcategories = array(
        'post_type' => 'product_subcategory'
    );

    $new_query_subcategories = new WP_Query($args_subcategories);

    while($new_query_subcategories -> have_posts() ) {
        $new_query_subcategories -> the_post();

        $subcategory_name = get_post_field('post_name');
        $permalink = get_permalink();

        $permalinks[$subcategory_name] = $permalink;
    }

    $args_products = array(
        'post_type' => 'product'
    );

    $new_query_products = new WP_Query($args_products);

    while($new_query_products -> have_posts() ) {
        $new_query_products -> the_post();

        $product_name = get_post_field('post_name');
        $permalink = get_permalink();

        $permalinks[$product_name] = $permalink;
    }

    return $permalinks;
}

function getProductQuantities($post_id) {
    $values = array();

    if (have_rows('quantities_and_prices', $post_id)) {
        while (have_rows('quantities_and_prices', $post_id)) {
            the_row();
            $lQuantity = new LQuantity();
            $lQuantity->QuantityFrom = get_sub_field('quantity_from');
            $lQuantity->QuantityTo = get_sub_field('quantity_to');
            $lQuantity->Price = get_sub_field('price');

            array_push($values, $lQuantity);
        }
    }

    return $values;
}

function getVariants($post_id) {
    $values = array();

    if (have_rows('variants', $post_id)) {
        while (have_rows('variants', $post_id)) {
            the_row();
            $lVariant = new LVariant();
            $lVariant->ColorName = get_sub_field('color_name');
            $lVariant->ColorCode = get_sub_field('color_code');
            $lVariant->ColorImage = get_sub_field('color_image');

            array_push($values, $lVariant);
        }
    }

    return $values;
}

function getSizes($post_id) {
    $values = array();

    if (have_rows('sizes', $post_id)) {
        while (have_rows('sizes', $post_id)) {
            the_row();
            array_push($values, get_sub_field('size'));
        }
    }

    return $values;
}

function getDeals() {
    $deals = array();
    $GLOBALS['deal_info'] = array();

    $args = array(
        'post_type' => 'product'
    );

    $query = new WP_Query($args);

    while($query -> have_posts() ) {
        $query -> the_post();

        $product_name = get_post_field('post_name');
        $parent = get_post(get_field('parent')) -> post_name;
        $has_deal = get_field('has_deal');
        $isActive = get_field('isActive');

        if ($isActive) {
            if ($has_deal == "Yes") {

                array_push($deals, array(
                    'product_name' => $product_name,
                    'product_parent' => $parent,
                    'product_price' => get_field('starting_price'),
                    'product_deal_price' => get_field('deal_price')
                ));
            }

            $subcategory = get_post(get_field('parent'));
            $subcategory_name = $subcategory -> post_name;

            $category = get_post(get_field('parent', $subcategory -> ID));
            $category_name = $category -> post_name;

            $product_info = array(
                "subcategory" => $subcategory_name,
                "category" => $category_name
            );

            $GLOBALS['deal_info'][$product_name] = array();

            $GLOBALS['deal_info'][$product_name] = $product_info;
        }
    }

    return $deals;
}

function getFAQ() {
    $GLOBALS['faq_questions'] = array();
    $GLOBALS['faq_answers'] = array();

    $args = array(
        'post_type' => 'faq_question'
    );

    $query = new WP_Query($args);

    while ($query -> have_posts()){
        $query -> the_post();

        array_push($GLOBALS['faq_questions'], get_field('question'));
        array_push($GLOBALS['faq_answers'], get_field('answer'));

    }
}