<?php
/**
 * Plugin Name: Sadoje Template
 * Plugin URI:  https://codigo.co.uk
 * Description: Personalización de la plantilla Galería para adaptarse a los requisitos de Sadoje.
 * - Añade una barra lateral de widgets en las páginas de categoría WooCommerce.
 * Version:     1.0
 * Author:      Pablo Rica
 * Author URI:  https://codigo.co.uk/team/pablo-rica
 * License:     GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Register Sidebar
function sdj_register_sidebar() {
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
add_action('widgets_init', 'sdj_register_sidebar');

// Hook Sidebar into WooCommerce Category Pages
function sdj_display_sidebar() {
    if (is_tax('product_cat')) {
        echo '<aside class="woocommerce-category-sidebar">';
        dynamic_sidebar('woocommerce-category-sidebar');
        echo '</aside><div class="product_cat_grid">';
    }
}
add_action('woocommerce_before_main_content', 'sdj_display_sidebar', 15);

// Hook Sidebar into WooCommerce Category Pages
function sdj_close_main_section() {
    if (is_tax('product_cat')) {
        echo '</div>';
    }
}
add_action('woocommerce_after_main_content', 'sdj_close_main_section', 15);

// Enqueue Plugin CSS
function cws_enqueue_styles() {
    if (is_tax('product_cat')) { // Load CSS only on WooCommerce category pages
        wp_enqueue_style(
            'cws-woocommerce-sidebar-style',
            plugin_dir_url(__FILE__) . 'assets/css/style.css',
            array(),
            '1.0',
            'all'
        );
    }
}
add_action('wp_enqueue_scripts', 'cws_enqueue_styles');
