(function( $ ) {
	'use strict';

	 $( attach_maps ); // $() is called when the DOM is ready.	
	
	
	function attach_maps () {
		// For each map, find the image(s), attach the map, and (optionally) remove their responsiveness
	
		// Maps
		var maps = $('span#addimgmaps-maps map');
		
		console.log( maps );
		
		maps.each( attach_to_image );
	}
	
	function attach_to_image( index, map ) {
		console.log('attach_to_image for ', map);

		var image, image_id;
		
		/* The most common ways of labelling images are "data-attachment-id" attributes and 
		 * classes named "wp-image-nnn". 
		 */
		image_id = map.getAttribute('data-image-id');
		
		image = $('img.wp-image-' + image_id + ',img[data-attachmend-id="' + image_id + '"]' );
		
		// If that fails, we're trying to guess it from the URL.
		if ( ! image ) {
			image = $('img[src="' + map.data('url') + '"]');
		}
		console.assert( image, 'Not found image.');
		
		image.attr('usemap', map.name );
		
		// FOR HANDLE_SIZES, choose an image with the right width
		
		//TODO remove srcset & interactive width
	
	}
	
	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );
