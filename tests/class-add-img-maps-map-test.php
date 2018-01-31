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
					"rect" , array(10, 10, 20, 30), 'Test rectangle',
					"circle" , array(20,20,8), 'Test circle',
					"poly" , array(30,0,40,5,35,10), 'Test polygon'
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
				"rect" , array(15, 10, 25.3, 30), 'Test rectangle',
				"circle" , array(25,18,9.2), 'Test circle',
				"poly" , array(25,0,45,5,35,15), 'Test polygon'
		); 
		$element = $map->get_HTML( 314 );
		$this->assertRegExp( '/<map .+<\/map>/' , $element );
		$this->assertRegExp( '/314-full/' , $element );
	}
	
	// Check the error checking
	
	public function test_create_map_fail_rect_numbers() {
		$this->setExpectedException('Exception', "miscounted co-ords");
		$failMap = new Add_Img_Maps_Map  (
			"rect" , array(10, 10, 20, 30, 40), 'Test Rect'
		);
		$this->assertEmpty( $failMap );
	}
	
	public function test_create_map_fail_circ_numbers() {
		$this->setExpectedException('Exception', "miscounted co-ords");
		$failMap = new Add_Img_Maps_Map  (
			"circle", array(30, 30), 'test rect' 
		);
		$this->assertEmpty($failMap);
	}
		
	public function test_create_map_fail_poly_numbers() {
		$this->setExpectedException('Exception', "miscounted co-ords");
		$failMap = new Add_Img_Maps_Map  (
			"poly" , array(30, 30), 'test poly' 
		);
		$this->assertEmpty($failMap);
	}		
		
	public function test_create_map_fail_shape() {
		$this->setExpectedException('Exception', "unrecognised shape");
		$failMap = new Add_Img_Maps_Map  (
			"circ", array(30, 30), 'test circ' 
		);
		$this->assertEmpty($failMap);
	}	
	
	public function test_create_map_fail_args_count() {
		$this->setExpectedException('Exception', "threes");
		$failMap = new Add_Img_Maps_Map  (
			"circle", array(30, 30) 
		);
		$this->assertEmpty($failMap);
	}	
	
}