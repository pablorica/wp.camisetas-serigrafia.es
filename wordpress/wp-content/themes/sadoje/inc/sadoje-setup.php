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
        get_stylesheet_directory_uri() . '/style.css',
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

    // Order line items: My Account (Pedidos > Ver) + thank-you page after checkout
    if ( is_account_page() || is_wc_endpoint_url( 'order-received' ) ) {
      wp_enqueue_style(
          'sadoje-order-items-style',
          get_stylesheet_directory_uri() . '/assets/css/order-items.css',
          array(),
          filemtime( get_stylesheet_directory() . '/assets/css/order-items.css' ),
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



/**
 * Add a new column 'Color Code' to the colors attribute taxonomy list
 * and make it sortable.
 */

/**
 * Adds a new column 'Color Code' to the colors attribute taxonomy list
 * @since 1.0.0
 * @param array $columns The existing columns in the taxonomy list.
 * @return array The modified columns with the new 'Color Code' column added.
 *
 * @package Sadoje
 *
*/
add_filter('manage_edit-pa_color_columns', 'add_color_code_column');
function add_color_code_column($columns) {
    $columns['color_code'] = __('Color Code', 'your-textdomain');
    return $columns;
}
/** Make the 'Color Code' column sortable
 * @since 1.0.0
 * @param array $sortable_columns The existing sortable columns.
 * @return array The modified sortable columns with the 'Color Code' column added.
 *
 * @package Sadoje
 *
*/
add_filter('manage_edit-pa_color_sortable_columns', 'make_color_code_column_sortable');
function make_color_code_column_sortable($sortable_columns) {
    $sortable_columns['color_code'] = 'color_code';
    return $sortable_columns;
}
/** Show the content of the 'Color Code' column in the colors attribute taxonomy list
 * @since 1.0.0
 * @param string $content The existing content of the column.
 * @param string $column_name The name of the column.
 * @param int $term_id The ID of the term being displayed.
 * @return string The modified content for the 'Color Code' column.
 *
 * @package Sadoje
 *
*/
add_filter('manage_pa_color_custom_column', 'show_color_code_column_content', 10, 3);
function show_color_code_column_content($content, $column_name, $term_id) {
    if ($column_name === 'color_code') {
        $color_code = get_term_meta($term_id, 'agni_variation_swatch_field', true); // Use your actual meta key
        if ($color_code) {
            // Show color preview + hex code
            $content = '<span style="display:inline-block;width:20px;height:20px;background:' . esc_attr($color_code) . ';border:1px solid #ccc;margin-right:5px;vertical-align:middle;"></span>';
            $content .= '<code>' . esc_html($color_code) . '</code>';
        } else {
            $content = '<em>No color code</em>';
        }
    }
    return $content;
}
/** Sort and filter the 'Color Code' column in the colors attribute taxonomy list
 *
 * @since 1.0.0
 * @param WP_Term_Query $query The term query object.
 *
 * @package Sadoje
 *
*/
add_action('pre_get_terms', 'sort_color_code_column');
function sort_color_code_column($query) {
    if (!is_admin()) return;

    //Check if get_current_screen() function exists

    if (!function_exists('get_current_screen')) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || $screen->taxonomy !== 'pa_color') return;

    // Ensure meta_query is initialized as an array
    if (!isset($query->query_vars['meta_query']) || !is_array($query->query_vars['meta_query'])) {
        $query->query_vars['meta_query'] = array();
    }

    // Sorting logic
    if (isset($_GET['orderby']) && $_GET['orderby'] === 'color_code') {
        $query->query_vars['meta_key'] = 'agni_variation_swatch_field';
        $query->query_vars['orderby'] = 'meta_value';

        $query->query_vars['meta_query'][] = array(
            'relation' => 'OR',
            array(
                'key' => 'agni_variation_swatch_field',
                'compare' => 'EXISTS',
            ),
            array(
                'key' => 'agni_variation_swatch_field',
                'compare' => 'NOT EXISTS',
            ),
        );
    }

    // Filtering logic
    if (isset($_GET['color_code_filter'])) {
        if ($_GET['color_code_filter'] === 'none') {
            $query->query_vars['meta_query'][] = array(
                'key' => 'agni_variation_swatch_field',
                'compare' => 'NOT EXISTS',
            );
        } elseif ($_GET['color_code_filter'] === 'has') {
            $query->query_vars['meta_query'][] = array(
                'key' => 'agni_variation_swatch_field',
                'compare' => 'EXISTS',
            );
        }
    }
}

/** Add a dropdown filter for the 'Color Code' column in the colors attribute taxonomy list
 *
 * @since 1.0.0
 * @param string $taxonomy The taxonomy being filtered.
 * @param string $post_type The post type being filtered.
 *
 * @package Sadoje
 *
*/
add_action('admin_footer-edit-tags.php', 'inject_color_code_filter_dropdown');
function inject_color_code_filter_dropdown() {
    if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] !== 'pa_color') {
        return;
    }

    $selected = isset($_GET['color_code_filter']) ? $_GET['color_code_filter'] : '';

    ?>
    <script>
    jQuery(function($) {
        const currentUrl = new URL(window.location.href);
        const selected = "<?php echo esc_js($selected); ?>";

        const filterHtml = `
            <select name="color_code_filter" id="color_code_filter">
                <option value="">All Color Codes</option>
                <option value="has" ${selected === 'has' ? 'selected' : ''}>Has Color Code</option>
                <option value="none" ${selected === 'none' ? 'selected' : ''}>No Color Code</option>
            </select>
        `;

        const $filter = $(filterHtml);

        $filter.on('change', function () {
            const value = $(this).val();
            const url = new URL(window.location.href);
            if (value) {
                url.searchParams.set('color_code_filter', value);
            } else {
                url.searchParams.delete('color_code_filter');
            }
            window.location.href = url.toString();
        });

        $('.tablenav.top .actions.bulkactions').append($filter);
    });
    </script>
    <?php
}



// Add tabs before the 'cdg_pricetable'
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 11 );

add_action( 'woocommerce_share', 'woocommerce_output_product_data_tabs', 10 );