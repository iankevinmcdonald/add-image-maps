<?php

/**
 * Fired during plugin deactivation
 *
 * @link       mcdonald.me.uk
 * @since      1.0.0
 *
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/includes
 * @author     Ian McDonald <ian@mcdonald.me.uk>
 */
class Add_Img_Maps_Deactivator {

	/**
	 * Deactiveate plugin.
	 *
	 * Unregister the settings. (Doesn't remove metadata; that waits for uninstallation).
	 *
	 * @since    0.1.0
	 */
	public static function deactivate() {
		//delete the options. BUG: This is expected to delete the lines from the table, and it doesn't. Impact: zero.
		unregister_setting( Add_Img_Maps::get_plugin_name() , Add_Img_Maps::get_plugin_name() );
	}

}
