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
	static protected $COORD_KEYS = array( 'x', 'y', 'r' ); // PHP<5.7 rejects array constants
	static protected $VALID_SHAPES = array( "rect", "circle", "poly" );
	static protected $AREA_KEYS = array( "shape", "href", "alt", "coords");
	
	
	/**
	 * new Add_Img_Maps_Map ( 'rect|circle|poly', array( *co-ordinates*), 'Alt text', link ... )
     * return - an image map object or false
	 */

	/* Or it takes an associative array made from input vars.
	 * 
	 * $arg1[$areaNum] = [ shape=>$shape, alt=>$alt, href=>$href, 0,1,2,3...=>[ $x, $y] ]
	 */
	 
	public function __construct () {
		
		/* Number of arguments should be even, or it takes a hashed object */
		error_log('Add_Img_Maps_Map->__construct');
		$args = func_get_args();
		$num_args = count($args);
		
		if ( $num_args == 1 and is_array( $args[0]  ) ) {
			// If it's an authentic object array, just set it.
			
			if ( array_key_exists( 'areas' , $args[0] )  /*and array_key_exists( 'shape', $args[0]['areas'][0] ) */ ) {
				if ( ! array_key_exists(  'shape', $args[0]['areas'][0] ) ) {
					throw new Exception ( 'Cannot detect "shape" key in first map: ' . print_r($args[0], true) );
				}
				$this->areas = $args[0]['areas'];
				$this->_validate(); // Throws error if bad.
				return $this;
				
	//			An associative array of form input vars, of form:
	// [size][$areaNum] = [ shape=>$shape, alt=>$alt, href=>$href, 0,1,2,3...=>[ $x, $y] ]
			} elseif (	array_key_exists( 0, array_values($args[0] ) ) ) { 
				error_log('Interpreting values as form input: ' . print_r( $args[0], true ) );

		// and	array_key_exists( 'shape', array_shift(array_values($args[0] ) ) ) 
				// Let's reshape into an object array by rearranging the coords.
				
				$areasList = array();
				
				// This might renumber the areas, but that's not desparately important
				foreach( $args[0] as $inputArea ) {
				
					error_log('$inputArea=' . print_r($inputArea, true));
					
					// Get all the co-ordinates (and typecast them to int, as a defence)					
					$coords = array();
					// Keys are x, y, and r in order.
					foreach ( self::$COORD_KEYS as $key ) {
						if (isset($inputArea[$key]) ) {
							array_push( $coords, (integer) $inputArea[$key] );
						}
					}
					// For every numbered member in order
					
					$pairs = array_filter( 
									array_keys( $inputArea ), 'is_numeric' );
					
					if ( gettype( $pairs ) != 'array' ) {
						throw new Exception ('$pairs should be array but is ' . print_r( $pairs, true) . 
						' for input ' . print_r( $inputArea, true ) );
					}					
					
					sort( $pairs, SORT_NUMERIC );
					
//					error_log('$pairs = ' . print_r($pairs, true));
					
					if ( gettype( $pairs) != 'array' ) {
						throw new Exception ('$pairs should be array but is ' . print_r( $pairs, true) . 
						' for input ' . print_r( $inputArea, true ) );
					}
					
					foreach ($pairs as $pair) {
						if ( ! isset( $inputArea[$pair]['x']) ) {
							throw new Exception ("Missing x co-ordinate for point pair=$pair in inputArea '" . print_r( $inputArea, true ) );
						}
						array_push( $coords, (integer) $inputArea[$pair]['x'], (integer) $inputArea[$pair]['y'] );
					}
					
					if ( ! in_array( 
						$inputArea['shape'],
						self::$VALID_SHAPES )
					) {
						error_log( sprintf('Invalid shape %s discarded.', $inputArea['shape']));
						continue;
					}
					
					if ( ! $this->_coords_apt_for_shape( count($coords), $inputArea['shape'] ) ) {
						throw new Exception("Tried to create new image map with shape $shape but miscounted co-ords ${coords}");
					}					
					
					// Many of the values are as input
					$this_area = array(
						'shape' => $inputArea['shape'],
						'coords' => $coords,
						'alt' => sanitize_text_field( $inputArea['alt'] ),
						'href' => esc_url( $inputArea['href'] ),
					);
											
					array_push( $areasList, $this_area);
				} // end Foreach $inputArea
				
				$this->areas = $areasList;
				$this->_validate();
				return $this;
				
			// Else not a valid array
			} else {
				throw new Exception('Tried to create new image map from invalid array ' . print_r($args, true) );
			}
		} else if ( gettype( $args[0] ) != 'string' and gettype( $args[0] ) != 'array' ) {
			throw new Exception( "num_args=$num_args args submitted, with unrecognised type first. Args=" . print_r($args, true ) );
			
		} else if ( $num_args == 0 or ( $num_args % 4) != 0) {
			throw new Exception('Tried to create new image map with arguments not in fours :' . 
				print_r($args, true) );
		} else {
					
			while ( count($args) ) {
				// The first part of each pair is the area type 
				$shape = array_shift($args);
				// This should be automated - no need to play nice.
				// $shape = strtolower($shape);
				if ($shape != 'rect' && $shape != 'circle' && $shape != 'poly') {
					throw new Exception("Tried to create new image map with unrecognised shape $shape");
				}
				
				// Second part is the co-ordinates 
				$coords = array_shift($args);
				if ( ! $this->_coords_apt_for_shape( count($coords), $shape ) ) {
					throw new Exception("Tried to create new image map with shape $shape but miscounted co-ords ${coords}");
				}
				
				// Final part is the Alt text. Pre-escaped /
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
		} //input as list if quartets
	}
	
	protected function _coords_apt_for_shape( $num_coords, $shape ) {
		return (
			'rect' == $shape && $num_coords != 4 or
			'circle' == $shape && $num_coords != 3 or
			'poly' == $shape && ($num_coords %2) != 0 or
			'poly' == $shape && $num_coords < 6
		) ? false : true;
	}
	
	static $IMAGE_SIZE_ABBREVIATIONS = array (
		'full' => 'full',
		'full image' => 'full',
		'thumbnail' => 'thmb',
		'medium' => 'med',
		'medium_large' => 'mlg',
		'large' => 'lrg',
	);
	


	public static function get_map_id ( $image_id, $image_size='full' ) {

		// Trim the word 'image', so that 'full image' becomes 'image'
		$image_size = 
//			sanitize_key(
				trim (
					preg_replace('/\W*image\W*/', '', $image_size) 
//				)
			);
	
		return sprintf("%s-%s-%s",
			Add_Img_Maps::PLUGIN_NAME,
			$image_id,
			$image_size );
	
	}
	
	
	public function get_html (  $attrs ) {
		// image_size defaults to 'full'
		
		if ( is_null ($attrs) or ! isset($attrs['id'] ) ) {
			throw new Exception ('Add_Img_Maps_Map->get_html without set attrs');
		}

		if ( ! count( $this->areas ) ) {
			throw new Exception ('get_html called on Map with no areas');
		}
		
		// Set name to ID
		if ( !isset( $attrs['name'] ) ) {
			$attrs['name'] = $attrs['id'];
		}
		
		$areaElement = function( $this_area ) {
				if ( ! isset( $this_area['shape'] ) or 
					! isset( $this_area['coords'] ) or
					! isset( $this_area['alt'] ) 
				) {
					throw new Exception('get_html called on Map with area not fully defined');
				}
		
				return "<area shape='$this_area[shape]' " .
					'coords="' . 
					join(', ', $this_area['coords'] ) .
					'" ' .
					"href='" . esc_url( $this_area['href'] ) . "' " .
					"alt='$this_area[alt]' >"
				;
		};
	
		// Attributes were sanitized & escaped when entered.
		
		
		
		return "<map " .
			implode( ' ', array_map(
				function($k, $v) { return $k . '="' . esc_attr( $v ) . '"'; },
				array_keys($attrs), $attrs ) 
			) .
			'>"' .
			join( ' ', array_map ( $areaElement , $this->areas) ) .
			'</map>'
		;
	}
			
			
	/**
	 *
	 *
	 *
	 * @since	1.0.0
	 */

	public function as_array () {
		$result = array();
		$result['areas'] = $this->areas;
		// If more members get added, must include them too.
		return $result;
	}

	/**
	 * Throw error if the object values aren't as expected.
	 */
	
	protected function _validate() {
		if ( ! count( $this->areas ) ) {
			throw new Exception ('Map has no areas');
		}
		foreach ( $this->areas as $area ) {
			// Check all keys are present
			if ( array_diff( self::$AREA_KEYS, array_keys( $area ) ) ) {
				throw new Exception ('Map area missing a key: ' . print_r( $area, true ) );
			}
			if (  ! in_array( $area['shape'], self::$VALID_SHAPES ) ) {
				throw new Exception ('Area has invalid shape: ' . print_r( $area, true ) );
			}
			if ( ! $this->_coords_apt_for_shape( count($area['coords']) , $area['shape'] )) {
				throw new Exception ('Area has wrong co-ordinate count: ' . print_r( $area, true ) );
			}
			foreach ( $area['coords'] as $xy) {
				if ( ! is_numeric( $xy ) ) {
					throw new Exception ('Non numeric co-ordinate ' . $xy . ' in:' . print_r( $area, true ) );
				}
			}
			// And assert is_numeric
			
		}
	
	}
	
	/**
	 * Return whether or not the object is valid.
	 */
	public function is_valid() {
		try {
			$this->_validate();
			return true;
		} catch (Exception $e) {
			error_log('Area failed to validate: ' . $e);
			return false;
		}
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
