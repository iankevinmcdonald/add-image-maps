<?php

/**
 * The class of objects representing actual HTML imagemaps
 *
 *
 * @link       mcdonald.me.uk
 * @author     Ian McDonald <ian@mcdonald.me.uk>
 * @since      1.0.0
 *
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/includes
 */
 
class Add_Img_Maps_Map {
	/**
	* Represent a single HTML image map 
	* Following https://html.spec.whatwg.org/multipage/image-maps.html
	* 
	* A WP image may possess several image maps - a main one, and one per size
	*/

	/**
	 * The only object setting is an array of the areas.
	 *
	 */
	protected $areas = array ();
	
	/**
	 * new Add_Img_Maps_Map ( 'rect|circle|poly', array( *co-ordinates*), 'Alt text', link ... )
     * return - an image map object or false
	 */
	 
	public function __construct () {
		
		/* Number of arguments should be even */
		$args = func_get_args();
		$num_args = count($args);
		if ( $num_args == 0 or ( $num_args % 4) != 0) {
			throw new Exception('Tried to create new image map with arguments not in threes');
		};
				
		while ( count($args) ) {
			/* The first part of each pair is the area type */
			$shape = array_shift($args);
			// This should be automated - no need to play nice.
			// $shape = strtolower($shape);
			if ($shape != 'rect' && $shape != 'circle' && $shape != 'poly') {
				throw new Exception("Tried to create new image map with unrecognised shape $shape");
			}
			
			/* Second part is the co-ordinates */
			$coords = array_shift($args);
			if (
				'rect' == $shape && count($coords) != 4 or
				'circle' == $shape && count($coords) != 3 or
				'poly' == $shape && (count($coords) %2) != 0 or
				'poly' == $shape && (count($coords) < 6)
			) {
				throw new Exception("Tried to create new image map with shape $shape but miscounted co-ords ${coords}");
			}
			
			/* Final part is the Alt text. Pre-escaped */
			$alt = sanitize_text_field( array_shift($args));
			
			$href = esc_url(array_shift($args));
			
			array_push( $this->areas, array(
				'shape' => $shape,
				'coords' => $coords,
				'alt' => $alt,
				'href' => $href,
				)
			);
		}
		
		return $this;
	}
	
	static $IMAGE_SIZE_ABBREVIATIONS = array (
		'full' => 'full',
		'full image' => 'full',
		'thumbnail' => 'thmb',
		'medium' => 'med',
		'medium_large' => 'mlg',
		'large' => 'lrg',
	);
	
	public function get_html (  $image_id, $image_size=null) {
		// image_size defaults to 'full'
		if ( is_null ($image_size) ) {
			$image_size = 'full';
		} 
				
		// Just in case they created their own
		if ( ! isset( self::$IMAGE_SIZE_ABBREVIATIONS[ $image_size ] )) {
			// A hash will ensure that the key is short and apt, but make it a big unreadable
			// It would be better to write a custum routine to create an abbreviation
			echo "Image size $image_size not in IMAGE_SIZE_ABBREVIATIONS" ;
			self::$IMAGE_SIZE_ABBREVIATIONS[ $image_size ] = hash ( 'crc32b'  , $image_size );
		} 
		$image_size = self::$IMAGE_SIZE_ABBREVIATIONS[ $image_size ];
		
		$areaElement = function( $this_area ) {
				return "<area shape='$this_area[shape]' " .
					'coords="' . 
					join(', ', $this_area['coords'] ) .
					'" ' .
					"alt='$this_area[alt]' >"
				;
		};
			
		$name = $image_id . '-' . $image_size;
		return "<map id='$name' name='$name'>" .
			join( ' ', array_map ( $areaElement , $this->areas) ) .
			'</map>'
		;
	}
			
			
			
			

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __CORE_BOILERPLATE_construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'add-img-maps';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	private function _NOT_NEEDED_load_dependencies() {

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
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-add-img-maps-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-add-img-maps-public.php';

		$this->loader = new Add_Img_Maps_Loader();

	}



}
