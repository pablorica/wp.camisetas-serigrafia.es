<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function sdjthm_child_enqueue_scripts() {
    // Adding parent styles
    wp_enqueue_style( 
        'agni-parent-style', 
        get_template_directory_uri() . '/style.css', 
        array() 
    );

    // Adding rtl support
    if( is_rtl() ){
	    wp_enqueue_style( 'agni-parent-rtl-style', 
            get_template_directory_uri() . '/rtl.css' 
        );
    }

    // Adding child stylesheet
    wp_enqueue_style(
        'sadoje-style',
        get_stylesheet_directory_uri() . '/style.css'
    );

    if (is_front_page()) {
        wp_enqueue_style(
            'sadoje-home-style',
            get_stylesheet_directory_uri() . '/assets/css/home.css'
        );
    }
    if (is_tax('product_cat')) { 
        wp_enqueue_style(
            'sadoje-product-category-style',
            get_stylesheet_directory_uri() . '/assets/css/product_category.css'
        );
    }

    if (is_product()) {
        wp_enqueue_style(
            'sadoje-product-style',
            get_stylesheet_directory_uri() . '/assets/css/product.css'
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



?>