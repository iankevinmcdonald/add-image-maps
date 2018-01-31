<?php

// Based on https://engineering.hmn.md/guides/writing-code/writing-tests/
// and https://codesymphony.co/writing-wordpress-plugin-unit-tests/

namespace Add_Img_Maps\Tests;

// Get tests directory from environment, but provide a default if needed.
// For the sake of consistency, remove a trailing white slash
$_tests_dir = rtrim( getenv( 'WP_TESTS_DIR' ), '/' );

if ( ! $_tests_dir ) {
	$_tests_dir = '/home/ian/wordpress-dev/trunk/tests/phpunit';
//	$_tests_dir = '/usr/share/wordpress/wp-content/plugins/add-img-maps/tests';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 * Assumes in current environment
 */
function manually_load_plugin() {
	/* Changed from '_pluginName_/plugins.php' to '_pluginName_/_pluginName_.php' - 
	 * which means this isn't generalisable */
	require dirname( __DIR__ ) . '/add-img-maps.php';
}
tests_add_filter( 'muplugins_loaded', __NAMESPACE__ . '\\manually_load_plugin' );

/**
 * Sets up the WordPress test environment.
 *
 * We've got our action set up, so we can load this now,
 * and viola, the tests begin.
 */
require $_tests_dir . '/includes/bootstrap.php';