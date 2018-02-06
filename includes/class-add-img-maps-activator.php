<?php

/**
 * Fired during plugin activation
 *
 * @link       mcdonald.me.uk
 * @since      1.0.0
 *
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/includes
 */

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

	// TODO check this hasn't picked up a value from elsewhere. Doubt it.
	// private $plugin_name='add_img_maps';

	/**
	 * Short Description: Set up default settings
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Set up the options
/*		error_log(' returned option BEFORE registration of ' . Add_Img_Maps::get_plugin_name() . ' :' . var_export(
			get_option(Add_Img_Maps::get_plugin_name() , Add_Img_Maps::get_plugin_name()) ,
			true // return instead of echo
			)
		);
 */
		register_setting(Add_Img_Maps::get_plugin_name() , Add_Img_Maps::get_plugin_name(), array(
			'default'=> array(
				'header' => 1,
				'content' => 1,
				'thumbnail' => 1,
				'srcset' => 'off',
				'test' => 'yes, the defaults were stored',
				)
		));
		// var_dump( get_option(Add_Img_Maps::get_plugin_name() , Add_Img_Maps::get_plugin_name()) 
/*		error_log(' returned option: ' . var_export(
			get_option(Add_Img_Maps::get_plugin_name() , Add_Img_Maps::get_plugin_name()) ,
			true // return instead of echo
			)
		);
 */
	}

}
