<?php

// Commented this out because it found no tests & I'm not sure why.
// namespace Add_Img_Maps\Tests; 

require_once ( dirname( __DIR__ ) . '/includes/class-add-img-maps-map.php'); // kludge before this is all organised neatly
// use PHPUnit_Framework_TestCase;
// use WP_UnitTestCase;

class Add_Img_Maps_Map_Test extends PHPUnit_Framework_TestCase {
	
	public function test_create_map() {
		// This should work
		try {
			$map1 = new Add_Img_Maps_Map ( 
					// Normal working imagemap
					"rect" , array(10, 10, 20, 30), 'Test rectangle', 'http://t.co/',
					"circle" , array(20,20,8), 'Test circle', 'http://t.co/',
					"poly" , array(30,0,40,5,35,10), 'Test polygon', 'http://t.co/'
			);
		} catch (Exception $e) {
			$this->assertTrue(false,$e);
		}
		$this->assertTrue( count($map1) > 0, var_export( $map1, TRUE ) );
// Can't because attributes are protected
//		$this->assertObjectHasAttribute( 'alt', $map1);
	}

	public function test_map_as_HTML() {
		// Should pass; takes a post_id
		// I'll just let this throw an exception

		$map = new Add_Img_Maps_Map ( 
				// Normal working imagemap
				"rect" , array(15, 10, 25.3, 30), 'Test rectangle', 'http://t.co/',
				"circle" , array(25,18,9.2), 'Test circle', 'http://t.co/',
				"poly" , array(25,0,45,5,35,15), 'Test polygon', 'http://t.co/'
		); 
		$elementId = Add_Img_Maps_Map::get_map_id( 314 );
		$this->assertRegExp( '/314-full/' , $elementId );
		$attrs = array( 'id' => $elementId );
		$element = $map->get_HTML( $attrs );
		$this->assertRegExp( '/<map .+<\/map>/' , $element );
		$this->assertRegExp( '/314-full/' , $element );
	}
	
	// Check the error checking
	
	public function test_create_map_fail_rect_numbers() {
		$this->setExpectedException('Exception', "miscounted co-ords");
		$failMap = new Add_Img_Maps_Map  (
			"rect" , array(10, 10, 20, 30, 40), 'Test Rect', 'http://t.co/'
		);
		$this->assertEmpty( $failMap );
	}
	
	public function test_create_map_fail_circ_numbers() {
		$this->setExpectedException('Exception', "miscounted co-ords");
		$failMap = new Add_Img_Maps_Map  (
			"circle", array(30, 30), 'test rect' , 'http://t.co/'
		);
		$this->assertEmpty($failMap);
	}
		
	public function test_create_map_fail_poly_numbers() {
		$this->setExpectedException('Exception', "miscounted co-ords");
		$failMap = new Add_Img_Maps_Map  (
			"poly" , array(30, 30), 'test poly' , 'http://t.co/'
		);
		$this->assertEmpty($failMap);
	}		
		
	public function test_create_map_fail_shape() {
		$this->setExpectedException('Exception', "unrecognised shape");
		$failMap = new Add_Img_Maps_Map  (
			"circ", array(30, 30), 'test circ' , 'http://t.co/'
		);
		$this->assertEmpty($failMap);
	}	
	
	public function test_create_map_fail_args_count() {
		$this->setExpectedException('Exception', "fours");
		$failMap = new Add_Img_Maps_Map  (
			"circle", array(30, 30) 
		);
		$this->assertEmpty($failMap);
	}	
	

	
	public function test_create_map_from_form_input() {
		try {
			$formInput =  array(
						0 => array(
							'shape' => 'notashape', //should be rejected without throwing error
							'href' => 'http://example.org/',
							'alt' => 'Random text',
							0 => array(
								'x' => 35,
								'y' => 96,
							),
							1 => array(
								'x' => 283,
								'y' => 328,
							)
						),
						1 => array(
							'shape' => 'circle',
							'href' => 'http://circle.org/',
							'alt' => 'This is a circle',
							'x' => 100,
							'y' => 150,
							'r' => 36,
						),
						2 => array(
							'shape' => 'poly',
							'href' => 'http://xyz.co.uk/',
							'alt' => 'Make this a real test',
							0 => array(
								'x' => 205,
								'y' => 50,
							),
							1 => array(
								'x' => 250,
								'y' => 49,
							),
							2 => array(
								'x' => 240,
								'y' => 150,
							),
							3 => array(
								'x' => 203,
								'y' => 150,
							),
							4 => array(
								'x' => 150,
								'y' => 98,
							)
						)
				);
			$map1 = new Add_Img_Maps_Map ( $formInput );
			$id = Add_Img_Maps_Map::get_map_id ( 1 );
			$this->assertTrue( is_string( $id ) );
			$element = $map1->get_HTML( 
				array( 'id' => $id ) ) ;
			$this->assertRegExp( '/98/' , $element );
			$this->assertRegExp( '/real test/' , $element );
			$this->assertNotRegExp( '/328/', $element); // The fake shape should have been rejected
/*	*/	
		} catch (Exception $e) {
			$this->assertTrue(false,$e);
		}	
	}
	
	public function test_create_map_fail_from_form_input() {
		$this->setExpectedException('Exception', "Missing x co-ordinate");
		$formInput = array(
				'full' => array(
					0 => array(
						'shape'	=> 'notashape',
						'href' => 'http://example.org/',
						'alt' => 'Random text',
						0 => array(),
						1 => array()
					)
				)
		);
		$map1 = new Add_Img_Maps_Map ( $formInput );
		$this->assertEmpty( $map1);		
	}
	
	public function test_create_map_fail2_from_form_input() {
		$this->setExpectedException('Exception', "Missing x co-ordinate");	
		$formInput = array(
					1 => array(
						'shape' => 'circle',
						'href' => 'http://circle.org/',
						'alt' => 'This is a circle',
						'x' => 100,
						'y' => 150,
						'r' => 36,
					),
					2 => array(
						'shape' => 'poly',
						'href' => 'http://xyz.co.uk/',
						'alt' => 'Make this a real test',
						0 => array(
							'x' => 205,
							'y' => 50,
						),
						1 => array(
							'x' => 250,
							'y' => 49,
						),
						2 => array(
							'x' => 240,
							'y' => 150,
						),
						3 => array(
							'x' => 203,
							'y' => 150,
						),
						4 => array(
							'r' => 150,
							'y' => 98,
						)
					)
				);
		$map1 = new Add_Img_Maps_Map ( $formInput );
		$element = $map->get_HTML( 
			array( 'id' => $map::get_map_id( 1 ) ) 
		);
		$this->assertEmpty( $map1);
	}
	
/*	public function test_create_map_deliberate_fail() {
		$this->assertTrue( false );
	}
*/
	public function test_create_map_from_associative_array() {
		try {
			$map1 = new Add_Img_Maps_Map ( 
					// Normal working imagemap
					"rect" , array(10, 10, 20, 30), 'Test rectangle', 'http://t.co/',
					"circle" , array(20,20,8), 'Test circle', 'http://t.co/',
					"poly" , array(30,0,40,5,35,10), 'Test polygon', 'http://t.co/'
			);
			$serialised = serialize($map1);
			$this->assertTrue( unserialize($serialised) == $map1 );
			$map1_as_array = $map1->as_array();
			$json_map_code = json_encode( $map1_as_array); //Use associative array, not object
			$this->assertNotEquals( $json_map_code, '{}', print_r($map1, true) );
			$this->assertRegExp( '/Test rectangle/', $json_map_code );
			//JSON doesn't store the object type.
			$this->assertTrue( json_last_error() == JSON_ERROR_NONE, json_last_error_msg() );
			
//			$this->assertTrue( false, "INPUT " . print_r( $map1_as_array, true) . 'JSON=' . $json_map_code ); //for debug info

			$json_decoded = json_decode( $json_map_code, true ); // create array rather than StdClass object.

			$this->assertTrue( json_last_error() == JSON_ERROR_NONE, json_last_error_msg() );
//			$this->assertTrue( false, print_r($json_decoded, true) ); //for debug info
			$this->assertTrue( gettype( $json_decoded ) == 'array', "json_decoded $json_decoded not array. Val:" . print_r( $json_decoded, true) );
			$json_map = new Add_Img_Maps_Map ( $json_decoded );
			$this->assertTrue( $json_map == $map1 ); 
/* */		} catch (Exception $e) {
			$this->assertTrue(false,$e);
		}
		//$this->assertTrue( count($map1) > 0, "map1 $map1 is empty" );
	}
	
}