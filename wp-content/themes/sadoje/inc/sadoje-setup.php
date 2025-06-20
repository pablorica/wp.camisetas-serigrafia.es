<?php
/**
 * Basic setup for Sadoje template
 *
 * Adds the needed template hooks.
 * php version 7.4.1
 *
 * @package    Agni Framework
 * @subpackage Sadoje
 * @author     Pablo Rica <pablo@codigo.co.uk>
 * @license    MIT
 * @version    GIT: @1.0.0@
 * @link       link
 * @since      Sadoje 1.0
 */
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

function sdjthm_child_enqueue_scripts() {
    // Adding parent styles
    wp_enqueue_style(
        'agni-parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme()->get('Version'),
    );

    // Adding rtl support
    if( is_rtl() ){
	    wp_enqueue_style(
            'agni-parent-rtl-style',
            get_template_directory_uri() . '/rtl.css' ,
            array(),
            wp_get_theme()->get('Version'),
        );
    }

    // Adding child stylesheet
    wp_enqueue_style(
        'sadoje-style',
        get_stylesheet_directory_uri() . 'style.css',
        array(),
        wp_get_theme()->get('Version'),
    );

    wp_enqueue_style(
        'sadoje-style-general',
        get_stylesheet_directory_uri() . '/assets/css/style.css',
        array(),
        wp_get_theme()->get('Version'),
    );

    if (is_front_page()) {
        wp_enqueue_style(
            'sadoje-home-style',
            get_stylesheet_directory_uri() . '/assets/css/home.css',
            array(),
            wp_get_theme()->get('Version'),
        );
    }
    if (is_tax('product_cat')) {
        wp_enqueue_style(
            'sadoje-product-category-style',
            get_stylesheet_directory_uri() . '/assets/css/product_category.css',
            array(),
            wp_get_theme()->get('Version'),
        );
    }
    //https://developer.woocommerce.com/docs/theming/theme-development/conditional-tags
    if (is_product()) {
        wp_enqueue_style(
            'sadoje-product-style',
            get_stylesheet_directory_uri() . '/assets/css/product.css',
            array(),
            wp_get_theme()->get('Version'),
        );
    }
    if(is_cart() || is_checkout()) {
      wp_enqueue_style(
        'sadoje-cart-style',
        get_stylesheet_directory_uri() . '/assets/css/cart.css',
        array(),
        wp_get_theme()->get('Version'),
      );
    }
    


}
add_action( 'wp_enqueue_scripts', 'sdjthm_child_enqueue_scripts', 9 );

// Add custom editor styles
function sdjthm_gutenberg_editor_styles() {;

    wp_enqueue_style(
        'sadoje-editor-style',
        get_stylesheet_directory_uri() . '/assets/css/editor-style.css',
        array(),
        wp_get_theme()->get('Version'),
        'all'
    );
}
add_action( 'enqueue_block_editor_assets', 'sdjthm_gutenberg_editor_styles' );


// Register Sidebar
function sdjthm_register_sidebar() {
    register_sidebar(array(
        'name'          => __('WooCommerce Category Sidebar', 'custom-woocommerce-sidebar'),
        'id'            => 'woocommerce-category-sidebar',
        'description'   => __('Widgets in this area will be shown on WooCommerce category archive pages.', 'custom-woocommerce-sidebar'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'sdjthm_register_sidebar');

// Hook Sidebar into WooCommerce Category Pages
function sdjthm_display_sidebar() {
    if (is_tax('product_cat')) {
        echo '<aside class="woocommerce-category-sidebar">';
        dynamic_sidebar('woocommerce-category-sidebar');
        echo '</aside><div class="product_cat_grid">';
    }
}
add_action('woocommerce_before_main_content', 'sdjthm_display_sidebar', 15);

// Hook Sidebar into WooCommerce Category Pages
function sdjthm_close_main_section() {
    if (is_tax('product_cat')) {
        echo '</div>';
    }
}
add_action('woocommerce_after_main_content', 'sdjthm_close_main_section', 15);

// Override custom Yith colors with Product Attribute colors
function sdjthm_yith_save_filter_data($preset, $data) {
    if ($preset->filters) {
        $filters = $preset->get_raw_filters();
        foreach ($filters as $key => $filter) {
            if ($filter['taxonomy'] === 'pa_color' && $filter['filter_design'] === 'color') {
                foreach ($filter['terms'] as $k => $term) {
                    $meta_color = get_term_meta($term['term_id'], 'agni_variation_swatch_field', true);

                    if ($meta_color) {
                        $filter['terms'][$k]['color_1'] = $meta_color;
                    }
                }
            }

            $filters[$key] = $filter;
        }

        $preset->set_filters($filters);
    }
}
add_action('yith_wcan_before_data_object_save', 'sdjthm_yith_save_filter_data', 10, 2);

// Add the new column 'Color Code' in colors attribute taxonomy list
add_filter('manage_edit-pa_color_columns', 'add_color_code_column');
function add_color_code_column($columns) {
    $columns['color_code'] = __('Color Code', 'your-textdomain');
    return $columns;
}

// Display content in the new column 'Color Code'
add_filter('manage_pa_color_custom_column', 'show_color_code_column_content', 10, 3);
function show_color_code_column_content($content, $column_name, $term_id) {
    if ($column_name === 'color_code') {
        $color_code = get_term_meta($term_id, 'agni_variation_swatch_field', true); // Use your actual meta key
        if ($color_code) {
            // Show color preview + hex code
            $content = '<span style="display:inline-block;width:20px;height:20px;background:' . esc_attr($color_code) . ';border:1px solid #ccc;margin-right:5px;vertical-align:middle;"></span>';
            $content .= '<code>' . esc_html($color_code) . '</code>';
        } else {
            $content = '<em>No color</em>';
        }
    }
    return $content;
}


// Add tabs before the 'cdg_pricetable' 
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 11 );

add_action( 'woocommerce_share', 'woocommerce_output_product_data_tabs', 10 );