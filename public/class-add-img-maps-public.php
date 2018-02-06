<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       mcdonald.me.uk
 * @since      1.0.0
 *
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/public
 * @author     Ian McDonald <ian@mcdonald.me.uk>
 */
class Add_Img_Maps_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->add_img_maps_options = get_option($this->plugin_name);
	}

	
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/add-img-maps-public.css', array('jquery'), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/add-img-maps-public.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * 
	 * The *_usemap functions do the following:
	 * - find the images (content_usemap only)
	 * - find the image IDs & sizes (content_usemap & perhaps header_usemap only)
	 * - discover whether those IDs have an image map.
	 * - add 'usemap' attribute
	 * - optionally, delete srcset attribute
	 * - add the image ID & size to the 'usemap'
	 */
	 
	/*
	 * Takes string $html, object $header, array $attr
	 */
	
	public function header_usemap( $html, $header, $attr ) { 
		error_log("In Add_Img_Maps_Public->header_usemap for header: " .
			print_r( $header, true) );
	}
	
	/**
	 * Add the usemap attribute to a featured image.
	 *
	 * @hook wp_get_attachment_image_attributes (& possibly modify_header_image_data)
	 
$attr
(array) Attributes for the image markup.

$attachment
(WP_Post) Image attachment post.

$size
(string|array) Requested size. Image size or array of width and height values (in that order). Default 'thumbnail'.	 
	 */
	
	public function featured_usemap( $attr, $attachment, $size ) {
		error_log("Called featured_usemap with attr=$attr attachment=" . 
			$attachment->ID . " size=$size");

		$maps_metadata = get_post_meta( $attachment->ID, '_add_img_maps', false); // Not single value
		
/*		error_log('maps_metadata: ' . print_r($maps_metadata, true) .
 *			'size: ' . print_r($size, true));
 */		
		// Abandon this is it's not set.
		if ( ! count( $maps_metadata ) ) {
			return $attr;
		}

		$map_size = null;
		
		if ( 'array' == gettype( $size) ) { // If responsive (I think)
			// Responsive image.
			error_log('add_usemap passed size as array (' .
				$size[0] . ' x ' . $size[1] . ') - responsive?');
			$map_size = 'full'; //Force it to use the 'full' image size map
			if ( $this->add_img_maps_options['srcset'] == 'off' ) {
				unset ( $attr['srcset'] ); // Responsive images off
			}
		}
				
		if ( isset( $maps_metadata[$size] ) ) { //if map
			if ( ! $maps_metadata[$size] instanceof Add_Img_Maps_Map ) {
				throw new Exception ( 
					'Post metadata not Add_Img_Maps_Map instance, instead ' .
					print_r( $maps_metadata[$size] , true )
				);
			}
			$attr['usemap'] = 'add-img-map-' . $attachment->ID . '-' . $size;
			// What about srcset?
		}

		// Return the changed attributes.
		return $attr;
	}
	
	
	public function content_usemap( $html ) {
		error_log("->content_usemap with html=" . substr( $html, 0, 32) );
		// Grab all img elements
		// Interrogate them.
		// If I can't find the image tag, so be it.
		return $html;
	}
	
	/**
	 * - find the images (DB query) with IDs
	 * - discover whether those IDs have an image map.
	 * - pass unique identifier (url?) to Javascript, possibly in div data.
	 * - add 'usemap' attribute
	 * - optionally, delete srcset attribute
	 * - add the image ID & size to the 'usemap'
	 *
	 */
	public function append_maps() {
		error_log('Add_Img_Maps_Public->append_maps()');
		global $post;
		
		// Which ones are we processing?
		// [ from => [ content /header/thumbnail ], image => $image ]
		
		$images = array ();
		
		if ( $this->add_img_maps_options['content'] ) {
			$children = 			
				get_children( // Start a new query for our registered images
					array(
						'post_parent' => $post->ID, // Get data from the current post
						'post_type' => 'attachment', // Only bring back attachments
						'post_mime_type' => 'image', // Only bring back attachments that are images
						'post_status' => 'all', // Attachments default to "inherit", rather than published. 
												// Use "inherit" or "all". 
						)
				)
			;
			$images = array_map ( 
				function( $v ) {
					return array(
						'from' => array('content'),
						'image' => $v,
					);
				},
				$children
			);

			foreach ( $images as $image ) {
				error_log('Found image ID ' . $image['image']->ID . ' "' . $image['image']->post_title . '"' );
			}
		
		}
		
		
		if ( $this->add_img_maps_options['thumbnail'] ) {
			// Includes check for 'has feature support'
			$thumbnail = get_the_post_thumbnail();
			if ( $thumbnail ) {
				if ( isset( $images[ $thumbnail->ID ] )) {
					array_push( $images[ $header_image->attachment_id ]['from'], 'thumbnail' );
				} else { //new image, add
					$images[ $thumbnail->ID ] = array (
						'image' => $thumbnail,
						'from' => 'thumbnail',
					);
				}
				error_log('Added featured image: ' . $thumbnail->ID );
			}
			
		}

		
		//if 'header' is turned on.
		/* Do this last because it passes not an image object, but a 'StdClass' object
		 * with properties: url, url_thumbnail, width, height, attachment_id 
		 */
		
		if ( $this->add_img_maps_options['header']  ) {
				
			$header_image = get_custom_header(); // returns stdClass object with limited field
			if ( $header_image ) {
				$header_image->ID = $header_image->attachment_id ;
				// In unlikely situation of being both header & content
				if ( isset( $images[ $header_image->attachment_id ] )) {
					array_push( $images[ $header_image->attachment_id ]['from'], 'header' );
				} else { //new image, add
					$images[ $header_image->ID ] = array (
						'image' => $header_image,
						'from' => 'header',
					);
				}
				error_log('Head image ID=' . $header_image->attachment_id);
			}
		}
				
		error_log( 'images: ' . print_r($images, true) );
		
		
		// Of course, this doesn't tell me which size of the page is included in the page. 
		// Rather than interrogate the page, I'll dump all the image maps and let Javascript sort it out.
		
		//Include debug comment if debug level turned on?
		$images_with_maps = array();
		
		foreach( $images as $ID => $image ) {
			// Does it have a map?
			$maps_metadata = get_post_meta( $image['image']->ID, '_add_img_maps', true );
			if ( $maps_metadata ) {
				$image['maps']=$maps_metadata;
				$images_with_maps[ $ID ] = $image;
			}
		}
			
		
		// If there's nothing to see, we move on.
		if ( ! $images_with_maps ) {
			return;
		}
		error_log( 'images_with_maps: ' . print_r($images_with_maps, true) );
		
		?><span id='addimgmaps-maps'><?php
		
		foreach( $images_with_maps as $ID => $image ) {
			// for HANDLE_SIZES, will need to fetch image metadata, create
			// an array of sizes, and add a 'data-width' attribute to each
			// map to help find the size.
			foreach ( $image['maps'] as $size => $map ) {
				$elementId = Add_Img_Maps_Map::get_map_id( $ID, $size );
				if ( $image['image'] instanceof stdClass ) {
					$url = $image['image']->url;
				// Else a WP_Post object
				// If the GUID ends in a file suffix, assume URL
				} else if ( preg_match( '/\.\w{3,5}$/', $image['image']->guid ) ) {
					$url = $image['image']->guid;
				// else just look it up
				} else {
					$url = wp_get_attachment_url( $image['image']->ID );
				}
				$attrs = array(
					'id' => $elementId,
					'data-from' => implode( ',' , $image['from'] ),
					'data-image-id' => $ID, 
					// Oddly, urls are stored on attachment WP_POst object GUIDs
					// (which stores, for post/page types, UIDs for feed readers)
					'data-url' => esc_url($url),
					'data-size' => $size,
					// Other options include WP_POST->slug & header->url_thumbnail
					// Will add a data-width attribute for HANDLE_SIZES
				);
				if ( ! $map instanceof Add_Img_Maps_Map ) {
					throw new Exception( 'Expected map object. Is ' . 
						print_r ( $map, true ) );
				}
				
				echo $map->get_html( $attrs );
			}
		}
		?></span><?php

		// Also, only enqueue the Javascript for this if it's needed.
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/add-img-maps-public.js', array( 'jquery' ), $this->version, true );
	}
	
	/**
	 * Push an [id/size] pair onto the mapId cached object.
	 */
	protected function DEPRECATED_push_map_id ( $id, $size ) {
		if ( ! is_numeric ($id) or ! is_string( $size) ) {
			throw new Exception ("->push_mapId invalid input $id $size");
		}
		$map_ids = wp_cache_get( $this->plugin_name, 'map_id');
		if (! $map_ids ) {
			$map_ids = array ( );
		}
		array_push( $map_ids, array( $id, $size) );
		wp_cache_replace( $this->plugin_name, $mapIds, 'map_id' );
	}
	
	/**
	 * Get the stored ids from the cache
	 */
	
	protected function DEPRECATED_get_map_ids () {
		return wp_cache_get( $this->plugin_name, 'map_id');
	}
}
