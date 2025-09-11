<?php

/**
 * The class of objects representing actual HTML imagemaps.
 *
 * The overall data structure is described in 'data_formats.md', in the plugin root.
 * This class deals with creating them (from arrays and input fields), storing them,
 * and displaying them as HTML.
 *
 * The data structure can potentially store multiple per image (for different sizes)
 * but currently only one is implemented.
 * 
 * @author     Ian McDonald <ian@mcdonald.me.uk>
 * @since      1.0.0
 *
 * @package Add_Img_Maps/includes
 */
 
class Add_Img_Maps_Map {
	/**
	* @link https://html.spec.whatwg.org/multipage/image-maps.html
	* 
	*/

	/*
	 * Pseudo-constant reference arrays for use in functions.
	 *
 	 * @var	array	COORD_KEYS	The suffixes used for co-ordinates in the input names (x,y,r)
	 * @var	array	VALID_SHAPES	Valid values for HTML map:shape attributes.
	 * @var array	AREA_KEYS	The members of the array representing each area.
	 * @access protected
	 */
	
	static protected $COORD_KEYS = array( 'x', 'y', 'r' ); // PHP<5.7 rejects array constants
	static protected $VALID_SHAPES = array( "rect", "circle", "poly" );
	static protected $AREA_KEYS = array( "shape", "href", "alt", "coords");

	
	/**
	 * The only object setting is an array of the areas.
	 *
	 * @var	array	$areas	The areas of the map.
	 */
	protected $areas = array ();
	
	/**
	 * Create a new Map object. There are three possible input formats:
	 *
	 * **A list of array attributes**
	 * @param	string	$shape	A valid 'shape' element value (rect�poly�circle).
	 * @param	array	$coords	A list of co-ordinates
	 * @param	string	$alt	The alt attribute for the area
	 * @param	string	$href	The href attribute for the area
	 * @param	(*and repeat these 4 arguments for each map*)
	 *
	 * **An associative array reflecting the HTML element structure**
	 * @param	type	array	
	 * @type `[ [ shape=>string, alt=>string, href=>string, coords=>[ ] ], ... ]`
	 * 
	 * **An associative array that mirrors the input form**
	 * @param	type	array
	 * @type `[ shape=> ", alt=> ", href=> ", 0, 1 ,2...=>[ x=>int, y=>int, ?z=>int ] ]
	 * @see Add_Img_Maps_Metabox->save for how the input form becomes an array
	 *
	 * @return object	Add_Img_Maps_Map
	 */

	public function __construct () {
		
		/* Number of arguments should be even, or it takes a hashed object */
		// error_log('Add_Img_Maps_Map->__construct');
		$args = func_get_args();
		$num_args = count($args);
		
		if ( $num_args == 1 and is_array( $args[0]  ) ) {
		
			// If it's an associative array following the HTML map element, just copy it.			
			if ( array_key_exists( 'areas' , $args[0] )   
			) {
				if ( ! array_key_exists(  'shape', $args[0]['areas'][0] ) ) {
					throw new Exception ( 'Cannot detect "shape" key in first map: ' . print_r($args[0], true) );
				}
				$this->areas = $args[0]['areas'];
				$this->_validate(); // Throws error if bad.
				return $this;
				
			/**
			 * If an associative array representing input variables then we just need
			 * to check it and change the co-ordinates from the 'leaves' of an associate
			 * array to a list of numbers. 
			 *
			 * This is not guaranteed to maintain the ordering of areas, but this matters not.
			 */
			} elseif (	array_key_exists( 0, array_values($args[0] ) ) ) { 
			
				// error_log('Interpreting values as form input: ' . print_r( $args[0], true ) );
				$areasList = array();

				// For each area
				foreach( $args[0] as $inputArea ) {
				
					//error_log('$inputArea=' . print_r($inputArea, true));
					
					// Get all the co-ordinates (and typecast them to int, as a defence)					
					$coords = array();

					// Fetch circle co-ordinates (x, y, and r) (order matters)
					foreach ( self::$COORD_KEYS as $key ) {
						if (isset($inputArea[$key]) ) {
							array_push( $coords, (integer) $inputArea[$key] );
						}
					}

					// Co-ordinates for rectangles & polygons come in pairs,
					// and are the only things in the input element to have numeric keys.
					$pairs = array_filter( 
									array_keys( $inputArea ), 'is_numeric' );
					
					// They are meant to be stored as x/y arrays, and it should be a fatal error otherwise.
					if ( gettype( $pairs ) != 'array' ) {
						throw new Exception ('$pairs should be array but is ' . print_r( $pairs, true) . 
						' for input ' . print_r( $inputArea, true ) );
					}					
					
					// Ensure that the co-ordinate pairs are in the right order.
					sort( $pairs, SORT_NUMERIC );
					
//					error_log('$pairs = ' . print_r($pairs, true));
					
					foreach ($pairs as $pair) {
						// Expecting a form of array(x=>integer, y=>integer)
						if ( ! isset( $inputArea[$pair]['x']) ) {
							throw new Exception ("Missing x co-ordinate for point pair=$pair in inputArea '" . print_r( $inputArea, true ) );
						}
						
						// Add the co-ordinates
						array_push( $coords, (integer) $inputArea[$pair]['x'], (integer) $inputArea[$pair]['y'] );
					}
					
					// Validate the input area
					if ( ! in_array( 
						$inputArea['shape'],
						self::$VALID_SHAPES )
					) {
					// 	error_log( sprintf('Invalid shape %s discarded.', $inputArea['shape']));
						continue;
					}
					
					// Validate the co-ordinate count
					if ( ! $this->_coords_apt_for_shape( count($coords), $inputArea['shape'] ) ) {
						throw new Exception("Tried to create new image map with shape {$shape} but miscounted co-ords {$coords}");
					}					
					
					// Create the area
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
		
		// Other first arguments are unexpected and throw fatal errors.
		} else if ( gettype( $args[0] ) != 'string' and gettype( $args[0] ) != 'array' ) {
			throw new Exception( "num_args=$num_args args submitted, with unrecognised type first. Args=" . print_r($args, true ) );
		
		// If this is an argument list, it must have the right number of arguments.
		} else if ( $num_args == 0 or ( $num_args % 4) != 0) {
			throw new Exception('Tried to create new image map with arguments not in fours :' . 
				print_r($args, true) );

		// So precoess the list.
		} else {
					
			while ( count($args) ) {


				// The first part of each pair is the area shape 
				$shape = array_shift($args);

				// Check the shape
				if ( ! in_array( $shape, self::$VALID_SHAPES ) ) {
					throw new Exception("Tried to create new image map with unrecognised shape $shape");
				}
				
				// Second part is the co-ordinates 
				$coords = array_shift($args);
				if ( ! $this->_coords_apt_for_shape( count($coords), $shape ) ) {
					throw new Exception("Tried to create new image map with shape $shape but miscounted co-ords {$coords}");
				}
				
				// Final parts are the Alt text & link. Pre-escaped /
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

/**
 * Check that the list of co-ordinates is an apt length for the shape.
 *
 * @access protected
 * @var integer	$num_coords	The number of co-ordinates
 * @var	string	$shape		The shape of the area 
 * @return boolean
 */
	
	protected function _coords_apt_for_shape( $num_coords, $shape ) {
		return (
			'rect' == $shape && $num_coords != 4 or
			'circle' == $shape && $num_coords != 3 or
			'poly' == $shape && ($num_coords %2) != 0 or
			'poly' == $shape && $num_coords < 6
		) ? false : true;
	}
	
	/**
	 *
	 * Return the id attribute for an HTML map element.
	 *
	 * This is formed by a hyphen-separated list, broadest term first:
	 * The plugin tag, the image id, and (for when sizes are handled) its size.
	 *
	 * @access public
	 * @var	integer	$image_id	The wordpress id number for the image to which it will be attached.
	 * @var string	$image_size	The wordpress size of the image to which it is attached
	 * @return string	element id
	 
	 */

	public static function get_map_id ( $image_id, $image_size='full' ) {

		// Trim the word 'image', so that 'full image' becomes 'image'
		$image_size = 
//			sanitize_key(
				trim (
					preg_replace('/\W*image\W*/', '', $image_size) 
//				)
			);
	
		return sprintf("%s-%s-%s",
			Add_Img_Maps::name(),
			$image_id,
			$image_size );
	
	}
	
	/**
	 * Return the HTML of the image map
	 *
	 * The Map object only contains information about the map itself. But some
	 * extra information will be needed by JS to attach it to the right image,
	 * and this is passed as an associative array of attributes.
	 *
	 * @access public
	 * @var		array	$attrs	An associative array of attributes for the element to display.
	 * @type					An 'id' element is obligatory (and reduplicated as 'name')
	 * @return	string	HTML of the MAP element
	 */
	 
	public function get_html (  $attrs ) {
	
		// Confirm valid input; invalid should be a fatal error.
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
		
		/**
		 * Closure to turn an area into an element.
		 *
		 * @var		array	$this_area	An associative array representing a single area.
		 * @return	string	The Area element
		 */
		$areaElement = function( $this_area ) {
						
				return "<area shape='$this_area[shape]' " .
					'coords="' . 
					join(', ', $this_area['coords'] ) .
					'" ' .
					"href='" . esc_url( $this_area['href'] ) . "' " .
					"alt='$this_area[alt]' >"
				;
		};
	
		// Attributes were sanitized & escaped when entered.
		// Put the HTML element together.
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
	 * Returns the object as an associative array.
	 *
	 * This is necessary to turn its hidden fields into a JSON string.
	 * @return	array	An associative array with (currently) just an areas array.
	 */

	public function as_array () {
		$result = array();
		$result['areas'] = $this->areas;
		// If more members get added, must include them too.
		return $result;
	}

	/**
	 * Throw an error if the object values aren't as expected.
	 *
	 * Usually, if the object doesn't validate, it *should* throw a fatal
	 * error because that means something has gone seriously wrong elsewhere
	 * in the plugin and you should deactivate it.
	 *
	 * @access protected
	 * @return none
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
	 *
	 * This is for when you don't necessarily want to risk a fatal error.
	 *
	 * @return bool
	 * @access public
	 */
	public function is_valid() {
		try {
			$this->_validate();
			return true;
		} catch (Exception $e) {
			// error_log('Add_Img_Maps: failed validation' . $e);
			return false;
		}
	}

}
