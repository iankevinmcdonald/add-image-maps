<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       mcdonald.me.uk
 * @since      1.0.0
 *
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/includes
 * @author     Ian McDonald <ian@mcdonald.me.uk>
 */
class Add_Img_Maps {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Add_Img_Maps_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   global
	 * @const    This is a constant, not a variable
	 * @var      string    PLUGIN_NAME    The string used to uniquely identify this plugin.
	 */
	const PLUGIN_NAME='add-img-maps';

	/**
	 * The unique identifier of this plugin for private metadata.
	 *
	 * @since    1.0.0
	 * @access   global
	 * @const    This is a constant, not a variable
	 * @var      string    PLUGIN_NAME    The string used to uniquely identify this plugin.
	 */
	const PLUGIN_KEY='_add_img_maps';	
	
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function __construct() {
		if ( defined( 'ADD_IMG_MAPS_VERSION' ) ) {
			$this->version = ADD_IMG_MAPS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Add_Img_Maps_Loader. Orchestrates the hooks of the plugin.
	 * - Add_Img_Maps_i18n. Defines internationalization functionality.
	 * - Add_Img_Maps_Admin. Defines all hooks for the admin area.
	 * - Add_Img_Maps_Public. Defines all hooks for the public side of the site.
	 * - Add_Img_Maps_Map. Defines the imagemap object.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-add-img-maps-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-add-img-maps-i18n.php';

		/**
		 * The Map object class, representing the Image Maps themselves
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-add-img-maps-map.php';		
		
		/**
		 * The class responsible for defining all actions that occur in the settings admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-add-img-maps-admin.php';

		/**
		 * The class responsible for creating and updating the per-attachment metadata
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-add-img-maps-metabox.php'; 
		
		
		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-add-img-maps-public.php';

		$this->loader = new Add_Img_Maps_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Add_Img_Maps_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Add_Img_Maps_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin. Called by constructor.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
	
		$plugin_admin = new Add_Img_Maps_Admin( $this->get_plugin_name(), $this->get_version() );

		// Enqueues metadata box CSS & JS.
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		
		// $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Pasted from https://scotch.io/tutorials/how-to-build-a-wordpress-plugin-part-1
		// Add menu item
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		// Save/Update our plugin options
		$this->loader->add_action('admin_init', $plugin_admin, 'options_update');
			// Add Settings link to the plugin
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->get_plugin_name() . '.php' );
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );
		
		// Add metabox 
		$this->loader->add_action('add_meta_boxes', 'Add_Img_Maps_Metabox', 'add');
			
		/**
		 * Uses pre_post_update hook to save post -
		 * because save_post only called on core (non-meta) change.
		 */
		$this->loader->add_action('pre_post_update', 'Add_Img_Maps_Metabox', 'save');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
	
		$plugin_public = new Add_Img_Maps_Public( $this->get_plugin_name(), $this->get_version() );

//		There is no plugin-specific CSS on the public-facing side, so this line is commented out.
//		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );

//		The Javascript is only enqueued when needed, so this line is commented out too.
//		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	/**
	 * Fires for every post as it loads to capture images registered to it
	 */
		$this->loader->add_action('the_post', $plugin_public, 'list_images');

	/**
	 * And then it fires on the footer to:
	 * - capture the header image
	 * - output the image maps
	 * - enqueue the Javascript, if needed
	 */
		
		$this->loader->add_action('wp_footer', $plugin_public, 'append_maps');
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return self::PLUGIN_NAME;
	}
	
    /**
	 * 'get_plugin_name', only shorter
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function name() {
		return self::PLUGIN_NAME;
	}

	/**
	 * The key used for private metadata (and also, for no good reason,
	 * using underscores instead of hyphens).
	 * @return  string	_the_plugin_metadata_key
	 */
	
	public function get_key() {
		return self::PLUGIN_KEY;
	}	
	
	/**
	 * The key used for element IDs and names.
	 * 
	 * This is without any word dividers ('-' or '_') because the compound
	 * element IDs and names are themselves a hyphen-separated list.
	 */
	 
	public function attr_prefix() {
		return str_replace( '-' , '', self::PLUGIN_NAME);
	}
	
	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Add_Img_Maps_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * There's some inconsistency between development versions with a
	 * version number of 0.x.y, and some functions that were given a default
	 * 'since' value of 1.0.0, even though they really arrived earlier. 
	 *
	 * @since     0.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}