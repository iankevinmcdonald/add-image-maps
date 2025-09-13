<?php
/*
 * Class for managing plugin metadata.
 * 
 * @since 0.1.0
 */
 
class Add_Img_Maps_Metabox
{

	/*
	 * Register Metadata box to image attachment pages.
	 *
	 * @access	public
	 * @param	{void}
	 * @return {void}
	 */
	 
    public static function add()
    {
		/* I believe this argument is how to limit by attachment */
		add_meta_box(
			Add_Img_Maps::attr_prefix() . '_metabox',
			'Image Maps',
			'Add_Img_Maps_Metabox::html',
			'attachment'
		);
    }
 
	 /**
	 * Save the image maps when an attachment post is saved. 
	 * 
	 * Attached to the pre-updated hook.
	 *
	 * @access 		public
	 * @param 		int 	$post_id 	The post ID.
	 */
 
 
    public static function save($post_id)
    {
		// error_log('Add_Img_Maps_Metabox->save');
		
		try {
		
			$post = get_post($post_id);
			
			// If this is not an image, bail
			if ( strncasecmp( $post->post_mime_type, 'image', 5) ) {
				// error_log('Mime type is ' . $post->post_mime_type );
				return $post_id;
			}

			// Bail out now if POST vars not set
			if ( ! isset( $_POST[ Add_Img_Maps::name() . '-nonce'] ) ) {
				return $post_id;
			}
			// Bail out now if nonce doesn't verify
			if ( ! wp_verify_nonce( $_POST[ Add_Img_Maps::name() . '-nonce'], Add_Img_Maps::name() ) ) {
				return $post_id;
			}
			
			// Start by turning the relevant input forms into an array. 
			$input = array();
			foreach ( $_POST as $field => $val ) {
			
				/* if the key belongs to this plugin */
				if ( ! strncmp( $field, Add_Img_Maps::attr_prefix(), 10) ) {
					/* Field names translate into a layered associative array - 
					 * EG addimgmaps->size->0->shape - and each input field is a
					 * 'leaf' 
					 */
					$subkeys = explode('-', sanitize_text_field($field) );
					$val = sanitize_text_field( $val );
					
					// $subkeys[0] is the prefix, so not part of the array
					// $subkeys[1] is the image size.
					// $subkeys[2] is usually a numerical area list, or a flag (eg 'rm')
					switch ( count($subkeys) ) {
						case 2:
						$input[ $subkeys[1] ] = $val;
						break;
						
						case 3:
						$input[ $subkeys[1] ][ $subkeys[2] ] = $val;
						break;
						
						case 4:
						$input[ $subkeys[1] ][ $subkeys[2] ][ $subkeys[3] ] = $val;
						break;
						
						case 5:
						$input[ $subkeys[1] ][ $subkeys[2] ][ $subkeys[3] ][ $subkeys[4] ] = $val;
						break;
						
						default:
						// This should not happen, and is an apt error log.
						error_log( "Add_Img_Maps: Unrecognised input option: $field.");
					}
				}
			}

			/* INPUT now has the form
			 * 
			 * $input[$size][$areaNum] = [ shape=>$shape, alt=>$alt, href=>$href, 0,1,2,3...=>[ $x, $y] ]
			 *
			 */
			
			/* No addimgmaps input at all */
			if ( 0 == count($input) ) {
				return;
			}

			/* Have we been passed any 'input' values without the 'unchanged' flag? */
			if ( 0 == count(
				array_filter( $input, function($map) {
					/* If the 'unchanged' flag is either absent or false */
					return (! isset($map['unchanged']) or
					! $map['unchanged']);
				})
			)) {
				// error_log('No changes in add_img_maps');
				return;
			}
			
			//error_log( 'Parsed $input:' );
			//error_log( print_r( $input, true ) );

			// Load the previously saved image maps as an array.
			$maps_metadata = get_post_meta( $post_id, Add_Img_Maps::get_key(), true );
			
			//error_log('Retrieved old post metadata:');
			//error_log( print_r( $maps_metadata, true ) );
			
			// If this is an entirely new image map ...
			if ( ! $maps_metadata ) {
				$maps_metadata = array ();
			// If the metadata are storing just one map, it's the one for the full image.
			} elseif ( $maps_metadata instanceof Add_Img_Maps_Map ) {
				// (For backwards compatibility with data saved by an earlier dev version.)
				$fixed_maps = array( 'full' => $maps_metadata );
				$maps_metadata = $fixed_maps;
			}
			
			// Which maps are new with this save?
			
			$new_maps = array_diff_key( $input, $maps_metadata) ;
			
			// Process all the old maps first.
			//error_log( 'List of key (sizes) of $new_maps (if any):');
			//error_log( print_r( $new_maps, true ) );
			
			foreach ( $maps_metadata as $size => $map ) {

				if( is_numeric($size) ) {
					throw new Exception("Unexpected size $size. maps_metadata: " . print_r($maps_metadata, true));
				}
				
				/* If the flag is set to remove this map ... */
				if ( isset($input[$size]['rm']) and $input[$size]['rm'] ) {
					unset ( $maps_metadata[$size] );
				/* If the flag is set to say this map isn't changed, it saves us some processing. */
				} elseif ( isset($input['size']['unchanged']) and $input[$size]['unchanged']) {
					/*do nothing */;
				/* Else the input defines the new map */
				} else {
					//Remove the flags (unset doesn't throw an error if it doesn't exist)				
					unset( $input[$size]['unchanged'], $input[$size]['rm'] );
					
					// Send the rest of it to the constructor.
					$maps_metadata[$size] = new Add_Img_Maps_Map( $input[$size] );
				}
			}
			
			/* New maps are in $input but not maps_metadata; construct them too */
			foreach( $new_maps  as $size => $map ) {
				$maps_metadata[$size] = new Add_Img_Maps_Map( $input[$size] );
			}
				
			/* And update the metadata */ 
			// error_log( 'After updating, maps_metadata are: ' . print_r( $maps_metadata, true ) );
			
			update_post_meta(
				$post_id,
				Add_Img_Maps::get_key(),
				$maps_metadata
			);

		} catch ( Exception $e) { //anything go wrong?
		
		?>	<div><p class="notice notice-error"><?php
			_e(
				'The Add_Img_Maps plugin failed to save the map. The details are in the error log & page source.',
				Add_Img_Maps::name()
			);
			echo '<!-- ' . esc_html($e) . '-->';
			error_log ("Plugin Add_Img_Maps caught Exception: $e");
			
		?>	</div></p>			<?php

		} // caught error
	
    }
 
 /**
  * Output the metadata box on an attachment edit page.
  * 
  * @see add, add_meta_box
  *
  * @access	public
  * @param	{WP_Post}	The object for the current post.
  * @return null
  */
 
    public static function html($post)
    {
		try {
			// Get the imagemaps saved as metadata
			$imagemaps = get_post_meta($post->ID, Add_Img_Maps::get_key(), true);
			
			// Get the general image metatdata (eg sizes)
			$image_metadata = wp_get_attachment_metadata($post->ID);

			$all_sizes = array();
			$sizesWithoutImageMaps = array();

			//error_log('Image: ' . print_r( (array) $post, true));
			//error_log('Meta: ' . print_r( $image_metadata, true ));
			//Canvas will be superimposed onto main image.
			?>
	<canvas id="addimgmaps-canvas" class="add_img_maps-canvas"></canvas>
	<?php

			wp_nonce_field( Add_Img_Maps::name(), Add_Img_Maps::name() . '-nonce' );
			
			if (ADD_IMG_MAPS_HANDLE_SIZES == true) {
				$all_sizes = array_keys( $image_metadata['sizes'] );
			}
			// 'Full' is not included in the WP 'sizes' metadata, because it's the default.
			array_unshift( $all_sizes, 'full' );
			
			// Create size_dimensions to pass to script.
			$size_dimensions = $image_metadata['sizes'];
			$size_dimensions['full'] = array(
				'file' => $image_metadata['file'],
				'width' => $image_metadata['width'],
				'height' => $image_metadata['height']
			);
	?>
	<div id='addimgmaps-ctrlmaps' data-size_dimensions='<?php echo json_encode($size_dimensions); ?>' >
	<span class="hide-if-js notice notice-warning inline" 
			><?php _e('Javascript required.', Add_Img_Maps::name() ); ?></span>
	<?php			

			/*
			 * @ignore. When implemented, this will be the biz logic of the 
			 * _HANDLE_SIZES feature
			 *
			 * No Img Maps:
			 * "There are no maps attached to this image. / Add map to size [[full]..]" (select menu)
			 * 
			 * OR [before editing]
			 * Image Maps -> Option to (Open) existing maps or add.
			 * 
			 * OR [during editing]
			 * Open Img Maps -> One map is open for editing; option to add or switch; (or delete current map)
			 *					(Most of this business logic is already in the Javascript)
			 *
			 * There will be a *UI need* to only edit one at a time, otherwise it gets confusing.
			 *
			 */
			 
			 if ( $imagemaps ) { 

			 // Backwords compatibility with maps stored by earlier dev version.
				if ( $imagemaps instanceof Add_Img_Maps_Map ) {
					$new_imagemaps = array( 'full' => $imagemaps );
					$imagemaps = $new_imagemaps;
				}

				// Ignore invalid maps.
				foreach ($imagemaps as $image_size => $map ) {
					if ( ! $map->is_valid() ) {
	?>					<div class="notice notice-warning inline"><P><?php
						printf(
                        /** translators: %s: image size */
							__('Invalid image map for size <em>"%s"</em> ignored.', Add_Img_Maps::name() ),
							$image_size );
	?>					</p></div><?php			
						unset( $imagemaps[$image_size] );
					}
				}

				foreach ($imagemaps as $image_size => $map) {
			/* @ignore. Implementation note for _HANDLE_SIZES, when it happens
			 * .create image size pulldown
			 * 	.initialise the HANDLE_SIZES 'add map' button 
			 */	
					$image_size_id = Add_Img_Maps::attr_prefix() . '-' . $image_size;
				?>
					<div id="<?php echo $image_size_id; ?>">
					<a href="#" class="button-secondary add_img_maps-ed"
						id="<?php echo $image_size_id; ?>-ed" data-imagesize="<?php echo $image_size; ?>">
					<?php			
					if (ADD_IMG_MAPS_HANDLE_SIZES) {
						printf(
                            /** translators: %s: image size */
							__('Open image map for size %s.', Add_Img_Maps::name() ),
							$image_size
							);

					} else {
						_e('Open image map for editing.', Add_Img_Maps::name() );
					}
					// Set up the 'unchanged' and 'rm' flags that JS can set if needed.
					?></a>
					<input type="hidden" name="<?php echo $image_size_id ?>-unchanged" id="<?php 
						echo $image_size_id ?>-unchanged" value="1">
					<input type="hidden" name="<?php echo $image_size_id ?>-rm" id="<?php 
						echo $image_size_id ?>-rm" value="0">
					</div>
	<?php		} // close foreach Image
				
	//			error_log( '$imagemaps=' . print_r( $imagemaps, true) );
				
				$sizesWithoutImageMaps = array_diff ( 
					$all_sizes, array_keys($imagemaps) );

			} else { // if no image maps
				// $imagemaps = array(); // Commented out because we don't actually use the empty $imagemaps.
				$sizesWithoutImageMaps = $all_sizes;			
				?>
				<div ><?php _e('There are no maps attached to this image.', Add_Img_Maps::name() ); ?></div>
	<?php	}

			switch (count($sizesWithoutImageMaps)) {
				case 0: // no sizes witout image maps, so nothing to mention
				break;
				
				case 1: // just one
				?>
				<a href="#" class="button-secondary dashicons-before dashicons-screenoptions" id="addimgmaps-cr" 
				data-imagesize="<?php echo $sizesWithoutImageMaps[0]; ?>"  >
	<?php 
				printf(
                    /** translators: %s: image size */
					__('Create image map for size "%s".', Add_Img_Maps::name() ),
					$sizesWithoutImageMaps[0]
					);
				?></a>
	<?php		break;
		
				default: // multiple sizes
	?>			<label for="addimgmaps-cr"><?php	
					_e('Create image map for image size', Add_Img_Maps::name() ); ?></label>
				<select name="addimgmaps-cr" id="addimgmaps-cr" class="postbox">
					<option value="<?php echo $sizesWithoutImageMaps[0]; ?>" selected 
					><?php echo $sizesWithoutImageMaps[0]; ?></option><?php
				foreach( array_slice($sizesWithoutImageMaps, 1) as $size ) {
					echo "<option value='$size'>$size</option>";
				}
	?>			</select>
	<?php	} // end Switch
			//End the ctrl element & start fieldSets - which involves looping over the maps all over again
	?>		</div><div>
	<?php	foreach ( $all_sizes as $size ) {
		?>		<fieldset id="addimgmaps-<?php echo $size ?>" class="add_img_maps-editmap"
	<?php			if ( $imagemaps && $imagemaps[$size] ) {
						if ( ! $imagemaps[$size] instanceof Add_Img_Maps_Map ) { // We're assuming this is a map object
							throw new Exception ('Expected map object, got ' . print_r( $imagemaps[$size] , true) );
						}
		?>				data-map='<? echo json_encode( $imagemaps[$size]->as_array() ); ?>'>
	<?php			} else {
		?>			> 		<?php
					} 
		?>		<div>		<?php
				printf( 
					__( 'Editing map for image size "%s".', Add_Img_Maps::name() ),
					$size);
		?>		</div></fieldset> <?php
			}

			// Images will only be detected in the page if they are attached, header, or thumbnail
			// For this reason the parent page of the image (if any) is displayed.
			if ( $post->post_parent ) {
				?><div class="notice notice-info inline" ><p><?php
				printf(
					/** translators: %1$s is the page title, %2$s is the page ID */
					__('Image attached to "%1$s" (id=%2$s)',
						Add_Img_Maps::name() ),
					get_the_title( $post->post_parent ),
					$post->post_parent
				);
			} else {
				?><div class="notice notice-warning inline" ><p><?php
				_e('This image is not attached to a page or post, so this plugin can only attach a map to it if it appears as a featured or header image.', Add_Img_Maps::name() );
			}
		?>	</p></div>			<?php
		
			/* 
			 *	Cropped versions of images do not appear in the image library. Because these are sometimes
			 *	the very header images we want to attach image maps too, there needs to be a way to reach
			 * 	them.
			 */
			// Can only add an extra SQL clause with a filter. Otherwise, I'm just doing a double SQL query.
			
			$image_basename = basename( wp_get_attachment_url( $post->ID ) );

			global $wpdb;
			
			$like_basename = function( $where, &$wp_query ) use ($image_basename) {
					global $wpdb;
					// error_log( print_r( $wp_query, true ));
					$where .= ' AND ' . 
						$wpdb->posts . ".guid LIKE '%" . $wpdb->esc_like( $image_basename ) . "'";
					return $where;
			};
			
			add_filter(
				'posts_where', 
				$like_basename,
				10,
				2
			);
						
			$child_images = get_posts(
				array(
					'post_type' => 'attachment',
					'posts_per_page' => -1,
					'post_status' => 'any',
					'post_mime_type' => 'image',
					'exclude' => array($post->ID),
					'suppress_filters' => false,	// filters suppressed by default
				)
			);
			
			// We need to let the editor know that they may be looking for something else.
			if ( count( $child_images ) ) {
		?>	<div id="addimgmaps-childimages" class="notice notice-info inline"><p><?php
				echo _n(
					// translators: singular
					'This image has apparently been used to create another. Is it <em>this</em> to which you wish to add a map?', 
					// translators: plural
					'This image has apparently been used to create others. Is it <em>one of these</em> to which you wish to add a map?',
					count( $child_images ),
					Add_Img_Maps::name() 
				);
				
		?>		</p><ul>		<?php
				foreach ( $child_images as $child_image ) {
		?>			<li>		<?php
					// error_log( print_r( $child_image, true));
					// Echo link to the edit page for image
					edit_post_link(
						// Link text 
						$child_image->post_title ,	
						// *translators*: Before link
						__('Edit image "', Add_Img_Maps::name() ),
						// *translators*: After link
						__('"', Add_Img_Maps::name() ),			
						$child_image->ID
					);
		?>			</li>		<?php
				}
		?>		</ul>			<?php
			} // END of child_images

			// Won't need that filter again.
			remove_filter(
				'posts_where',
				$like_basename,
				10 
			);
		
		} catch ( Exception $e) { //anything go wrong?
		
		//If it failed, say so.
		
		?>	<div><p class="notice notice-error"><?php
			_e(
				'The Add_Img_Maps box failed to display. The details are in the error log & page source.',
				Add_Img_Maps::name()
			);
			echo '<!-- ' . esc_html($e) . '-->';
			error_log ("Plugin Add_Img_Maps caught exception during display of metadata box: $e");
			
		?>	</div></div>			<?php
		
		} // End function
    }

	// End class
}