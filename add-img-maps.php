<?php

/**
 * Add Img Maps plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts it.
 *
 * (based on the boilerplate generator)
 *
 * @link              mcdonald.me.uk
 * @since             0.1.0
 * @package           Add_Img_Maps
 *
 * @wordpress-plugin
 * Plugin Name:       Add Image Maps
 * Plugin URI:        https://github.com/iankevinmcdonald/wordpress-imagemaps/
 * Description:       Enables editors to set HTML5 image maps on images. Displays them, inclding on header images.
 * Version:           1.0.0
 * Author:            Ian McDonald
 * Author URI:        mcdonald.me.uk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       add-img-maps
 * Domain Path:       /languages
 *
 * @throws			  Unexpected fatal exceptions only are caught and logged
 *					  to error_log (or console.log in the case of Javascript).
 *					  Thus the plugin *shouldn't* have any errors, but if it
 *			  		  does, they will be easier to fix before resubmission.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'ADD_IMG_MAPS_VERSION', '0.1.0' );

/**
 * Whether we can HANDLE_SIZES yet
 */
define( 'ADD_IMG_MAPS_HANDLE_SIZES', false);

/**
 * The code that runs during plugin activation.
 */
function activate_add_img_maps() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-add-img-maps-activator.php';
	Add_Img_Maps_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
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
 * @since    0.1.0
 */
function run_add_img_maps() {

	$plugin = new Add_Img_Maps();
	$plugin->run();

}
run_add_img_maps();