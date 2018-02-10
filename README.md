# Add Image Maps ###

Wordpress Plugin to implement HTML Image Maps for any image, including headers

`
Contributors: driannmcdonald
Tags: display, images, links, imagemaps
Requires at least: 3.0.1
Tested up to: 4.9.2
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
`
# Description

This plugin allows authors to turn areas of an image into links by implementing 
HTML image maps in Wordpress.

Authors can enter the co-ordinates of image areas on the editing page and see 
them outlined on the image. They will then appear in public.

The plugin allows for featured images and headers. Admins can configure where
the plugin looks for images with maps, and how it interacts with responsive
images.

# KEPT FOR IAN

    Note that the `readme.txt` of the stable tag is the one that is considered the defining one for the plugin, so
if the `/trunk/readme.txt` file says that the stable tag is `4.3`, then it is `/tags/4.3/readme.txt` that'll be used
for displaying information about the plugin.  In this situation, the only thing considered from the trunk `readme.txt`
is the stable tag pointer.  Thus, if you develop in trunk, you can update the trunk `readme.txt` to reflect changes in
your in-development version, without having that information incorrectly disclosed about the current stable version
that lacks those changes -- as long as the trunk's `readme.txt` points to the correct stable tag.

    If no stable tag is provided, it is assumed that trunk is stable, but you should specify "trunk" if that's where
you put the stable version, in order to eliminate any doubt.

## Installation 

1. Upload `add-img-maps.zip` to the `/wp-content/plugins/` directory
1. Unzip it.
1. Activate the plugin through the 'Plugins' menu in WordPress

### How to use

1. Choose the image you wish to edit in the library. 
1. Open it in the attachment edit page. (From "Attachment details", click on "Edit more details")
1. Click on "create image map for size 'full'"
1. Add clickable areas, including alt text as well as links.

(This is not a full GUI, but the areas are visible on the image, which is slightly greyed out when
you edit the image maps.)

1. Click 'update' to save the image with the map.
1. Add the image to the desired page (or, set it as a header, if wished)
1. If the image is in page content (rather than a featured or header image), confirm that it is
attached to the page in question, either on the attachment editing screen, or the list view of the
media library.

## Troubleshooting

### Where do I add the image map?

[screenshots]

### I've created an image map, and inserted the image into a page, but the image map isn't working.

Add_Img_Maps doesn't search the page HTML to find every image (which would slow the plugin down). Instead, it asks Wordpress which images are attached to it (or featured, or the header).

These are the things to check:

1. Did you add the Image Map to the right instance of the image?

When you add images to a theme, as a header or an icon, Wordpress sometimes creates a new cropped or shrunk image. Those copies do not appear in the media library grid screen, so they're easy to miss. They *are* listed in the Add_Img_Maps box on the attachment edit screen, with a link the *their* attacment edit screen.

2. Is the image attached to the post/page in the Wordpress database?

(This question doesn't apply if it's the featured image or the header, which are checked separately.)

The Add_Img_Maps box tells you which post/page (if any) the image is attached to. 

You can change which images are attached to which pages if, as an admin, you go to the Media Library and choose the list view.

'Images List' page. [Screenshot B]

> ### What exactly do I mean by 'attached'?
> "Attached", in this context, doesn't quite mean exactly the same thing as appearing on the page. By default, the 
> images "attached" to the post are the ones uploaded whilst editing it,
> which is why they are listed as "uploaded to this page" in 
> the post edit screen. Depending which editor you use, you can easily end up putting an image on a post without 
> "attaching" it. And if you upload an image to a page, and then remove the image, it will still be "attached".

3. Does the site theme include the image ID in its markup?

Sorry, but this involves looking at the page/posts' code view.

Most themes, when the insert images into pages, include the image's Wordpress ID somewhere in the HTML. (The number on the edit screen address after `post =`). The most popular ways are as the value of an attribute called `data-attachment-id` or a series of CSS classes of the form `wp-image-*id*`, ending in the id number.

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

* Developing for release.


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

A way to fail more gracefully when the site is being viewed on a device with a smaller screen.

## DELETEABLE brief Markdown Example ##

Ordered list:

1. *Some feature*
1. Another feature
1. Something else about the plugin

Unordered list:

* something
* something else
* third thing