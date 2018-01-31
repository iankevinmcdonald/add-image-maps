<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              mcdonald.me.uk
 * @since             3.0.1
 * @package           Add_Img_Maps
 *
 * @wordpress-plugin
 * Plugin Name:       Add Image Maps
 * Plugin URI:        https://github.com/iankevinmcdonald/wordpress-imagemaps/
 * Description:       Enables editors to set HTML5 image maps on images. Displays them, inclding on header images.
 * Version:           0.1
 * Author:            Ian McDonald
 * Author URI:        mcdonald.me.uk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       add-img-maps
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-add-img-maps-activator.php
 */
function activate_add_img_maps() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-add-img-maps-activator.php';
	Add_Img_Maps_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-add-img-maps-deactivator.php
 */
function deactivate_add_img_maps() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-add-img-maps-deactivator.php';
	Add_Img_Maps_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_add_img_maps' );
register_deactivation_hook( __FILE__, 'deactivate_add_img_maps' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-add-img-maps.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_add_img_maps() {

	$plugin = new Add_Img_Maps();
	$plugin->run();

}
run_add_img_maps();
