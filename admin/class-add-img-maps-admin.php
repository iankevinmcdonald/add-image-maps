<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       mcdonald.me.uk
 * @since      1.0.0
 *
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/admin
 * @author     Ian McDonald <ian@mcdonald.me.uk>
 */
class Add_Img_Maps_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->add_img_maps_options = get_option($this->plugin_name);

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * So this will be called by WP's own 'actions' callback feature.
		 * After being added as an action by Add_Img_Map_Loader->run
		 * After Add_Img_Maps->define_public_hooks registers it with Add_Img_Map_Loader
		 * (by calling Add_Img_Map_Loader->add_action)
		 *
		 * I'm sure there's a reason for doing this the long way around, but I don't know
		 * what it is - I just trust the boilerplate. Here's how the WPBP puts it:
		 *
			 * An instance of this class should be passed to the run() function
			 * defined in Add_Img_Maps_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Add_Img_Maps_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
		 */

		// There is no general admin stylesheet, nor one for the options page, but there is one for the Image Metadata Box
		// So check to see if it's an admin, and if the current screen is apt for this.
		if ( is_admin() ) {
			$screen = get_current_screen(); 
			$post = get_post();
			/* For our screen, base=post post_type=attachment */
			/* What's the right condition to load this only on a post.php page when editing an attachment image? */
			if ( $screen->{'base'} == 'post' && 
					$screen->{'post_type'} == 'attachment' &&
					strncasecmp( 'image', $post->{'post_mime_type'}, 5 ) == 0 // first five characters are 'image'
					) {

				wp_enqueue_style( 
					$this->plugin_name . '-metabox', 
					plugin_dir_url( __FILE__ ) . 'css/add-img-maps-metabox.css', 
					array(), 
					$this->version, 
					'all' 
				);
				wp_enqueue_script(
					$this->plugin_name . '-metabox', 
					plugin_dir_url( __FILE__ ) . 'js/add-img-maps-metabox.js', 
					array( 'jquery' ), 
					$this->version
				); 
				
				
			}
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Add_Img_Maps_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Add_Img_Maps_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		 
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/add-img-maps-admin.js', array( 'jquery' ), $this->version, false );
		// Image MetaDataBox JS must be enqueued. TODO

		// Accordion.JS is needed to run the Accordion - see https://core.trac.wordpress.org/ticket/23449
		wp_enqueue_script( $this->plugin_name, admin_url( 'js/accordion.js' ), array( ), $this->version, false );
		
		

	}
	
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
		
	public function add_plugin_admin_menu() {

    /*
     * Add a settings page for this plugin to the Settings menu.
     *
     * Alternatives at Administration Menus: http://codex.wordpress.org/Administration_Menus
     *
     */
    add_options_page( 'Add Image Maps options', 'Add Image Maps', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page')
    );
}

	 /**
	 * Add settings action link to the plugins page. Called when the plugin is activated.
	 *
	 * @since    1.0.0
	 */

	public function add_action_links( $links ) {
		/*
		*  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
		*/
	   $settings_link = array(
		'<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
	   );
	   return array_merge(  $settings_link, $links );

	}
	
	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */

	public function display_plugin_setup_page() {
		include_once( 'partials/add-img-maps-admin-display.php' );
	}
	
	/*
	 * The function that actually pushes the settings to WP.
	 * 
	 */
	
	public function options_update() {
		/* Saving in one chunk, so the second argument is the plugin name rather than a setting. */
		register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
	}
	
	/**
	 * Turn the input form into usable inputs
	 */
	
	public function validate($input) {
	// All checkboxes inputs        
		$valid = array();
		//error_log( '$input' . var_export($input, true));
		
		//Cleanup
		if ( isset($input['srcset']) && !empty($input['srcset']) ) {
			if ('off' == $input['srcset'] or 'run' == $input['srcset']) {
				$valid['srcset'] = $input['srcset'];
			}
		}
		$valid['header'] = (isset($input['header']) && !empty($input['header'])) ? 1 : 0;
		$valid['content'] = (isset($input['content']) && !empty($input['content'])) ? 1 : 0;
		
		//error_log(' $valid ' . var_export($valid, true));		
		return $valid;
 }

}
