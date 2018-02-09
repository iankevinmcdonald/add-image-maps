<?php


/**
 * The overarching admin functionality.
 *
 * Defines the plugin name, version, options, and hooks.
 *
 * @package Add_Img_Maps/admin
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
				// Set up button labels and other internationalisable text
				wp_localize_script(
					$this->plugin_name . '-metabox',
					//str_replace('-', '',$this->plugin_name) . '_metabox_i18n', //The variable
					'addimgmaps_metabox_i18n', //The variable
					array(
						'please_upgrade' 		=>	__('Please upgrade your browser to one that supports HTML5 to use the editing aspects of this plugin', $this->plugin_name),
						
						'map_button_rm' 		=>	__('Delete whole map', $this->plugin_name),
						
						// translators: this is the image size (which is a WP term and not translated)
						'map_button_rm2ed' 		=>	__('Cancel deletion and re-open "%s" map',
														$this->plugin_name),
														
/* for _HANDLE_SIZES	'map_button_ed2close	=>	__('Pause editing', $this->plugin_name),
						'map_button_close2ed	=>	__('Re-open "%s" map for editing', $this->plugin_name),
 */
					
						'map_button_close'		=>	__('Cancel', $this->plugin_name),
						'map_button_cr'			=> 	__('Add new area', $this->plugin_name),

						'area_button_rm'		=>	__('Delete area', $this->plugin_name),
						'area_button_add'		=>	__('co-ord pair', $this->plugin_name),
						
						'area_placehold_href'	=>	__(
							'Please enter the web link that the clickable area links to.',
							$this->plugin_name),
						'area_placehold_alt'	=>	__(
							"Please enter alternative text for people who don't see the image.",
							$this->plugin_name),
							
						//translators: will be prefixed with unicode square/circle/star characters
						'shape_rect'			=>	__('Rectangle', $this->plugin_name),
						'shape_circle'			=>	__('Circle',	$this->plugin_name),
						'shape_poly'			=>	__('Polygon',	$this->plugin_name),
						/* a mathematical italic 'r' for radius, invisible in some monospace charsets */
						'shape_label_r'			=>	__('𝑟',			$this->plugin_name),
						/* the x and y co-ordinate labels are arrows, so pre-internationalised */
						/* Only used on title attribute; the label is an icon */
						'shape_coord_rm'		=>	__('Delete co-ordinates', $this->plugin_name),
						
						/* Vars. Translators: do not translate */
						'plugin_name'			=>	$this->plugin_name, 
						'plugin_id_name'		=>	Add_Img_Maps::attr_prefix(), 
						'ADD_IMG_MAPS_HANDLE_SIZES'
												=>	false,
						)
					); //Added internationalisation				
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
		 * The only admin-specific Javascript deals with the image map metadata
		 * box. And as the decision on whether to load it is exactly the same as
		 * the decision to load the metadata box CSS, the script is queued by
		 * enqueue_styles. 
		 * 
		 * This function remains because I expect I may be loading more JS with
		 * advances in functionality.
		 *
		 */

		 // Accordion.JS would be needed to run the Accordion - see https://core.trac.wordpress.org/ticket/23449
		// wp_enqueue_script( $this->plugin_name, admin_url( 'js/accordion.js' ), array( ), $this->version, false );
		
		

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
		$valid['thumbnail'] = (isset($input['thumbnail']) && !empty($input['thumbnail'])) ? 1 : 0;
		
		//error_log(' $valid ' . var_export($valid, true));		
		return $valid;
 }

}
