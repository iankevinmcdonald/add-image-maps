<?php
/*
 * Adding meta box
 */
 
/* - add <label for=""> to files */

const ADDIMGMAPS_HANDLE_SIZES = false;

class Add_Img_Maps_Metabox
{

    public static function add()
    {
	/* I believe this is how to limit by attachment, but not sure */
		add_meta_box(
			'addimgmaps_metabox',
			'Image Maps',
			'Add_Img_Maps_Metabox::html',
			'attachment'
		);
    }
 
	 /**
	 * Save the image maps when an attachment post is saved. On the pre_post_update hook
	 *
	 * @param int $post_id The post ID.
	 */
 
 
    public static function save($post_id)
    {
		error_log('Called SAVE');
		$post = get_post($post_id);
		
		// If this is not an image, return
		if ( strncasecmp( $post->post_mime_type, 'image', 5) ) {
			error_log('Mime type is ' . $post->post_mime_type );
			return null;
		}
		
		/**
		 * To save this function from looking up the full set of images sizes,
		 * I pass it a flag field within the form [oops]
		 */
		
		//error_log( print_r( $_POST, true ) );
		
        if ( true ) { // array_key_exists('add_img_maps-TODO', $_POST)
			$input = array();
			foreach ( $_POST as $field => $val ) {
				/* if the key belongs to this plugin */
				if ( ! strncmp( $field, 'addimgmaps', 10) ) {
					/* Field names are hierarchical - addimgmaps->size->0->shape */
					$subkeys = explode('-', $field );
					// Ignore the first element, 'addimgmaps'
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
						error_log( "Unrecognised plugin key $field.");
					}
				}
			}

			/* INPUT now has the form
			 * 
			 * $input[$size][$areaNum] = [ shape=>$shape, alt=>$alt, href=>$href, 0,1,2,3...=>[ $x, $y] ]
			 *
			 * MAP Class constructor expects
			 * 
			 * [ areas=>[ [ shape=>$shape, alt=>$alt, href=>$href, coords=>[ ] ]... ]
			 */
			
			/* Are there any changes? */
			
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
				error_log('No changes in add_img_maps');
				return;
			}
			
			error_log( 'Parsed $input:' );
			error_log( print_r( $input, true ) );
			
			/* will have to understand the $_POST array, and go through the functions */
			$maps_metadata = get_post_meta( $post_id, '_add_img_maps', true);
			
			error_log('post metadata:');
			error_log( print_r( $maps_metadata, true ) );
			
			if ( ! $maps_metadata ) {
				$maps_metadata = array ();
			}
			
			foreach ( $maps_metadata as $size => $map ) {
				/* Deleted */
				if ( isset($input[$size]['rm']) and $input[$size]['rm'] ) {
					unset ( $maps_metadata[$size] );
				/* Unchanged */
				} elseif ( isset($input['size']['unchanged']) and $input[$size]['unchanged']) {
					; /*do nothing */
				/* Else the input defines the new map */
				} else {
					$maps_metadata = new Add_Img_Maps_Map( $input[$size] );
					// Unset the input, so to keep track of what's changed.
				}
			}

			$new_maps = array_diff_key( $input, $maps_metadata);

			error_log( print_r( $new_maps, true ) );
			
			/* New maps are in $input but not maps_metadata */
			foreach( $new_maps  as $size => $map ) {
				$maps_metadata[$size] = new Add_Img_Maps_Map( $input[$size] );
			}
				
			/* And update the metadata */ 
			error_log( print_r( $maps_metadata, true ) );
			
            update_post_meta(
                $post_id,
                '_add_img_maps',
                $maps_metadata
            );
			
        }
    }
 
    public static function html($post)
    {
        $imagemaps = get_post_meta($post->ID, '_add_img_maps', true);
		$image_metadata = wp_get_attachment_metadata($post->ID);

//Canvas might be best created by JS during init; will be superimposed onto main image.
		?>
<canvas id="addimgmaps-canvas" class="add_img_maps-canvas"></canvas><!-- Should be styled onto main image-->
<?php
		$all_sizes = $sizesWithoutImageMaps = array();
		if (ADDIMGMAPS_HANDLE_SIZES == true) {
			$all_sizes = array_keys( $image_metadata['sizes'] );
		}
		// 'Full' is not included in the 'sizes' metadata, because it's special.
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
<?php			

		/*
		 * Business logic of HANDLE_SIZES feature
		 *
		 * No Img Maps -> "There are no maps attached to this image. / Add map to size [[full]..]" (select menu)
		 * OR [b4 editing]
		 * Image Maps -> Option to (Open) existing maps or add.
		 * OR [during editing]
		 * Open Img Maps -> One map is open for editing; option to add or switch; (or delete current map)
		 *					(Most of this business logic is already in the Javascript)
		 *
		 * Only editing one at a time is a *UI need*, because otherwise it gets confusing
		 * 
		 * NB: if you edit & close a map, the form retains its hidden fields; but until then,
		 * it's a hidden field of "~-<size>=untouched". 
		 * 
		 * MVP is 'full' only; waiting on interface advice
		 * 
		 * Suggest a hidden field - "~-<size>=untouched"
		 * (JS TODO - position canvas over div#wp_attachment_image > img)
		 */
		 
		 if ( $imagemaps ) { 

		 // Early iterations stored just one image_map in the array.
			if ( $imagemaps instanceof Add_Img_Maps_Map ) {
				$new_imagemaps = array( 'full' => $imagemaps );
				$imagemaps = $new_imagemaps;
			}

			foreach ($imagemaps as $image_size => $map) {
		/* TODO 
		 *	create image size pulldown
		 * 	initialise the HANDLE_SIZES 'add map' button */	
			?>
				<div id="addimgmaps-<?php echo $image_size; ?>">
				<a href="#" class="button-secondary" id="addimgmaps-<?php echo $image_size; ?>-ed">
				<?php			
				if (ADDIMGMAPS_HANDLE_SIZES) {
					printf(
						__('Open image map for size %s.', 'add-img-maps'),
						$image_size
						);

				} else {
					_e('Open image map for editing.', 'add-img-maps');
					// Do something to trigger the autoloading of the imagemap TODO
				}
				?></a>
				<input type="hidden" 
					name="addimgmaps-<?php echo $image_size; ?>-unchanged" 
					id="addimgmaps-<?php echo $image_size; ?>-unchanged" value="1">				
				</div>
<?php		} // close foreach Image
			
//			error_log( '$imagemaps=' . print_r( $imagemaps, true) );
			
			$sizesWithoutImageMaps = array_diff ( 
				$all_sizes, array_keys($imagemaps) );
/*				function( $size) {
					error_log( '$imagemaps in closure=' . print_r( $imagemaps, true) );
					return ! array_key_exists( $size , $imagemaps );
				}
			);
*/		
		} else { // if no image maps
			// $imagemaps = array(); // Commented out because we don't actually use the empty $imagemaps.
			$sizesWithoutImageMaps = $all_sizes;			
			?>
			<div ><?php _e('There are no maps attached to this image.', 'add-img-maps'); ?></div>
<?php	}

		// Add new map pulldown:
		?><pre><?php var_dump("All_sizes", $all_sizes, "Sizes without Image Maps", $sizesWithoutImageMaps); ?></pre><?php
		
		switch (count($sizesWithoutImageMaps)) {
			case 0: // no sizes witout image maps, so nothing to mention
			break;
			
			case 1: // just one
			?>
			<a href="#" class="button-secondary dashicons-before dashicons-screenoptions" id="addimgmaps-cr" 
			data-imagesize="<?php echo $sizesWithoutImageMaps[0]; ?>"  >
<?php 
			printf(
				__('Create image map for size "%s".', 'add-img-maps'),
				$sizesWithoutImageMaps[0]
				);
			?></a>
<?php		break;
	
			default: // multiple sizes
?>			<label for="addimgmaps-cr"><?php	
				_e('Create image map for image size','add-img-maps'); ?></label>
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
			<input name="addimgmaps-<?php echo $size; ?>-unchanged" id="addimgmaps-<?php echo $size; ?>-unchanged"
					type="hidden" value="1">
<?php			} else {
	?>			> 		<?php
				} 
	?>		<div>		<?php
			printf( 
				__( 'Editing map for image size "%s".', Add_Img_Maps::PLUGIN_NAME ),
				$size);
	?>		</div></fieldset> <?php
		}
	?>	</div>			<?php
	/*
	 * And note, we now need two initalisations:
	 * - Initialise the box
	 * - Initialise an editor (which closes other editors) & replaces the hidden fxn
	 */
			
		/* Useful for testing, but I'm not adding enough HTML to make a real difference */
		// include_once( 'partials/add-img-maps-metabox.php' );
	/* ?><pre><?php var_dump("image_metadata", $image_metadata); ?></pre><?php */
	// End function
    }

	// End class
}
 
/*
 * Old HTML note I might refere back to but will ultimately delete.
			
<div id="addimgmaps-full">
	<div id="addimgmaps-controls"><h1>Map on __Full__ Size Image</h1></div>
	<a id="addimgmaps-full-rm" class="button-secondary" href="#">Delete whole map</a>
	<a id="addimgmaps-full-up" class="button-secondary" href="#">Edit image map</a>	
	<!--a id="addimgmaps-full-cr" class="button-secondary" href="#">Add new area</a-->
</div>
*/



 
/**
 * Class & ID scheme
 * 
 * addimgmaps_metabox - parent metabox
 * postbox - the class of the overall metabox (with handlediv & toggle-indicator & hndle important classes: with aria-hidden|expanded attrs
 * addimgmaps-<size>-rm|up|area-n-{}
 *
 * Classes:
 * postbox - part of WP - unsure
 */

