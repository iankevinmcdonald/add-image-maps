### Plugin Name ###

Contributors: drianmcdonald
Donate link: http://mcdonald.me.uk/
Tags: display, images, links, imagemaps
Requires at least: 3.0.1
Tested up to: 4.9.2
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Wordpress Plugin to implement HTML Image Maps for any image, including headers

## Description

Allow parts of images to be linkable by implementing HTML Image Maps.

Editors can set the dimensions of linkable areas on the image editor page. 

## README HELP ##

A few notes about the sections above:

*   "Contributors" is a comma separated list of wp.org/wp-plugins.org usernames
*   "Tags" is a comma separated list of tags that apply to the plugin
*   "Requires at least" is the lowest version that the plugin will work on
*   "Tested up to" is the highest version that you've *successfully used to test the plugin*. Note that it might work on
higher versions... this is just the highest one you've verified.
*   Stable tag should indicate the Subversion "tag" of the latest stable version, or "trunk," if you use `/trunk/` for
stable.

    Note that the `readme.txt` of the stable tag is the one that is considered the defining one for the plugin, so
if the `/trunk/readme.txt` file says that the stable tag is `4.3`, then it is `/tags/4.3/readme.txt` that'll be used
for displaying information about the plugin.  In this situation, the only thing considered from the trunk `readme.txt`
is the stable tag pointer.  Thus, if you develop in trunk, you can update the trunk `readme.txt` to reflect changes in
your in-development version, without having that information incorrectly disclosed about the current stable version
that lacks those changes -- as long as the trunk's `readme.txt` points to the correct stable tag.

    If no stable tag is provided, it is assumed that trunk is stable, but you should specify "trunk" if that's where
you put the stable version, in order to eliminate any doubt.

## Installation 

1. Upload `wp-imgmaps.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Edit your chosen image, and add an image map
1. Place the image on your page - and it should be clickable

### How to use

1. Choose the image you wish to edit in the library. 
2. 

## Troubleshooting

### Where do I add the image map?

[screenshots]

### I've created an image map, and inserted the image into a page, but the image map isn't being added. Why?

Add_Img_Maps doesn't examine the HTML of every page to find every image. (This would be computationally expensive, and would not allow you to fine-tune which instance of the image has map.) Instead, it asks Wordpress which images are registered to that page, either as header, featured image, or uploaded to that page.

So these are the things to check.

1. Did you add the Image Map to the right instance of the image?

When you add images to a theme, as a header or an icon, Wordpress sometimes creates a new cropped or shrunk image. Those do not appear in the image library, but they are listed in the Add_Img_Maps box on the page where you edit the image.

2. If it is part of the page content, is the image attached to the page in the database?

The Add_Img_Maps box tells you which post/page (if any) the image is attached to in the database. 

You can change which images are attached to which pages if, as an admin, you go to the Media Library and choose the list view.

'Images List' page. [Screenshot B]

> ("Attached", in this context, doesn't quite mean exactly the same thing as appearing on the page. By default, the > images "attached" to the post are the ones uploaded whilst editing it, and listed as "uploaded to this page" in 
> the post edit screen. Depending which screens you use, you can easily end up putting an image on a post without 
> "attaching" it. And if you upload an image to a page, and then remove the image, it will still be "attached".)

3. Does the theme mark it with the image ID?

Most themes include the image's Wordpress ID in the HTML. (The number on the edit screen address after `post =`). The most popular ways are as the value of an attribute called `data-attachment-id` or a series of CSS classes of the form `wp-image-*id*`, ending in the id number.

If your theme doesn't have those, then Add_Img_Maps will try to recognise it by filename, but that's not guaranteed, and you might have to manually add one of those to the HTML page.

## Screenshots TODO 

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot

## Changelog 

# 1.0 

* Testing and release.

# 0.1

* Developing release.


# 0.5 =
* List versions from most recent at top to oldest at bottom.

## Upgrade Notice ##

= 1.0 =

- WHY USERS SHOULD UPGRADE -

## Interaction with responsive images ##

HTML Image Maps don't play well with responsive images; their dimensions are absolute, and they don't scale up or down when CSS resizes the image. As far as I can tell, this is a problem with the image maps themselves, and not soluble with this plugin.

Wordpress 4.4 onwards includes srcset and sizes attributes. By default, Add Image Maps turns this off for images with maps added.

## Feature Wantlist ##

An alternative impelementation of displaying Image Maps (perhaps in CSS & JavaScript) that plays well with Responsive Images.

A way to turn it off when the site is being viewed on a device with a smaller screen.

## DELETEABLE brief Markdown Example ##

Ordered list:

1. Some feature
1. Another feature
1. Something else about the plugin

Unordered list:

* something
* something else
* third thing