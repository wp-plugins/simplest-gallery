=== Simplest Gallery Plugin ===

Contributors: cleoni
Tags: gallery,simple,image,images,photo,photos,picture,pictures,fancybox,jquery,best gallery plugin,slideshow,photo-albums,best-gallery-plugin
Requires at least: 3.5.0
Tested up to: 3.8.1
Stable tag: trunk
License: MIT License
License URI: http://en.wikipedia.org/wiki/MIT_License

The simplest way to integrate Wordpress' builtin Photo Galleries into your pages with nice visual effects.



== Description ==


"Simplest Gallery Plugin" is the simplest way to integrate WordPress' builtin Photo Galleries into your pages with nice visual effects. 

WordPress has got a builtin Gallery functionality which is great and lets you create, organize and place images galleries in your pages and posts with great ease.

If your theme doesn't render the galleries properly, this little plugin adds a flavor of magic by rendering them with a fancybox jQuery effect at no effort. 
Just install, activate and experience the wonder of your WordPress galleries. 
More gallery styles can be added later with additional plugins, and you can create your own. See http://www.simplestgallery.com for this.


== Installation ==

Ultra-simple:

1. Install from the WP administration interface: Plugins->Add New, and Activate
2. Create or edit any page or post. Insert a gallery into it by using the Wordpress builtin method ("Add Media" button).
3. View page and enjoy the gallery!

4. OPTIONAL: If you are not using the Wordpress default theme and you see the galleries are not working properly, go to WP Admin / Settings / Simplest gallery and change 
the "Compatibility" setting to "Use Gallery Specific jQuery" to resolve possible jQuery conflicts.

5. OPTIONAL: if you don't like how your galleries look like, go to WP Admin / Settings / Simplest Gallery and change the format of the gallery. You can add more gallery styles by adding extension plugins.

For more tips and help/support, check out the [Simplest Gallery Plugin Website](http://www.simplestgallery.com/ "Simplest Gallery Plugin Website")



== Screenshots ==

1. Thumbnail Gallery - allowed multiple in the same page and with different columns
2. View of single image when clicked

== Demo ==

* [Demo page for FancyBox (default) style](http://www.simplestgallery.com/what-is-simplest-gallery-plugin/ "Demo page for FancyBox (default) style")
* [Demo page for ImageFlow style](http://www.simplestgallery.com/add-ons/imageflow-gallery-style-plugin/ "Demo page for ImageFlow style")
* [Demo page for jQuery Cycle Slideshow style](http://www.simplestgallery.com/add-ons/jquery-cycle-slideshow-gallery-style-plugin/ "Demo page for jQuery Cycle Slideshow style")
* [Demo page for LightView style](http://www.simplestgallery.com/add-ons/lightview-gallery-style-plugin/ "Demo page for LightView style")
* [Demo page for PikaChoose style](http://www.simplestgallery.com/add-ons/pikachoose/ "Demo page for ImageFlow style")

* [2-minute Video Tutorial](http://www.simplestgallery.com/support/tutorial-how-to-create-a-perfect-image-gallery/ "2-minute Video Tutorial")


== Credits ==

Includes the free Lightbox FancyBox 1.3.4 for the default, builtin gallery style (see http://fancybox.net, Licensed under both MIT and GPL licenses)



== Changelog ==

* 2.9 2014-04-05	Replaced Fancybox library with to FancyBox 1.3.4 (http://fancybox.net/) which is Licensed under both MIT and GPL licenses
* 2.8 2013-11-04	Fix for language support - Fix for admin bar disappearence problem (Thanks Mike Hegy)
* 2.7 2013-10-27	Fixed and tested for WP 3.7 - Added support for user-set columns - Fixed notices with WP_DEBUG set
* 2.6 2013-10-15	Improved support towards earlier versions of jQuery via the migration jQuery plugin
* 2.5 2013-09-18	Improved support for addon styles (added $gallery_id parameter for rendering function API syntax). Support & fix for jQuery 1.10.2 (Thanks Ian Byrne)
* 2.4 2013-09-12	Added settings box in page/post edit screen for selecting the desired gallery type and more settings. 
   			Support for multiple galleries in the same page/post.
   			Extended the SimplestGallery API to support rendering of more than one gallery per page (gallery_id and post_id parameters)
   			Auto-setup for fresh WP installs (uses jQuery bundled with Simplest Gallery)
* 2.3 2013-08-28	Optimized code for speed. Bug fix: Plugin did not work for WP gallery setting different from Link to: Attachment Page - now fixed.
* 2.2 2013-08-28	Bug fix in fbg-init.js. Added setting to force WP to use the correct version of jQuery - fixed compatibility issues with WP 3.6
* 2.1 2013-07-21	Added folders to the distribution (language support and more stuff) 
* 2.0 2013-07-21	Replaced included fancybox library to FancyBox 2.1.5 by Janis Skarnelis - http://fancyapps.com/fancybox/ in order to fix IE10 compatibility issues for default gallery style
* 1.3 2013-04-29	Added API support for external modules: More gallery formats can now be easily added with custom made plugins. 
   			Added support for gallery_type custom field for using different gallery types on different posts/pages
* 1.2 2013-04-16	Added possibility to select from a list of gallery types (for the moment: with/without labels). Added multi-language support.
* 1.1 2013-04-01	Replaced standard Lightbox with Lightbox 1.2.1 by Janis Skarnelis available under MIT License http://en.wikipedia.org/wiki/MIT_License
* 1.0 2013-03-28	First working version