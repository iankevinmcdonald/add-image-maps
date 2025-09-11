<?php

/**
 * PHPUnit Bootstrap file, based on
 * * sample plugin
 * * https://engineering.hmn.md/guides/writing-code/writing-tests/
 * * https://codesymphony.co/writing-wordpress-plugin-unit-tests/
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Get tests directory from environment, but provide a default if needed.
// For the sake of consistency, remove a trailing white slash
$_tests_dir = rtrim( getenv( 'WP_TESTS_DIR' ), '/' );

if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}
if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?";
    exit( 1 );
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

tests_add_filter( 'muplugins_loaded', '\\manually_load_plugin' );

/**
 * Sets up the WordPress test environment.
 *
 * We've got our action set up, so we can load this now,
 * and viola, the tests begin.
 */
require $_tests_dir . '/includes/bootstrap.php';