<?php
/*
 * WP Filters Extras
 * Add 2 PHP functions for WP Filter API, it's allow to remove filter/action on hooks for specific cases with CLASS PHP.
 * 
 * Remove filters with method name only
 * remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 );
 *
 * Remove filters with class and method name
 * remove_filters_for_anonymous_class( $hook_name = '', $class_name ='', $method_name = '', $priority = 0 );
 *
 * @see https://github.com/herewithme/wp-filters-extras/
*/

/**
 * Allow to remove method for an hook when, it's a class method used and class don't have global for instanciation !
 */
function remove_filters_with_method_name( $hook_name = '', $method_name = '', $priority = 0 ) {
	global $wp_filter;

	// Take only filters on right hook name and priority
	if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) 
        || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) 
    ) {
		return false;
	}

	// Loop on filters registered
	foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {

		// Test if filter is an array ! (always for class/method)
		if ( isset( $filter_array['function'] ) 
            && is_array( $filter_array['function'] ) 
        ) {
            
			// Test if object is a class and method is equal to param !
			if ( is_object( $filter_array['function'][0] ) 
                && get_class( $filter_array['function'][0] ) 
                && $filter_array['function'][1] == $method_name 
            ) {
				// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)

				if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
					unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
				} else {
					unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
				}
			}
		}

	}

	return false;
}

/**
 * Allow to remove method for an hook when, it's a class method used and class don't have variable, but you know the class name :)
 */
function remove_filters_for_anonymous_class( $hook_name = '', $class_name = '', $method_name = '', $priority = 0 ) {
	global $wp_filter;

	// Take only filters on right hook name and priority
	if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
		return false;
	}

	// Loop on filters registered
	foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
		// Test if filter is an array ! (always for class/method)
		if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
			// Test if object is a class, class and method is equal to param !
			if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) == $class_name && $filter_array['function'][1] == $method_name ) {
				// Test for WordPress >= 4.7 WP_Hook class (https://make.wordpress.org/core/2016/09/08/wp_hook-next-generation-actions-and-filters/)
				if ( is_a( $wp_filter[ $hook_name ], 'WP_Hook' ) ) {
					unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $unique_id ] );
				} else {
					unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
				}
			}
		}

	}

	return false;
}




add_action('init', function () {

    // Remove the block editor for the product post type
    remove_filter( 
        'use_block_editor_for_post_type', 
        'cartify_enable_block_editor_product', 
        10 
    );

}, 0); // Ensure this runs early enough to remove the action
