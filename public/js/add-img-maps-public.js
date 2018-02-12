/**
 * @file	Attaches image maps to images.
 * @author	Ian McDonald
 * @package Add_Img_Maps/admin
 * @since 	0.1.0
 */

/**
 * @external	Uses imageMapResizer.js ver 1.0.3 to resize maps with images. 
 * @link		https://github.com/davidjbradshaw/image-map-resizer
 */
 
/**
 * @function	Self-calling anonymous function, creates closure.
 * @param	Object		$		jQuery (to be able to use $ within closure)
 */
 
(function( $ ) {
	'use strict';

/**
 *	@function	Anonymous 
 *	@event		Waits until window.load event.
 */
 
	$( window ).load( 
			function() {
				attach_maps() ; 
			 
			/* If image_map_resizer is turned on, use it.
			 * (If it's turned on in Wordpress, then its JS
			 * will have been loaded first, and it will have
			 * added itself to the jQuery object.)
			 */
				if( jQuery.fn.hasOwnProperty('imageMapResize') ) {
					$('map').imageMapResize();
				}
			}
		)
	;
	
/**
 *	@member		string		option_srcset	Whether we should *run* or turn
 *											*off* the responsiveness of images
 *											that have maps.
 */
	var option_srcset;

/**
 * Attach all the maps to their images.
 * 
 * Also, as housekeeping, remmoves the maps whose images cannot be found, and
 * optionally removes the responsiveness of images with maps.
 * 
 * @acesss 	protected (via closure)
 * @param	none
 * @return	none
 */
 
	function attach_maps () {
	
		// Maps
		var maps = $('span#addimgmaps-maps map');
		option_srcset = $('span#addimgmaps-maps').data('option-srcset');
		
		//console.log( maps );
		
		maps.each( attach_to_image );
	}

/**
 * Attach a map to its image.
 *
 * @param	int		index	Index of map within array of maps (not used)		
 * @param	DOM		map		Map element
 */
	function attach_to_image( index, map ) {

		var image, image_id;
		
		/** 
		 *	The most common ways of labelling images are:
		 *	* "data-attachment-id" attributes and 
		 * 	* classes named "wp-image-nnn". 
		 */
		image_id = map.getAttribute('data-image-id');
		
		image = 
			$('img.wp-image-' + 
				image_id + 
				',img[data-attachmend-id="' + 
				image_id + 
				'"]' 
		);
		
		/**
		 * If that fails, we're trying to guess it from the URL.
		 */
		if ( ! image.length ) {
			image = $('img[src="' + map.getAttribute('data-url') + '"]');
		}
		
		//console.log('Map ID ' + image_id + '; image found: ' + image);
		
		/**
		 * And if that fails, remove it from the image. This is because maps
		 * without images can confuse the imageMapResize script.
		 */
		if ( ! image.length ) { //
			$( map ).append('<-- Add_Img_Maps: Failed to find image ID ' + 
				image_id + '; map deleted -->');
			$( map ).remove();
		}
		
		image.attr('usemap', map.name );
		image.unwrap('a'); // Remove any immediate parent anchor tag. 
		
		// Optionally turn off that images' responsiveness
		// (it doesn't mix well with an un-responsive imagemap)
		if ( option_srcset == 'off' ) {
			image.removeAttr('srcset sizes');
		}
		
		// When _HANDLE_SIZES is implemented, will need to choose an image with
		//	the right width
			
	}

})( jQuery );
