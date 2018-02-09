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
	 * Does nothing. (Unregistring options & removing metadata waits for uninstallation).
	 *
	 * @since    0.1.0
	 */
	public static function deactivate() {
		//nothing 
	}

}
