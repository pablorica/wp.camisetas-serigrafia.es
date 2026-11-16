<?php
/**
 * Integration tests: boot the real WordPress this plugin lives in (wp-content/plugins/cpt-easy).
 *
 * @package cpt-easy
 */

// wp-content/plugins/cpt-easy/tests → WordPress root. WP_LOAD overrides it (e.g. symlinked plugin).
$wp_load = getenv( 'WP_LOAD' ) ?: dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
	fwrite( STDERR, "wp-load.php not found at {$wp_load}\n" );
	exit( 1 );
}

$autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require $autoload;
}

// Safety net: refuse to run against production.
$env = getenv( 'APP_ENV' ) ?: 'production';
if ( 'production' === $env && ! getenv( 'CPT_EASY_TESTS_ALLOW_PRODUCTION' ) ) {
	fwrite( STDERR, "Refusing to run the integration tests with APP_ENV=production.\n" );
	exit( 1 );
}

define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
require $wp_load;

if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'CPT_Easy_Pricing' ) ) {
	fwrite( STDERR, "WooCommerce and CPT Easy must be active.\n" );
	exit( 1 );
}
