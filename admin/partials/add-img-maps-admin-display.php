<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       mcdonald.me.uk
 * @since      1.0.0
 *
 * @package    Add_Img_Maps
 * @subpackage Add_Img_Maps/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">

    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

    <form method="post" name="add_img_maps_options" action="options.php">

	<?php
        //Grab all options
        $options = get_option($this->plugin_name);

        // Load the options into variables for some reason
        $srcset = $options['srcset'];
		$do_header = $options['header'];
		$do_content = $options['content'];

        settings_fields($this->plugin_name);
        // do_settings_sections($this->plugin_name); // I have settings in one global, so this does nothing.
    ?>
	
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
	
	<!-- Do we run this on the headers? -->
    <fieldset>
        <legend class="screen-reader-text"><span><?php _e('Add image maps on header images', $this->plugin_name); ?></span></legend>
        <label for="<?php echo $this->plugin_name; ?>-header">
            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-header" name="<?php echo $this->plugin_name; ?>[header]" value="1" <?php checked( $do_header, 1 ); ?> />
            <span><?php esc_attr_e('Add image maps on header images', $this->plugin_name); ?></span>
        </label>
    </fieldset>	

	<fieldset>
        <legend class="screen-reader-text"><span><?php _e('Add image maps on content images', $this->plugin_name); ?></span></legend>
        <label for="<?php echo $this->plugin_name; ?>-content">
            <input type="checkbox" id="<?php echo $this->plugin_name; ?>-content" name="<?php echo $this->plugin_name; ?>[content]" value="1" <?php checked( $do_content, 1 ); ?> />
            <span><?php esc_attr_e('Add image maps on content images', $this->plugin_name); ?></span>
        </label>
    </fieldset>	

	
	<!-- Content images? -->
	
	<?php submit_button(__('Save all changes', $this->plugin_name), 'primary','submit', TRUE);	?>
    </form>

</div>