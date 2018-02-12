<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/includes
 * @author     Ian McDonald <ian@mcdonald.me.uk>
 */
 
class Add_Img_Maps_Activator {

	/**
	 * Set up default option settings.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {	
	

		register_setting(Add_Img_Maps::get_plugin_name() , Add_Img_Maps::get_plugin_name(), array(
			'default'=> array(
				'header' => 1,
				'content' => 1,
				'thumbnail' => 1,
				'imagemapresizer' => 1,
				'srcset' => 'off',
				)
		));
	}

}