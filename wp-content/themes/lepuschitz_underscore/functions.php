<?php
/**
 * lepuschitz functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package lepuschitz
 */

$lHost = $_SERVER['HTTP_HOST'] ?? '';
$lHost = is_string($lHost) ? strtolower(trim($lHost)) : '';
if ($lHost !== 'www.lepuschitz-promotion.at') {
    // Internal dev server constants
    define('TARGET', 'DEV');
} else {
    // Production server constants
    define('TARGET', 'LIVE');
}

if (!defined('_S_VERSION')) {
    // Replace the version number of the theme on each release.
    define('_S_VERSION', '1.0.0');
}

if (!function_exists('lepuschitz_setup')) :
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function lepuschitz_setup() {
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on lepuschitz, use a find and replace
         * to change 'lepuschitz' to the name of your theme in all the template files.
         */
        load_theme_textdomain('lepuschitz', get_template_directory() . '/languages');

        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support('title-tag');

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support('post-thumbnails');

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus(array(
            'menu-1' => esc_html__('Primary', 'lepuschitz'),
        ));

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        ));

        // Set up the WordPress core custom background feature.
        add_theme_support('custom-background', apply_filters('lepuschitz_custom_background_args', array(
            'default-color' => 'ffffff',
            'default-image' => '',
        )));

        // Add theme support for selective refresh for widgets.
        add_theme_support('customize-selective-refresh-widgets');

        /**
         * Add support for core custom logo.
         *
         * @link https://codex.wordpress.org/Theme_Logo
         */
        add_theme_support('custom-logo', array(
            'height' => 250,
            'width' => 250,
            'flex-width' => true,
            'flex-height' => true,
        ));
    }
endif;
add_action('after_setup_theme', 'lepuschitz_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function lepuschitz_content_width() {
    // This variable is intended to be overruled from themes.
    // Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $GLOBALS['content_width'] = apply_filters('lepuschitz_content_width', 640);
}

add_action('after_setup_theme', 'lepuschitz_content_width', 0);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function lepuschitz_widgets_init() {
    register_sidebar(array(
        'name' => esc_html__('Sidebar', 'lepuschitz'),
        'id' => 'sidebar-1',
        'description' => esc_html__('Add widgets here.', 'lepuschitz'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
}

add_action('widgets_init', 'lepuschitz_widgets_init');

/**
 * Enqueue scripts and styles.
 */
function lepuschitz_scripts() {
    wp_enqueue_script('jquery');

    wp_enqueue_style('lepuschitz-style', get_template_directory_uri() . '/scss/style.css', array(), '1.2');
    wp_style_add_data('lepuschitz-style', 'rtl', 'replace');

    if (is_singular('catalog')) {
        wp_enqueue_style(
            'lepuschitz-catalog-tools',
            get_template_directory_uri() . '/css/catalog-tools.css',
            array('lepuschitz-style'),
            '1.1.0'
        );
    }

    //wp_enqueue_style('Font_Awesome', 'https://use.fontawesome.com/releases/v5.6.1/css/all.css');
    wp_enqueue_style(
        'Font_Awesome',
        get_template_directory_uri() . '/assets/css/all.css',
        array(),
        '6.1.2'
    );

    wp_enqueue_script('lepuschitz-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '1.1', true);
    wp_enqueue_script('main_nav', get_template_directory_uri() . '/js/main_nav.js', array(), '1.1', true);
    wp_enqueue_script('main_nav_mobi', get_template_directory_uri() . '/js/mobi/main_nav_mobi.js', array(), '1.1', true);

    wp_enqueue_script('accordion_tree', get_template_directory_uri() . '/js/accordion.js', array(), '1.1', true);
    wp_enqueue_script('accordion_tree-mobi', get_template_directory_uri() . '/js/mobi/accordion-mobi.js', array(), '1.1', true);
    wp_enqueue_script('price_calculator', get_template_directory_uri() . '/js/price_calculator.js', array(), '1.4', true);
    wp_enqueue_script('color_variants', get_template_directory_uri() . '/js/color_variants.js', array(), '1.1', true);
    wp_enqueue_script('catalog_interface.js', get_template_directory_uri() . '/js/catalog_interface.js', array(), '1.1', true);

    /*
    wp_enqueue_script('jsPDF', 'https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js', array(), '1.0.1');
    wp_enqueue_script('jsPDF-debug', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.3/jspdf.debug.js', array(), '1.0.1');
    wp_enqueue_script('html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.js', array(), '1.0.1');
    */
    wp_enqueue_script(
        'jsPDF',
        get_template_directory_uri() . '/assets/js/jquery.min.js',
        array(),
        '2.1.1'
    );
    wp_enqueue_script(
        'jsPDF-debug',
        get_template_directory_uri() . '/assets/js/jspdf.debug.js',
        array(),
        '2.1.1'
    );
    wp_enqueue_script(
        'html2canvas',
        get_template_directory_uri() . '/assets/js/html2canvas.js',
        array(),
        '0.4.1'
    );


    if (is_singular() && comments_open() && get_option('thread_comments'))
    {
        wp_enqueue_script('comment-reply');
    }
}

add_action('wp_enqueue_scripts', 'lepuschitz_scripts');

function lepuschitz_get_catalog_hidden_categories($catalog_id = null) {
    $hidden = array();
    $catalog_ids = array();

    if ($catalog_id) {
        $catalog_ids[] = (int)$catalog_id;
    } else {
        $catalog_posts = get_posts(array(
            'post_type' => 'catalog',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ));
        foreach ($catalog_posts as $catalog_post_id) {
            $catalog_ids[] = (int)$catalog_post_id;
        }
    }

    foreach ($catalog_ids as $catalog_post_id) {
        $values = get_field('hidden_product_categories', $catalog_post_id);
        if (empty($values)) {
            continue;
        }

        foreach ((array)$values as $value) {
            if ($value instanceof WP_Post) {
                $value = $value->ID;
            } elseif (is_array($value)) {
                if (!empty($value['ID'])) {
                    $value = $value['ID'];
                } elseif (!empty($value['value'])) {
                    $value = $value['value'];
                }
            } elseif (is_object($value)) {
                if (isset($value->ID)) {
                    $value = $value->ID;
                } elseif (isset($value->value)) {
                    $value = $value->value;
                } elseif (isset($value->post_id)) {
                    $value = $value->post_id;
                } else {
                    continue;
                }
            }

            if (is_numeric($value)) {
                $hidden[] = (int)$value;
                continue;
            }

            if (is_object($value)) {
                continue;
            }

            $normalized = strtolower(trim((string)$value));
            if ($normalized !== '') {
                $hidden[] = $normalized;
            }
        }
    }

    return array_values(array_unique($hidden));
}

function lepuschitz_is_hidden_catalog_category($category_name, $catalog_id = null) {
    if (empty($category_name)) {
        return false;
    }

    $matches = lepuschitz_get_catalog_hidden_categories($catalog_id);
    if (empty($matches)) {
        return false;
    }

    $normalized_name = strtolower(trim((string)$category_name));
    if ($normalized_name === '') {
        return false;
    }

    foreach ($matches as $match) {
        if ($match instanceof WP_Post) {
            $match = $match->ID;
        } elseif (is_object($match)) {
            if (isset($match->ID)) {
                $match = $match->ID;
            } elseif (isset($match->value)) {
                $match = $match->value;
            } elseif (isset($match->post_id)) {
                $match = $match->post_id;
            } else {
                continue;
            }
        }

        if (is_int($match) || is_numeric($match)) {
            $category_post = get_post((int)$match);
            if ($category_post && strtolower($category_post->post_title) === $normalized_name) {
                return true;
            }

            if ($category_post && strtolower($category_post->post_name) === $normalized_name) {
                return true;
            }

            continue;
        }

        if (is_object($match)) {
            continue;
        }

        if (strtolower((string)$match) === $normalized_name) {
            return true;
        }
    }

    return false;
}

function lepuschitz_get_hidden_category_ids() {
    $hidden = array();
    $catalog_hidden = lepuschitz_get_catalog_hidden_categories();

    foreach ($catalog_hidden as $value) {
        if (is_numeric($value)) {
            $hidden[] = (int)$value;
        }
    }

    if (empty($hidden)) {
        $category_names = array();
        foreach (lepuschitz_get_catalog_hidden_categories() as $value) {
            if (!is_numeric($value)) {
                $category_names[] = strtolower(trim((string)$value));
            }
        }

        if (!empty($category_names)) {
            $category_posts = get_posts(array(
                'post_type' => 'product_category',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'suppress_filters' => true,
            ));

            foreach ($category_posts as $category_id) {
                $category_title = strtolower(get_the_title($category_id));
                $category_slug = strtolower(get_post_field('post_name', $category_id));
                if (in_array($category_title, $category_names, true) || in_array($category_slug, $category_names, true)) {
                    $hidden[] = (int)$category_id;
                }
            }
        }
    }

    return array_values(array_unique(array_filter($hidden, 'is_numeric')));
}

function lepuschitz_exclude_hidden_categories_from_frontend($query) {
    static $is_running = false;

    if ($is_running) {
        return;
    }

    if (is_admin() || !$query->is_main_query() && $query->get('post_type') !== 'product_category' && $query->get('post_type') !== 'product_subcategory' && $query->get('post_type') !== 'product') {
        return;
    }

    $is_running = true;

    try {
        $post_type = $query->get('post_type');
        if (empty($post_type)) {
            return;
        }

        $hidden_category_ids = lepuschitz_get_hidden_category_ids();
        $hidden_subcategory_ids = array();

        if (!empty($hidden_category_ids)) {
            $subcategories = get_posts(array(
                'post_type' => 'product_subcategory',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'meta_key' => 'parent',
                'meta_value' => $hidden_category_ids,
                'meta_compare' => 'IN',
                'suppress_filters' => true,
            ));

            foreach ($subcategories as $subcategory_id) {
                $hidden_subcategory_ids[] = (int)$subcategory_id;
            }
        }

        if ($post_type === 'product_category') {
            if (!empty($hidden_category_ids)) {
                $query->set('post__not_in', array_merge((array)$query->get('post__not_in'), $hidden_category_ids));
            }
            return;
        }

        if ($post_type === 'product_subcategory') {
            if (!empty($hidden_category_ids)) {
                $meta_query = $query->get('meta_query');
                if (!is_array($meta_query)) {
                    $meta_query = array();
                }
                $meta_query[] = array(
                    'relation' => 'AND',
                    array(
                        'key' => 'parent',
                        'value' => $hidden_category_ids,
                        'compare' => 'NOT IN',
                    ),
                );
                $query->set('meta_query', $meta_query);
            }

            if (!empty($hidden_subcategory_ids)) {
                $query->set('post__not_in', array_merge((array)$query->get('post__not_in'), $hidden_subcategory_ids));
            }
            return;
        }

        if ($post_type === 'product') {
            if (!empty($hidden_subcategory_ids)) {
                $meta_query = $query->get('meta_query');
                if (!is_array($meta_query)) {
                    $meta_query = array();
                }
                $meta_query[] = array(
                    'relation' => 'AND',
                    array(
                        'key' => 'parent',
                        'value' => $hidden_subcategory_ids,
                        'compare' => 'NOT IN',
                    ),
                );
                $query->set('meta_query', $meta_query);
            }
        }
    } finally {
        $is_running = false;
    }
}

add_action('pre_get_posts', 'lepuschitz_exclude_hidden_categories_from_frontend');

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
    require get_template_directory() . '/inc/jetpack.php';
}

include 'custom_functionality/custom_functions.php';

/**
 * Deletes all images when the post has been deleted
 */
function delete_images($post_id) {
    include_once('assets/lib/delete.php');

    LDeleteImages::DeleteImage(get_post_field('source_image', $post_id));

    if (have_rows('variants', $post_id)) {
        while (have_rows('variants', $post_id)) {
            the_row();

            LDeleteImages::DeleteImage(get_sub_field('color_image'));
        }
    }
}

add_action('before_delete_post', 'delete_images');

function update_hash_sum($post_id) {
    update_post_meta($post_id, 'hash_sum', md5(time()));
}

add_action('acf/save_post', 'update_hash_sum');

function filter_groups_by_category_result($aText, $aPost, $aField, $aPostId) {
    // Get the parent category
    $lParentId = get_field('parent', $aPost->ID);
    $lParentTitle = get_the_title($lParentId);
    return $aText . " (Kategorie: " . $lParentTitle . ")";
}

add_filter('acf/fields/post_object/result/name=mapping_category_mapped_group_id', 'filter_groups_by_category_result', 10, 4);
add_filter('acf/fields/post_object/result/name=mapping_group_mapped_id', 'filter_groups_by_category_result', 10, 4);

// LP Options page
acf_add_options_page(array(
    'page_title' => 'Shop-Einstellungen',
    'menu_title' => 'Shop-Einstellungen',
    'menu_slug' => 'shop-settings',
    'capability' => 'edit_posts',
    'redirect' => false
));

function acf_load_mandatetype_choices($aField) {
    $lMandatorsInfo = LMandator::GetMandatorTypes();
    foreach ($lMandatorsInfo as $lMandatorKey => $lMandatorText) {
        $aField['choices'][$lMandatorKey] = $lMandatorText;
    }

    return $aField;
}

add_filter('acf/load_field/name=mandate-type', 'acf_load_mandatetype_choices');

function CustomColumnsForSubcategoryList($aColumns) {
    $aColumns['parent'] = 'Produkt-Kategorie';
    return $aColumns;
}

add_filter("manage_product_subcategory_posts_columns", "CustomColumnsForSubcategoryList");

function CustomColumnsForSubcategoryListAddColumns($aColumn, $aPostId) {
    switch ($aColumn) {
        case 'parent':
            $lParentId = get_field('parent', $aPostId);
            $lCategorieTitle = get_the_title($lParentId);
            echo $lCategorieTitle;
            break;
    }
}

add_action('manage_product_subcategory_posts_custom_column', 'CustomColumnsForSubcategoryListAddColumns', 10, 2);

/**
 * Add extra dropdowns to the List Tables
 *
 * @param required string $post_type    The Post Type that is being displayed
 */
add_action('restrict_manage_posts', 'add_extra_tablenav');
function add_extra_tablenav($post_type) {
    global $wpdb;

    /** Ensure this is the correct Post Type*/
    if ($post_type !== 'product_subcategory')
        return;

    /** Grab the results from the DB */
    $query = "SELECT post_title FROM wp_posts WHERE post_type = 'product_category' AND post_status = 'publish' ORDER BY post_title";
    $results = $wpdb->get_col($query);

    /** Ensure there are options to show */
    if (empty($results))
        return;

    // get selected option if there is one selected
    if (isset($_GET['product_subcategory-name']) && $_GET['product_subcategory-name'] != '') {
        $selectedName = $_GET['product_subcategory-name'];
    } else {
        $selectedName = -1;
    }

    /** Grab all of the options that should be shown */
    $options[] = sprintf('<option value="-1">%1$s</option>', 'Alle Produktkategorien');
    foreach ($results as $result) :
        if ($result == $selectedName) {
            $options[] = sprintf('<option value="%1$s" selected>%2$s</option>', esc_attr($result), $result);
        } else {
            $options[] = sprintf('<option value="%1$s">%2$s</option>', esc_attr($result), $result);
        }
    endforeach;

    /** Output the dropdown menu */
    echo '<select class="" id="product_subcategory-name" name="product_subcategory-name">';
    echo join("\n", $options);
    echo '</select>';
}

add_filter('parse_query', 'prefix_parse_filter');
function prefix_parse_filter($query) {
    global $pagenow;
    global $wpdb;

    $current_page = isset($_GET['post_type']) ? $_GET['post_type'] : '';

    if (is_admin() && 'product_subcategory' == $current_page && 'edit.php' == $pagenow && isset($_GET['product_subcategory-name']) && $_GET['product_subcategory-name'] != '') {
        // Get the name of the
        $competition_name = $_GET['product_subcategory-name'];
        // Get the ID base on the category name
        $lQuery = "SELECT ID FROM wp_posts WHERE post_type = 'product_category' AND post_TITLE = '$competition_name'";
        $lCategoryId = $wpdb->get_var($lQuery);

        $query->query_vars['meta_key'] = 'parent';
        $query->query_vars['meta_value'] = $lCategoryId;
        $query->query_vars['meta_compare'] = '=';
    }
}

if (function_exists('acf_add_local_field_group')) {
    add_action('acf/init', 'lepuschitz_register_catalog_hidden_categories_field_group');
    function lepuschitz_register_catalog_hidden_categories_field_group() {
        acf_add_local_field_group(array(
            'key' => 'group_catalog_hidden_categories',
            'title' => 'Catalog Settings',
            'fields' => array(
                array(
                    'key' => 'field_catalog_hidden_product_categories',
                    'label' => 'Hidden product categories',
                    'name' => 'hidden_product_categories',
                    'type' => 'relationship',
                    'instructions' => 'Select product categories that should be hidden from the frontend for this catalog.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'post_type' => array(
                        0 => 'product_category',
                    ),
                    'taxonomy' => '',
                    'filters' => array(
                        0 => 'search',
                    ),
                    'elements' => '',
                    'min' => '',
                    'max' => '',
                    'return_format' => 'object',
                    'ui' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'catalog',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
        ));
    }
}

include_once(get_stylesheet_directory() . '/assets/lib/catalog.php');
include_once(get_stylesheet_directory() . '/assets/lib/images.php');
include_once(get_stylesheet_directory() . '/assets/lib/hooks.php');
