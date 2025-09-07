# Add Image Maps ###

Wordpress Plugin to implement HTML Image Maps for any image, including headers

The basic information for the plugin directory is in [readme.txt](readme.txt), in WordPress markdown format.

This page includes some extra implementation details that aren't appropriate for the plugin directory.

### Hooks

This works by running in 'the_content' and 'wp_footer' hooks to scan the post or
page for attached images. If images with maps are found, then the maps are
output in the footer. 

Javascript then attaches the maps to the images, where possible.

### Internal Data Formats

See [data_formats.md](data_formats.md).