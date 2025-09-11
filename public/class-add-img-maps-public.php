<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Tracks the images (at least the ones that the WP database knows appear in
 * the page), outputs their image maps, and loads the Javascript to attach
 * the imagemaps to the images.
 *
 * @since		0.1.0
 * @package    Add_Img_Maps/public
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
	 * A tracker of the images on the current page.
	 *
	 * Structure:
	 * `[ from => [ content/header/thumbnail ], image => $image ]`
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $images   List of image post objects
	 */

	private $images = array();

    protected $add_img_maps_options;

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
	 * This does not enqueue any CSS, and the Javascript is enqueued within
	 * the footer hook, so there are no specific enqueue_* functions.
	 */
	 
	/**
	 * Options prototyped and rejected:
	 *
	 * 1. Handling the wp_get_attachment_image_attributes hook & adding 'usemap'
	 * 2. Handling the modify_header_image_data to add 'usemap' attribute
	 *
	 * The problem with both these low-level handlers is that it's up to the 
	 * template whether to use the functions that call them.
	 */
	 

	
	
	/**
	 * Find all images attached (db-wise) to the post, and add them to $images.
	 *
	 * The function is hooked into this_post, so called once on single pages
	 * and multiple times on lists.
	 *
	 * @acess public
	 * @var	 WP_Post	$post	The current post.
	 * @return	null
	 *
	 */
	public function list_images($post) {
		//error_log('Add_Img_Maps_Public->append_maps()');
		
		try {
		
			// 	 find the images (DB query) with IDs
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
				
				foreach ( $children as $child_image ) {
					if ( isset( $this->images[ $child_image ->ID ] )) {
						array_push( $this->images[ $child_image->ID ]['from'], 'content' );
					} else { //new image, add
						$this->images[ $child_image->ID ] = array (
							'image' => $child_image,
							'from' => array('content'),
						);
					}

					//error_log('Found image ID ' . $child_image->ID . ' "' . $child_image->post_title . '"' );
				
				}
			} // end if content option set

			// If we're including featured images, check the featured image too.
			if ( $this->add_img_maps_options['thumbnail'] ) {
				// Includes check for 'has feature support'
				$thumbnail_id = get_post_thumbnail_id();
				if ( $thumbnail_id ) {
					if ( isset( $this->images[ $thumbnail_id ] )) {
						array_push( $this->images[ $thumbnail_id ]['from'], 'thumbnail' );
					} else { //new image, add
						$this->images[ $thumbnail_id ] = array (
							'image' => get_post($thumbnail_id),
							'from' => array('thumbnail'),
						);
					}
					//error_log('Added featured image: ' . $thumbnail_id );
				}
				
			}

		} catch ( Exception $e) { //anything go wrong?
		
		//If it failed, record it and mention it to admins
			if ( current_user_can('activate_plugins') ) {
		?>		<!--Visible message displayed only to admins.-->
				<div><p class="notice notice-error is-dismissible"><?php
				_e(
					'Add_Img_Maps threw a fatal error whilst looking for images. See error log and page source.',
					Add_Img_Maps::name()
				);
		?>		</div></p><?php
			}
			echo '<!-- ' . esc_html($e) . '-->';
			error_log ("Add_Img_Maps execption caught on public page: $e" );
			
		} // end of try/catch 
	} // end 'the_content' hook

/**
 * Add the header to the lists & output image maps in footer.
 *
 * Also, optionally, sets up the Javascript that will attach maps to images.
 *
 * @access	public 
 * @var		none
 * @return	none
 */
		
public function append_maps() {	
	
	try {
			//if 'header' is turned on.
			/* Do this last because it passes not an image object, but a 'StdClass' object
			 * with properties: url, url_thumbnail, width, height, attachment_id 
			 */
			
			if ( $this->add_img_maps_options['header']  ) {
					
				$header_image = get_custom_header(); // returns stdClass object with limited field
				if ( $header_image ) {
					// error_log('Header image: ' . print_r( $header_image, true));
					$header_image->ID = $header_image->attachment_id ;
					// In unlikely situation of being both header & content
					if ( isset( $this->images[ $header_image->attachment_id ] )) {
						array_push( $this->images[ $header_image->attachment_id ]['from'], 'header' );
					} else { //new image, add
						$this->images[ $header_image->ID ] = array (
							'image' => $header_image,
							'from' => array('header'),
						);
					}
					//error_log('Head image ID=' . $header_image->attachment_id);
				}
			}
					
			//error_log( 'images: ' . print_r($this->images, true) );
			
			
			// Of course, this doesn't tell me which size of the page is included in the page. 
			// Rather than interrogate the page, I'll dump all the image maps and let Javascript sort it out.

			// Track all images with maps
			$images_with_maps = array();
			
			foreach( $this->images as $ID => $image ) {
				if ( ! is_object( $image['image'] ) ) {
					throw new Exception("Expected image to be object. Is not: " . 
						print_r ( $image['image'], true ));
				}
				// Does it have a map?
				$maps_metadata = get_post_meta( $image['image']->ID, Add_Img_Maps::get_key(), true );
				if ( $maps_metadata ) {
					$image['maps']=$maps_metadata;
					$images_with_maps[ $ID ] = $image;
				}
			}
			
			// If there's nothing to see, we move on.
			if ( ! $images_with_maps ) {
				return;
			}
			//error_log( 'images_with_maps: ' . print_r($images_with_maps, true) );
			
			// The srcset option is either 'run' [responsive images] or 'off'
			
			?><span id='addimgmaps-maps' data-option-srcset='<?php echo $this->add_img_maps_options['srcset']; ?>' ><?php
			
			foreach( $images_with_maps as $ID => $image ) {
				// for HANDLE_SIZES, will need to fetch image metadata, create
				// an array of sizes, and add a 'data-width' attribute to each
				// map to help find the size.
				foreach ( $image['maps'] as $size => $map ) {
					$elementId = Add_Img_Maps_Map::get_map_id( $ID, $size );
					if ( $image['image'] instanceof stdClass ) {
						/* Due to a bug in either WP or UniqueHeader, stdClass->URL
						 * always point to the default header & cannot be trusted
						 * if there's a unique header-per-post plugin.
						 */
						$image['image'] = get_post( $ID );
						$url = wp_get_attachment_url( $ID );
					// Else a WP_Post object
					// If the GUID ends in a file suffix, assume it is a URL* (see below)
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

			// Only enqueue the Javascript for this if it's needed.
			
			// and if the option is turned on, add the imageMapResizer script first.
			/**
			 * Image Map Resizer by David J Bradshaw et al, licensed under MIT Expat license
			 * See: https://github.com/davidjbradshaw/image-map-resizer/
			 */
			if ( $this->add_img_maps_options['imagemapresizer'] ) {
				wp_enqueue_script(
					'image-map-resizer',
					plugin_dir_url( __FILE__ ) . 'js/imageMapResizer.js',
					array('jquery'),
					$this->version,
					true
				);
			}

			//error_log( print_r( $this->add_img_maps_options , true ));
			
			wp_enqueue_script( 
				$this->plugin_name, 
				plugin_dir_url( __FILE__ ) . 'js/add-img-maps-public.js'
				, array( 'jquery' ), 
				$this->version, 
				true
			);
			
		} catch ( Exception $e) { //anything go wrong?
		
		//If it failed, record it and mention it to admins
			if ( current_user_can('activate_plugins') ) {
		?>		<!--Visible message displayed only to admins.-->
				<div><p class="notice notice-error is-dismissible"><?php
				_e(
					'Add_Img_Maps threw a fatal error. See error log and page source.',
					Add_Img_Maps::name()
				);
		?>		</div></p><?php
			}
			echo '<!-- ' . esc_html($e) . '-->';
			error_log ("Plugin Add_Img_Maps caught Exception: $e" );
			
		} // end of try/catch 
	}

}

/* *About that GUID kludge. The GUID is meant to store a unique ID for RSS
 * feed readers, and that's the function it performs for post/page objects. 
 * For images, it seems to be used for the URL. But I can't see from the
 * documentation if that's a feature or an accidental side-effect that I 
 * cannot rely on. So I've tried to use it to save time if present, and make
 * another DB call if I have to. 
 */