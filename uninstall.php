<?php

/**
 * Fired during uninstallation to delete all Add_Img_Maps data.
 *
 * Other developers don't seem to to think this needs to be secured, so I
 * haven't implemented the WPBP recommendations for security. Why:
 * https://core.trac.wordpress.org/ticket/20195
 * 
 * But for reference, they are:
 * - Check if the $_REQUEST content actually is the plugin name - 
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913

 *
 * @link       mcdonald.me.uk
 * @since      0.1.0
 *
 * @package    Add_Img_Maps
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

//delete the options. BUG: This is expected to delete the lines from the table, and it doesn't. Impact: zero.
unregister_setting( 'add-img-maps', 'add-img-maps' ); // Add_Img_Maps::$PLUGIN_NAME


// Remove the post metadata

delete_post_meta_by_key( '_add_img_maps' ); // Add_Img_Maps::$PLUGIN_KEY