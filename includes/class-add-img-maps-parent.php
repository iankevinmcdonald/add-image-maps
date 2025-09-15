<?php



/**
 * Used for constants that are shared between classes.
 */
class Add_Img_Maps_Parent
{
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   global
     * @const    This is a constant, not a variable
     * @var      string    PLUGIN_NAME    The string used to uniquely identify this plugin.
     */
    const PLUGIN_NAME='add-img-maps';

    /**
     * The unique identifier of this plugin for private metadata.
     *
     * @since    1.0.0
     * @access   global
     * @const    This is a constant, not a variable
     * @var      string    PLUGIN_NAME    The string used to uniquely identify this plugin.
     */
    const PLUGIN_KEY='_add_img_maps';

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    const PLUGIN_VERSION = '1.0.1';

	/**
	 * Setting defaults.
	 * @since 1.1.0
	 * @const array SETTING_DEFAULTS The settting defaults, to avoid repeating.
	 */
	const SETTING_DEFAULTS=	[
		'header' => 1,
		'content' => 1,
		'thumbnail' => 1,
		'imagemapresizer' => 1,
		'srcset' => 'off',
	];

}