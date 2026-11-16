<?php
/**
 * Unit tests: load only the pure pricing class, without WordPress.
 *
 * @package cpt-easy
 */

define( 'ABSPATH', __DIR__ . '/' ); // The plugin files exit without it.

$autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require $autoload;
}

require dirname( __DIR__ ) . '/includes/class-cpt-easy-pricing.php';
