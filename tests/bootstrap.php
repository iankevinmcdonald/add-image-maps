<?php

// from https://engineering.hmn.md/guides/writing-code/writing-tests/

namespace wordpress-imagemaps;

// Get tests directory from environment.
$_tests_dir = getenv( 'WP_TESTS_DIR' );

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function manually_load_plugin() {
	require dirname( __DIR__ ) . '/plugin.php';
}
tests_add_filter( 'muplugins_loaded', __NAMESPACE__ . '\\manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';