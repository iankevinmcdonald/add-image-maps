<?php

namespace wp_imgmaps;

use ImgMaps;
use ImgMaps_Map;
use PHPUnit_Framework_TestCase;

class ImgMaps_Map_Test extends PHPUnit_Framework_TestCase {
	// Nothing here yet!
	
	$map1 = new ImgMaps_Map ( 
			array(
				// Normal working imagemap
				"rect" => array(10, 10, 20, 30),
				"circle" => array(20,20,8),
				"poly" => array(30,0,40,5,35,10),
			)
		);
		
	public function test_create_map() {
		// Fails with wrong number of co-ords
		$this->assertIsDefined true, $map1 );
	}

	public function test_map_as_HTML() {
		// Fails with wrong number of co-ords
		$this->assertRexExp( '/<map >.+<\/map>' , $map->get_HTML );
	}

	
	// Check the error checking
	
	public function test_create_map_fail_rect_numbers() {
		$failMap = new ImgMaps_Map (
			array( "rect" => array(10, 10, 20, 30, 40), )
		);
		this->assertFalse($failMap);
	}
	
	public function test_create_map_fail_circ_numbers() {
		$failMap = new ImgMaps_Map (
			array( "circle" => array(30, 30) )
		);
		this->assertFalse($failMap);
	}
		
	public function test_create_map_fail_poly_numbers() {
		$failMap = new ImgMaps_Map (
			array( "poly" => array(30, 30) )
		);
		this->assertFalse ($failMap);
	}		
		
	public function test_create_map_fail_numbers() {
		$failMap = new ImgMaps_Map (
			array( "poly" => array(30, 30) )
		);
		this->assertFalse ($failMap);
	}	
	
}

