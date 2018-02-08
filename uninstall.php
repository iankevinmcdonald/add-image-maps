<?php

/**
 * Fired during uninstallation to delete the Image Maps from image metadata.
 * (The options were deregistered during deactivation.)
 *
 * It seems an unlikely object for a malicious attack, so I've not implemented the
 * boiilerplate recommendations for security. But here they are:
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

// Remove the post metadata

delete_post_meta_by_key( Add_Img_Maps::get_key() );