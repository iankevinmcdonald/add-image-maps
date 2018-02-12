<?php

/**
 * The content area of the plugins' options page.
 *
 * @link       mcdonald.me.uk
 * @since      1.0.0
 *
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/admin/partials
 */
?>

<div class="wrap">

    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

    <form method="post" name="<?php echo $this->plugin_name; ?>-options" action="options.php">

	<?php
        //Grab all options
        $options = get_option($this->plugin_name);

        // Load the options into variables for some reason
        $srcset = $options['srcset'];
		$do_imagemapresizer = $options['imagemapresizer'];
		$do_header = $options['header'];
		$do_thumbnail = $options['thumbnail'];
		$do_content = $options['content'];

        settings_fields($this->plugin_name);
        // do_settings_sections($this->plugin_name); // I have settings in one global, so this does nothing.
    ?>
<p>	
	<fieldset>
		<legend class="screen-reader-text"><span><?php _e('Behaviour with responsive images', $this->plugin_name); ?></span></legend>
		<label for="<?php echo $this->plugin_name; ?>-srcset-off">
			<input type="radio" id="<?php echo $this->plugin_name; ?>-srcset-off" name="<?php echo $this->plugin_name; ?>[srcset]" value="off" <?php checked($srcset,'off'); ?> />
			<span><?php esc_attr_e( 'Images that use maps are not responsive', $this->plugin_name ); ?></span>
		</label><br>
		<label for="<?php echo $this->plugin_name; ?>-srcset-run">
			<input type="radio" id="<?php echo $this->plugin_name; ?>-srcset-run" name="<?php echo $this->plugin_name; ?>[srcset]" value="run" <?php checked($srcset,'run'); ?> />
			<span><?php esc_attr_e( 'Images that use maps can still be responsive. Remember, Image Maps use absolute co-ordinates, so you should only choose this if you have a workaround for responsive images.', $this->plugin_name); ?></span>
		</label>
	</fieldset>
</p>	
	<!-- What about running the resizer script? -->

<p>	
	<fieldset>
        <legend class="screen-reader-text"><span><?php _e('Automatically resize image maps', $this->plugin_name); ?></span></legend>
        <label for="<?php echo $this->plugin_name; ?>-imagemapresizer">
            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-imagemapresizer" name="<?php echo $this->plugin_name; ?>[imagemapresizer]" value="1" <?php checked( $do_imagemapresizer, 1 ); ?> />
            <span><?php esc_attr_e('Automatically resize image maps', $this->plugin_name); ?></span>
			<span><a href="https://github.com/davidjbradshaw/image-map-resizer"><em>(<?php esc_attr_e('uses Image Map Resize by David Bradshaw et al', $this->plugin_name); 
				?>)</em></a></span>
        </label>
    </fieldset>	
</p>
	
<p>	
	<!-- Do we run this on the headers? -->
    <fieldset>
        <legend class="screen-reader-text"><span><?php _e('Add image maps on header images', $this->plugin_name); ?></span></legend>
        <label for="<?php echo $this->plugin_name; ?>-header">
            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-header" name="<?php echo $this->plugin_name; ?>[header]" value="1" <?php checked( $do_header, 1 ); ?> />
            <span><?php esc_attr_e('Add image maps on header images', $this->plugin_name); ?></span>
        </label>
    </fieldset>	
	
    <fieldset>
        <legend class="screen-reader-text"><span><?php _e('Add image maps on featured images', $this->plugin_name); ?></span></legend>
        <label for="<?php echo $this->plugin_name; ?>-thumbnail">
            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-thumbnail" name="<?php echo $this->plugin_name; ?>[thumbnail]" value="1" <?php checked( $do_thumbnail, 1 ); ?> />
            <span><?php esc_attr_e('Add image maps on featured images', $this->plugin_name); ?></span>
        </label>
    </fieldset>		

	<fieldset>
        <legend class="screen-reader-text"><span><?php _e('Add image maps on content images', $this->plugin_name); ?></span></legend>
        <label for="<?php echo $this->plugin_name; ?>-content">
            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-content" name="<?php echo $this->plugin_name; ?>[content]" value="1" <?php checked( $do_content, 1 ); ?> />
            <span><?php esc_attr_e('Add image maps on content images', $this->plugin_name); ?></span>
        </label>
    </fieldset>	
</p>
	
	<!-- Content images? -->
	
	<?php submit_button(__('Save all changes', $this->plugin_name), 'primary','submit', TRUE);	?>
    </form>

</div>