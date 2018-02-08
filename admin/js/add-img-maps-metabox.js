/**
 * A closure containing the functions the Add_Img_Maps metadata box.
 *
 * Passed jQuery as an argument to be able to locally refer to it as $.
 * (jQuery elements are prefixed with 'jQ_').
 *
 * @package Add_Img_Maps/admin
 * @since 0.1.0
 * @returns Array with pseudo-public functions as values.
 */

var addImgMapsClosure = function($) {
	'use strict';
	
	var pluginClassName = "add_img_maps", // Not hyphenated because I use hyphens as a separator
		pluginIdName = "addimgmaps", //shorter used for Ids because they get very long
		ADD_IMG_MAPS_HANDLE_SIZES = false, // Not used at present.
		
		size_dimensions = { }; // Keep track of sizes (for _HANDLE_SIZES)

	/**
	 * Gets the attachment width for that imageSize.
	 *
	 * @since      1.0
	 * @access     private
	 * @param {string}   [var=full] which Wordpress size of the image (eg "full", "thumbnail")
	 * @returns {int} width in pixels
	 */	
	function getAttachmentWidth( imageSize ) {
		if ( undefined === imageSize ) {
			imageSize = 'full';
		}
		return size_dimensions[imageSize].width;
	}

	/**
	 * Gets the attachment height for that imageSize.
	 *
	 * @since      1.0
	 * @access     private
	 * @param {string}   [var=full] which Wordpress size of the image (eg "full", "thumbnail")
	 * @returns {int} width in pixels
	 */	
	function getAttachmentHeight( imageSize ) {
		if ( undefined === imageSize ) {
			imageSize = 'full';
		}
		return size_dimensions[imageSize].height;
	}
	
	/**
	 * Tells Init functions whether the client can run this.
	 *
	 * Only requirement is support for the "number" input type (absent in IE<9)
	 *
	 * @since      1.0
	 * @access     private
	 * @returns {boool}		True or False
	 */		
	function dependenciesSatisfied(  ) {
		/* Check that we can actually do this */
		var test = document.createElement("input");
		test.type="number";
		return ( test.type==="number");
	}

	/**
	 * Initialises the metabox.
	 *
	 * @since      1.0
	 * @access     public
	 * @returns   {none}
	 */			
	function init( ){
		// Fail gracefully if unsupported
		if ( ! dependenciesSatisfied ) {
			$( '#' + pluginIdName + '-metabox > .inside').get().innerHTML(
				__('Please upgrade your browser to one that supports HTML5 to use the editing aspects of this plugin','add-img-maps')
			);
			return;
		}
			
		// Put the canvas over the attachment pages' main image	
		var jQ_attachmentImage = $( '.wp_attachment_image img');
		console.assert(jQ_attachmentImage.length == 1, 'Problem with jQ_attachmentImage');
		
		var canvasElement = document.getElementById( pluginIdName + '-canvas' );
		
		// Move the canvas element to be next to the image in the DOM
		jQ_attachmentImage.get(0).parentElement.appendChild( canvasElement );
		
		// Give it the same dimensions
		canvasElement.width = jQ_attachmentImage[0].width;
		canvasElement.height = jQ_attachmentImage[0].height;
		/* Although the img element itself has no margin, its parent <P> element does, 
		 * and so I move the canvasElement down by as many pixels as the <P> top offset to compensate.
		 * (offsetTop is a number; style.top is a CSS element and needs the 'px' suffix.)
		 */
		canvasElement.style.top = jQ_attachmentImage[0].parentElement.offsetTop + 'px';
		
		/*
		 * Import the size_dimensions hash.
		 */
		 size_dimensions = $('#' + pluginIdName + '-ctrlmaps').data('size_dimensions');
		 console.assert( typeof (size_dimensions) == 'object', size_dimensions, typeof(size_dimensions) );
		
		
		// Initialise any 'create map' buttons
		var createMapButtons = $( '#' + pluginIdName + '-cr' );
		createMapButtons.click( function() {
			/**
			 * (anonymous function) Set up form for map for image size.
			 *
			 * @access Closure
			 */
			// BACKLLOG: Let user choose between sizes in a pulldown menu
			var image_size;
			if ( $(this).data('imagesize') ) {
				image_size = $(this).data('imagesize');
			} else {
				throw "Cannot find imagesize data attribute.";
			}
			// Set up the map for this image size.
			setupMap( image_size ); //Do I pass the target?
		});
		
		// Initialise any 'edit map' buttons.
		var editMapButtons = $( '.' + pluginClassName + '-ed' ); //button includes size on attr
		editMapButtons.click( function() {
			/**
			 * (anonymos function) to open existing map for editing
			 *
			 * @access Closure
			 */
			var image_size = $(this).data('imagesize');
			console.assert( image_size );
			//setupMap will open either new or saved map & call openEditMap to make visible.
			setupMap ( image_size ); 
		} );
	}
	
   /**
	 * Hides the control panel, unhides the editing area, dims the image, &c.
	 *
	 * Other maintenance includes removing the 'unchanged' flag, if it exists, 
	 * redrawing the map. It also switches the display from showing the 
	 * control fields, hiding the map fields, and hiding the canvas into the
	 * 'editing' state where the main image is dimmed, the canvas appears 
	 * over it, the 'control' form elements are hidden, and the map editing
	 * form elements appear.
	 * 
	 * @since      1.0
	 * @access   closure
	 * @var		string	imageSize
	 * @returns {none}		
	 */		
	
	function openEditMap ( imageSize ) {
		$('#addimgmaps-ctrlmaps').hide();

		//Grey the main image out a little; as another cue about imagemap being edited.
		$( 'img.thumbnail' )[0].style.opacity = 0.6; 

		// Show addimgmaps-<size> fieldset
		var jQ_thisFieldSet = $( 'fieldset#' + pluginIdName + '-' + imageSize );
		jQ_thisFieldSet.show();
		drawImageMap( jQ_thisFieldSet.get(0) );

		// Unset 'unchanged' flag (if any) to show that it's changed now.
		$( '#' + pluginIdName + '-' + imageSize + '-unchanged').val(0);
		
		// ensure the Canvas is visible
		$( 'canvas#addimgmaps-canvas' ).show();
	}

   /**
	 * Unhides the control panel, hides the editing area, restores the image.
	 *
	 * @since      1.0
	 * @access    private
	 * @var		string	imageSize
	 * @returns {none}		
	 */		
	
	function closeEditMap( imageSize ) {
		// Show the control panel
		$('#addimgmaps-ctrlmaps').show();
		// Hide addimgmaps-<size> fieldset
		$( 'fieldset#addimgmaps-' + imageSize ).hide();
		// Restore the main image.
		$( 'img.thumbnail' )[0].style.opacity = 1.0; 
		//And hide the canvas
		$( 'canvas#addimgmaps-canvas' ).hide();
	}

	
	
	/**
	 * Initialises the editing area for one size within the metadata box for the plugin.
	 *
	 * Sets up the canvas, initialises event listeners, optionally draws shape.
	 *
	 * @since      1.0
	 * @access     private
	 * @param {string}   [var=full] which Wordpress size (eg "full", "thumbnail") to open
	 * @returns {int} width in pixels
	 */		
	function setupMap( imageSize ) {

		if ( undefined === imageSize ) {
			throw 'Called setupMap without imageSize';
		}
	
		// Find Metabox element for imageSize - part of mapInit
		var mapForImageSize = $( 'fieldset#' + pluginIdName + "-" + imageSize ).get(0);
		var savedMap = false;

		// Check that it's a fieldset
		console.assert( typeof (mapForImageSize) == 'object', 'Failed to get element ' + pluginIdName + '-' + imageSize );
		console.assert( mapForImageSize.tagName == "FIELDSET", mapForImageSize);

		
		// Are we loading an existing map?
		if ( mapForImageSize.hasAttribute('data-map') ) {
			savedMap = JSON.parse( mapForImageSize.getAttribute('data-map') );
			//console.log ( 'Extracted JSON object:' . savedMap );
		}

		// And the remove Map button
		var rmMapButton = $('<A/>', {
			'id': pluginIdName + "-" + imageSize + "-rm",
			'class': 'button-secondary '+ pluginClassName  +'-rm dashicons-before dashicons-trash',
			'text' : ' Delete whole map',
			'href' : '#',
			'click' : function() {
				/**
			     * (Closure) Wipes editing fieldset & sets 'rm' flag.
				 *
				 * @see onClick
				 */

				 /* Delete the image map */				
				$('fieldset#addimgmaps-' + imageSize).empty();
				
				/* set 'rm' flag (unless this is a new map, in which it
				 * neither matters nor exists. */
				$( '[name=' + pluginIdName + '-' + imageSize + '-rm]' ).val(1);
				closeEditMap (imageSize);
				/* Expected to return -ed button to suggestion you create a new map; or cancel the deletion? */
				$( 'a#' + pluginIdName + '-' + imageSize + '-ed' ).text(
						'Cancel deletion & re-open "' + imageSize + '" map' );
						
				}
			}
				
		);
		
		// NOT CURRENTLY INCLUDED - WILL BE USED TO _HANDLE_SIZES
/*		var closeMapButton = $('<A/>', {
			'id': pluginIdName + "-" + imageSize + "-close",
			'class': 'button-secondary addimgmaps-close dashicons-before dashicons-admin-collapse',
			'text' : 'Pause editing',
			'href' : '#',
			'click' : function() {
				/**
				 * (Closure) to close the editing window without removing it.
				 *
				 * @See Click on 'stop editing' button.
				 */
				/* There are state changes to addimgmaps-ctrlmaps
				 * - either a "no map" has become "unsaved new map"
				 *		- or existing map has "unsaved changes"
				 *	
				 * (A deletion is modelled as a different state entirely.)
				 *

				 // Show control panel, modified
				$( 'a#' + pluginIdName + '-' + imageSize + '-ed' ).text(
						'Resume editing map for size "' + imageSize + '"' );
				closeEditMap(imageSize);

				}
			}
				
		);
*/

		var cancelMapButton = $('<A/>', {
			'id': pluginIdName + "-" + imageSize + "-close",
			'class': 'button-secondary ' + pluginClassName + '-close dashicons-before dashicons-undo',
			'text' : ' Cancel',
			'href' : '#',
			'click' : function() {
				/**
				 * (Closure) to cancel the edit, and return to the state on page load. 
				 * @See Click on 'stop editing' button.
				 */

				 /* Delete the fieldset for the editing image map */				
				$('fieldset#addimgmaps-' + imageSize).empty();
				
				/* set 'unchanged' flag (unless this is a new map, in which it
				 * neither matters nor exists. */
				$( '#' + pluginIdName + '-' + imageSize + '-unchanged').val(1);
				
				/* Switch back to the control fieldset */
				closeEditMap (imageSize);
								
				}
			}
				
		);
		
		
		
		
		// CF: Create Map ID is "#addimgmaps-cr" with a value of the imageSize
		var createAreaButton = $("<A/>", {
			'id': pluginIdName + "-" + imageSize + "-cr",
			'class': 'button-secondary add_img_maps-area-cr dashicons-before dashicons-plus-alt',
			'text' : ' Add new area',
			'href' : '#',
			'click' : function() {
				/**
				 * (closure handles event) Add form fields for new area & redraw the canvas.
				 * 
				 * @See Click on the "add area" button
				 */
					var newArea = createAreaBox( imageSize , nextChildIdNum( mapForImageSize ) ); 
					mapForImageSize.appendChild( newArea );
					drawImageMap( mapForImageSize );
				}
			}
		);
		
		//Append all the buttons, with linkebreak space between
		$(mapForImageSize).append( rmMapButton, ' ', cancelMapButton, ' ', createAreaButton, ' ');
		
		// Either set up the input forms for an existing image map ...
		if ( savedMap ) {
			var numAreas = savedMap.areas.length;
			for ( var i = 0; i< numAreas; i++ ) {
				var area = createAreaBox( imageSize, i, savedMap.areas[i]);	
				$(mapForImageSize).append(area);
			}
		// ... or for a new one
		} else {
			var firstArea = createAreaBox(imageSize, 0 , "rect");
			$(mapForImageSize).append( firstArea );
		}
		
		openEditMap ( imageSize );
		
		// This is a JScript event, not a JQuery one.
		mapForImageSize.addEventListener("change", drawImageMap);
	}	
	
	/**
	 * Create a div object with the input forms representing a single clickable area
	 *
	 * @since      1.0
	 * @access     private
	 * @see						createShapeSelect
	 * @see						createCoordForRect, createCoordForCircle, appendCoordForPoly
	 * @param      {string}   imageSize  which Wordpress size of the image (eg "full", "thumbnail")
	 * @param      {int}      areaIndex  which area we are creating
	 * @param      {object}   areaObj	EITHER an associate array representing an existing area OR
	 * 			OR	{string}	the shape of the clickable area (default: 'rect')
	 * @returns    {object}	A DIV element containing the input forms for that clickable area
	 */	
	function createAreaBox( imageSize, areaIndex, areaObj ) {
		
		var shape;
		// shape defaults to 'rect'; 
		if ( !areaObj ) {
			shape ="rect";
		} else if ( typeof areaObj == 'string' ) {
			shape = areaObj;
			areaObj = null;
		} else {
			// Existing area 
			shape = areaObj.shape;
		}

		console.assert ( shape=="rect"|| shape=="circle"||shape=="poly", "Invalid shape ", shape);

		var metaBoxForImageSize = $( 'fieldset#' + pluginIdName + "-" + imageSize ).get(0);
		console.assert( metaBoxForImageSize );
		
		var newArea = document.createElement("div");
		var newAreaId = pluginIdName + "-" + imageSize + "-" + areaIndex;
		newArea.id = newAreaId;
		newArea.className = pluginClassName + "-area";
		newArea.appendChild( 
			createShapeSelect( newArea.id, shape ) 
		);

		var deleteButton = document.createElement("a");
		deleteButton.className="button-secondary add_img_maps-area-rm dashicons-before dashicons-dismiss"; // WP Admin CSS class, shows it as button
		deleteButton.title="Delete area";
		deleteButton.text="Delete area";
		deleteButton.addEventListener("click", function() {
			/**
			 * Remove this clickable area & redraw (closure)
			 *
			 * @Listens	Clicks on the "Delete area" button
			 */
			metaBoxForImageSize.removeChild( newArea );
			drawImageMap( metaBoxForImageSize );
		});
		newArea.appendChild( deleteButton);
		
		switch ( shape ) {
			case "rect":
				newArea.appendChild( createCoordForRect( newArea, areaObj? areaObj.coords : null ) );
			break;
			
			case "circle":
				newArea.appendChild( createCoordForCircle( newArea, areaObj? areaObj.coords : null ) );
			break;

			// Poly also needs to add a button for extra co-ordinates.
			case "poly":
				var addCoordButton = document.createElement("a");
				addCoordButton.className="button-secondary add_img_maps-addcoord dashicons-before dashicons-plus"; 
				addCoordButton.title="+ co-ord pair";
				addCoordButton.text=" co-ord pair";
				addCoordButton.addEventListener("click", function() {
					addCoordPairForPoly( newArea );
					drawImageMap( metaBoxForImageSize );
				});
				newArea.appendChild(addCoordButton);
				if ( areaObj ) {
					appendCoordForSavedPoly( newArea, areaObj.coords );
				} else {
					appendCoordForNewPoly( newArea ) ; 
				}
			break;
			
			default:
			console.assert(false, "Unrecognised shape", shape);
		}
		
		/* Do the link field. */
		var newField = document.createElement("input");
		newField.type="url";
		newField.className="regular-text";
		newField.name= newAreaId + '-href';
		newField.maxlength=128;
		newField.size=32;
		newField.placeholder="Please enter the web link that the clickable area links to.";
		if ( areaObj ) {
			newField.value = areaObj.href;
		}
		newArea.appendChild( newField );

		newArea.appendChild( document.createElement('br'));
		
		/* Do the alt text field */
		newField = document.createElement("input");
		newField.type="text";
		newField.name= newAreaId + '-alt';
		newField.className="regular-text";
		newField.maxlength=128;
		newField.size=32;
		newField.placeholder="Please enter alternative text for people who don't see the image.";
		if ( areaObj ) {
			newField.value = areaObj.alt;
		}
		newArea.appendChild( newField );
		
		return newArea;
	}			

	/**
	 * Creates the select dropdown box to choose between the clickable area's shape
	 *
	 * @since      1.0
	 * @access     private
	 * @param      {string}   areaId  	 HTML id of the div of the clickable area
	 * @param      {string}   shape    the shape of the clickable area (default: 'rect')
	 * @returns    {object}	A DIV element containing the input forms for that clickable area
	 */	
	
	function createShapeSelect ( areaId, shapeValue ) {
//		console.log( areaId );

		var shape = document.createElement("select");
		
		var option = document.createElement("option");
		option.text="□ Rectangle";
		option.value="rect";
		option.name= areaId + "-shape";
		shape.add(option);
				
		option = document.createElement("option");
		option.text="○ Circle";
		option.value="circle";
		option.name=areaId + "-shape";
		shape.add(option);
		
		option = document.createElement("option");
		option.text="☆ Polygon";
		option.value="poly";
		option.name=areaId + "-shape";
		shape.add(option);
		
		// shape.selectedIndex = 0;
		shape.value = shapeValue;
		shape.name = areaId + '-shape';
		shape.className = pluginClassName + "-shape";
		
		shape.addEventListener("change", function(  ) {
				/**
				 * Recreate the clickable area when its shape is changed (closure)
				 * 
				 * @Listens The shape selector changing value.
				 */
				var newShapeValue = shape.value;

				// Our ID follows the rule {plugin}-{imageSize}-{areanum}
				var idBits = areaId.split("-");
								
				var parentMetaBox = $( 'fieldset#' + idBits[0] + "-" + idBits[1] ).get(0) ;
				
			/* 
			 * The alt text & URL are passed on to the new area, but not co-ordinates.
			 * Wishlist: translate co-ordinates when shape changes. (So a polygon
			 * turns into a rectangle occupying roughtly the same area).
			 */
				
				//console.log( parentMetaBox, shape, shape.parentNode );
				var newAreaBox = createAreaBox( idBits[1], idBits[2], newShapeValue );
				var oldAreaBox = parentMetaBox.replaceChild( 
					newAreaBox,
					shape.parentNode
				);

				// The only field of type TEXT is the ALT field.
				$( newAreaBox ).children('input:text').val(
					$( oldAreaBox ).children('input:text').val()
				);
				
				// Only one with a name field sending in HREF
				$( newAreaBox ).children('[name$="href"]').val(
					$( oldAreaBox ).children('[name$="href"]').val()
				);
							
			}
		);	
		
		return shape;
	}
	
	/*
	 * Create a div element with input boxes for 2 pairs of co-ordinates.
	 *
	 * @access private
	 * @param {DOMObject} areaDiv	    The Div representing the area to which the Co-ordinates are being added.
	 * @param {array}		coordArray	(optional) A list of co-ordinates.
	 *	
	 * @returns {DOMObject} The div element, ready to be appended.
	 */
	
	function createCoordForRect( areaDiv, coordArray ) {
		// createNumberInput - -0-x -0-y -1-x -1-y
		var coordsDiv = document.createElement( "div" );
		coordsDiv.id = areaDiv.id + "-co";
		var span1 = document.createElement( "span" );
		span1.className='add_img_maps-coord-pair';
		
		/**
		 * If no co-ordinates are passed, then put the rectangle
		 * roughly in the middle of the image, but give it a random
		 * offset so that new areas do not automatically superimpose
		 * over each other.
		 */
		if( ! coordArray ) {
			var randomOffset = Math.random();
			
			coordArray = [
				getAttachmentWidth() * 0.2 + 0.1*randomOffset,
				getAttachmentHeight() * 0.2 + 0.1*randomOffset,
				getAttachmentWidth() * 0.7 + 0.1*randomOffset,
				getAttachmentHeight() * 0.7 + 0.1*randomOffset,
			];
		}
		
		span1.appendChild ( 
			createNumberInput(
				areaDiv.id + "-0-x", 
				coordArray[0],
				getAttachmentWidth() - 1,
				'→'
			)
		);
		span1.appendChild ( 
			createNumberInput(
				areaDiv.id + "-0-y", 
				coordArray[1],
				getAttachmentHeight() - 1,
				'↓'
			)
		);
		
		var span2 = document.createElement( "span" );
		span2.className='add_img_maps-coord-pair';
		
		span2.appendChild ( 
			createNumberInput(
				areaDiv.id + "-1-x", 
				coordArray[2],
				getAttachmentWidth() - 1,
				'→'
			)
		);
		span2.appendChild ( 
			createNumberInput(
				areaDiv.id + "-1-y", 
				coordArray[3],
				getAttachmentHeight() - 1,
				'↓'
			)
		);
		
		coordsDiv.appendChild( span1);
		coordsDiv.appendChild( document.createTextNode(' ') );
		coordsDiv.appendChild( span2);
		return coordsDiv;
	}
	
	/*
	 * Create a div element with input boxes for the circle's position & radius.
	 *
	 * @param {DOMObject} 	areaDiv		DOM form element for the circle
	 * @param	{array}		coordsArray	List of co-ordinates (optional)
	 *	
	 * @returns {DOMObject} The div form element, ready to be appended.
	 */
	
	function createCoordForCircle(areaDiv, coordsArray ) {	
		// create NumberInput - x, y, r
		var coordsDiv = document.createElement( "div" );
		
		// Put a new area in the middle, with a random jiggle
		if ( ! coordsArray ) {
			var randomOffset = Math.random();
			coordsArray = [
				getAttachmentWidth() * 0.3 + 0.4*randomOffset,
				getAttachmentHeight() * 0.3 - 0.4*randomOffset,			
				(randomOffset+0.2)*(getAttachmentHeight()+getAttachmentWidth())/4
			];
		}
		coordsDiv.id = areaDiv.id + "-co";
		coordsDiv.appendChild ( 
			createNumberInput(
				areaDiv.id + "-x", 
				coordsArray[0],
				getAttachmentWidth() - 1,
				'→'
			)
		);
		coordsDiv.appendChild ( 
			createNumberInput(
				areaDiv.id + "-y", 
				coordsArray[1],
				getAttachmentHeight() -1,
				'↓'
			)
		);
		coordsDiv.appendChild ( 
			createNumberInput(
				areaDiv.id + "-r", 
				coordsArray[2],
				/* At this maximum, the circle could eclipse the whole area */
				(getAttachmentHeight()+getAttachmentWidth())/2,
				'𝑟'
			)
		);
		return coordsDiv;		
	}

	/*
	 * Append 3 co-ordinate div elements with input boxes for a co-ordinate 
	 * pair each.
	 *
	 * Polygons have an arbitrary number of co-ordinates, and hence more requirements.
	 * Thus these are created with a 'delete' button. But because a polygon needs at least
	 * 3 co-ordinates, the delete button is initially hidden.
	 * 
	 * The other difference to other shapes is the need to append the divs with
	 * the co-ordinate pairs within the function rather than to return them.
	 *
	 * @param {DOMObject} areaDiv	DOM form element for the polygon
	 *
	 * @see createCoordPairForPoly
	 *
	 * @returns {boolean} True 
	 */
	
	function appendCoordForNewPoly( areaDiv ) {
		// The polygons have multiple co-ordinate divisions
		var randomOffset = Math.random();
		
		areaDiv.appendChild( 
			createCoordPairForPoly( 
				areaDiv.id + "-0", 
				getAttachmentWidth() * 0.2 + 0.1*randomOffset,
				getAttachmentHeight() * 0.3 + 0.1*randomOffset
			)
		);

		areaDiv.appendChild( 
			createCoordPairForPoly( 
				areaDiv.id + "-1", 
				getAttachmentWidth() * 0.4 + 0.2*randomOffset,
				getAttachmentHeight() * 0.75
			)
		);

		areaDiv.appendChild( 
			createCoordPairForPoly( 
				areaDiv.id + "-2", 
				getAttachmentWidth() * 0.75,
				getAttachmentHeight() * 0.3 - 0.1*randomOffset
			)
		);
		
		// Make sure the delete buttons start off hidden
		$(areaDiv).find(".add_img_maps-delete-coords").hide();
		
		return true;
	}

	/*
	 * Append div elements with input elements for a previously saved polygon area.
	 *
	 * @param {DOMObject} areaDiv		DOM form element for the polygon
	 * @param {array}	  coordsArray	Array of the co-ordinates
	 *
	 * @see createCoordPairForPoly
	 * 
	 * @returns {boolean} True (because each co-ord pair is its own div, they must be appended in-function)
	 */

	 function appendCoordForSavedPoly( areaDiv, coordsArray ) {
		// The polygons have multiple co-ordinate divisions
		for (var i = 0; i*2 < coordsArray.length ; i++ ) {
		
			areaDiv.appendChild( 
				createCoordPairForPoly( 
					areaDiv.id + "-" + i, 
					coordsArray[2*i],
					coordsArray[2*i+1]
				)
			);

		}
		
		// Make sure the delete buttons start off hidden if this is already
		// a triangle.
		if ( coordsArray.length == 6 ) {
			$(areaDiv).find(".add_img_maps-delete-coords").hide();
		}
		
		return true;
	
	}
	
	
	
	/*
	 * Create a pair of polygon co-ordinates starting at the given dimensions.
	 *
	 * @see appendCoordForSavedPoly, appendCoordForNewPoly, 
	 *		appendCoordForNewPoly, addCoordPairForPoly
	 *
	 * @param	{string}	idStem		The area div id, plus an index for the co-ordinate pair
	 * @param 	{int}   	x	        x co-ordinate.
	 * @param 	{int}   	y	        y co-ordinate.
	 * 
	 * @returns {object} 	DOM object for co-ordinate pair input elements.
	 */
	function createCoordPairForPoly( idStem, x, y ) {
		var coordsDiv = document.createElement( "div" );
		coordsDiv.id = idStem;
		coordsDiv.appendChild ( 
			createNumberInput( idStem + "-x", x, getAttachmentWidth(), '→' )
		);
		coordsDiv.appendChild ( 
			createNumberInput( idStem + "-y", y, getAttachmentHeight(), '↓' )
		);
		coordsDiv.className="poly-coords";

		// Create a button to delete the co-ordinates	
		var deleteCoords = document.createElement( "a" );
		deleteCoords.className="button-secondary add_img_maps-delete-coords dashicons-before dashicons-no-alt"; 
		deleteCoords.title="Delete co-ordinates";
		deleteCoords.text=" "; /* The dashicon does enough. */
		deleteCoords.addEventListener("click", function() {
		/*
		 * Deletes the co-ordinate pair & makes follow-on changes (closure).
		 *
		 * Delete co-ordinate pair, redraw the image, and hide the buttons if
		 * the polygon has now become a triangle.
		 *
		 * @Listens for clicks on the "delete" button by a polygon co-ord pair
		 */
			var jQ_areaDiv = $(coordsDiv).closest("div." + pluginClassName + "-area");
			var jQ_numCoords = jQ_areaDiv.find(".poly-coords").length;
			if (jQ_numCoords <= 4) { // If we are about to hit the minimum co-ord pairs
				jQ_areaDiv.find(".add_img_maps-delete-coords").hide();
			}
			var areaDiv = jQ_areaDiv.get(0);
			areaDiv.removeChild( coordsDiv );
			// Pass drawImageMap the event, allowing it to track down the calling element that has the data
			drawImageMap( areaDiv );
		});
		coordsDiv.appendChild( deleteCoords );
		
		// NB: this doesn't try to count the number of coords; callers must do that
		return coordsDiv;
	}

	/** 
	 * Add a new polygon co-ordinate pair (or rather their input elements) 
     *
	 * Becase the polygon now has vertices to lose, this makes the 'delete' 
	 * button visible.
	 
	 * @Listens to the "add" button on polygon area.
	 *
	 * @see createCoordPairForPoly
	 *
	 * @param	{DOMObject}	areaDiv		DOM form element for the polygon
	 * @returns {DomObject}	areaDiv		DOM form element with added co-ord pair 
	 */ 
	function addCoordPairForPoly ( areaDiv ) {
		var whichIdNum = nextChildIdNum( areaDiv ), 
			jQ_coords = $( areaDiv).find(".poly-coords");
		// A sanity check
		console.assert( whichIdNum > 2, 
			"Called addCoordPairForPoly with ", areaDiv, "NextChildIdNum returned ", whichIdNum );
			
		jQ_coords.last().after(
			createCoordPairForPoly( 
				areaDiv.id + "-" + whichIdNum,
				getAttachmentWidth()*2/whichIdNum, //Will slowly track to the left, starting at 66%
				getAttachmentHeight()*( 0.1 + 0.2 * (whichIdNum % 2)) // Defaults to an up-down zig-zag
			)
		);
		
		// Make sure all the delete buttons are visible
		// (In theory, I could set this to only happen if jQ_coords.lenght==3, because that's
		//  the only time it should be needed, but a little robustness won't hurt.)
		$(areaDiv).find(".add_img_maps-delete-coords").show();
		
		return areaDiv;
	}

	/**
	 * Finds the next child index number for an HTML element with countable sub-elements
	 *
	 * This is used both when adding a new area to an imageMap, or a new vertex to a
	 * polygon. It relies on a consistent HTML id convention: a list of categories,
	 * subcategories, and index numbers, connected by hyphens:
	 * 		addImgMaps-full-0-3
	 *
	 * Note that the "next index" isn't the same as the number of relevant children, because
	 * some elements could have been deleted from the middle.
	 *
	 * @param 	{DOMObject} 	htmlElement   The element to search.
	 * @returns {int}			index to give to the *next* sub-element
	 */
	function nextChildIdNum( htmlElement ) {
		var lastAreaDiv= $(htmlElement).children("div").get(-1);
		if ( lastAreaDiv === undefined || lastAreaDiv.tagName.toUpperCase() != "DIV" ) {
//			console.log ("Looking for lastAreaDiv of ", htmlElement, "Found only ", lastAreaDiv);
//			console.trace;
			return 0;
		} else {
			var lastId = lastAreaDiv.id;
			//console.log(lastId);
		// Find the bit after the last "-" and turn it into a number.
			var suffix = lastId.substr( lastId.lastIndexOf("-")+1);
			return parseInt( suffix) + 1;			
		}
	}

/**
 * Create an Input element for a number.
 * 
 * Used to set up all co-ordinates.
 *
 * @param 	{string} 	id		HTML id to give the new number input box
 * @param 	{int}		value	Numerical value to give the input box
 * @param 	{int}		max		Max numerical value
 *
 * @returns {DOMObject} DOM element of the new numerical input box
 */ 
	function createNumberInput( numberId, defaultValue, max, labelText ) {
		var label = document.createElement('label');
		label.textContent = labelText;
		var numberInput = document.createElement("input");
		numberInput.type="number";
		numberInput.name=numberId;
		numberInput.id=numberId;
		numberInput.className="regular-text";
		numberInput.min=0;
		if ( max ) {
			numberInput.max=max;
		}
		numberInput.value = Math.round( defaultValue );
		label.appendChild(numberInput);
		return label;
	}	
 
 
/**
 * Redraws the clickable areas on the canvas.
 *
 * @param {object}   [e]		The event that triggered the redraw, OR
 *								The DOM Object that was clicked to trigger the event OR
								The DOM Object on which the event handler sat
 *
 * @returns			null
 */	
	
	function drawImageMap( e ) {

		// For some reason, I can't trust 'this' being a form entry field; it might be a div.
		// And I certainly can't trust "targetElement" either.
		var jQ_metaBoxForImageSize, canvas, context, scale;
		
		/* If this was triggered by a deletion, then "e.target" (or e) could be a DOMObject 
		 * that has already been removed. So we look at e.currentTarget
		 */
		if (e.currentTarget) {
			jQ_metaBoxForImageSize = $(e.currentTarget);			
		} else if (e.hasChildNodes) {
			jQ_metaBoxForImageSize = $(e);
		} else {
			// Else throw fatal error, as this should not happen.
			throw "drawImageMap called with " + e + " neither event nor DOM ancestor.";
		}

		// Find the overall parent of the input form
		if ( ! jQ_metaBoxForImageSize.is("fieldset") ) {
			jQ_metaBoxForImageSize = jQ_metaBoxForImageSize.closest("fieldset");
			console.assert( jQ_metaBoxForImageSize.length == 1, jQ_metaBoxForImageSize );
		}
		
		// There's only going to be one canvas
		canvas = $('#' + pluginIdName +"-canvas")[0];
		console.assert ( canvas );
		context = canvas.getContext("2d");
		context.globalCompositeOperation="xor";
		
		// About to start drawing, so choose this moment to clear the canvas.
		context.clearRect(0, 0, canvas.width, canvas.height);
		context.strokeStyle = "black";
		context.linewidth = "2em";
		context.shadowBlur = "10";
		context.shadowColor = "#ff8";
		
		/*
		 * {scale} is canvas/attachment
		 */
		scale = Math.min( 
			( canvas.width / getAttachmentWidth() ), 
			( canvas.height / getAttachmentHeight() )
		);
		
		
		jQ_metaBoxForImageSize.children("div").each( function(index,element) {
			// What shape is this?
			var shapeChooser = $( element ).children("select." + pluginClassName + "-shape");
			if ( ! shapeChooser.length ) {
				// Then this isn't an area div; it's something else.
				// console.log("Skipping div. Index & element are:", index, element);
				return null;
			}
			console.assert ( shapeChooser.length == 1 , shapeChooser );

			// NB: this ignores the id & relies entirely on the input order
			var x, y, r, coords;

			// All 3 start with x & y co-ords & a new path.
			coords = $( element ).find(":input[type=number]");
			console.assert ( coords.length > 2, coords );
			
			x = coords[0].value;
			y = coords[1].value;

			context.beginPath();
			
			switch( shapeChooser.val() ) {

			// Both of these involve getting a list of x/y pairs and drawing line between them
				case "rect":
					var x2 = coords[2].value;
					var y2 = coords[3].value;
					// strokeRect takes width & height, not co-ords
					context.strokeRect(scale*x, scale*y, scale*(x2-x), scale*(y2-y));
					// Doesn't actually use the beginPath / end / stroke sequence, but I put it
					// outside the switch block just to avoid repitition.
				break;
				
				case "poly":
					context.moveTo(scale*x,scale*y);
					coords.splice(0,2); // remove the first pair of co-ords
					while ( coords.length ) {
						x = coords[0].value;
						y = coords[1].value;
						coords.splice(0,2); // and then remove that pair
						context.lineTo(scale*x,scale*y);
					}
				break;

				// Circles involve fetching x, y, and r
				case "circle":
					r = coords[2].value;
					context.arc( scale*x, scale*y, scale*r, 0, Math.PI * 2, false);
				break;
				
				default:
				console.assert ( false, "Unrecognised shape", shapeChooser );
				
			} // End switch

			context.closePath();
				
			// Still need to fill it in

			context.stroke();


			
		} // end of Each closer
		); // end of fxn

	} // end function drawImageMap

	return {
		init: init
	};

}( jQuery ); // closureDefined

// wait till all loaded & call the init method within the closure
jQuery(document).ready( function() {
	addImgMapsClosure.init(); // now handled by button
} );

/**
 * Extant issues:
 * 
 * When testing in Chrome, I expect the browser to honour the 'min' and 'max' values set on input:number fields.
 * It doesn't.
 *
 */