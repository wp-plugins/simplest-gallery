=== Simplest Gallery Plugin ===

Contributors: cleoni
Tags: gallery, simple, fancybox, jquery
Requires at least: 3.5.0
Tested up to: 3.5.1
Stable tag: trunk
License: MIT License
License URI: http://en.wikipedia.org/wiki/MIT_License

The simplest way to integrate Wordpress' builtin Photo Galleries into your pages with nice visual effects.



== Description ==


"Simplest Gallery Plugin" is the simplest way to integrate WordPress' builtin Photo Galleries into your pages with nice visual effects. 

Since v. 3.5.1, WordPress has got a new Gallery functionality which is great and lets you create, organize and place images galleries in your pages and posts with great ease.

This little plugin adds a flavor of magic to you galleries by seamlessly adding a fancybox jQuery effect to your galleries at no effort. You can add more gallery styles later if you want.

Just install, activate and experience the wonder of your WordPress galleries!

Note: Includes the free Lightbox FancyBox 2.1.5 by Janis Skarnelis - http://fancyapps.com/fancybox/ (default, builtin gallery style)



== Installation ==

Ultra-simple:

1. Install from the WP administration interface: Plugins->Add New, and Activate
2. Create or edit any page or post. Insert a gallery into it by using the Wordpress builtin method ("Add Media" button).
3. View page and enjoy the gallery!

4. OPTIONAL: If you are not using the Wordpress default theme and you see the galleries are not working properly, go to WP Admin / Settings / Simplest gallery and change 
the "Compatibility" setting to "Use Gallery Specific jQuery" to resolve possible jQuery conflicts.

5. OPTIONAL: if you don't like how your galleries look like, go to WP Admin / Settings / Simplest Gallery and change the format of the gallery. You can add more gallery styles by adding extension plugins.

For more tips and help/support, check out the [Simplest Gallery Plugin Website](http://www.simplestgallery.com/ "Simplest Gallery Plugin Website")



== See it in action ==

* [Demo page for FancyBox (default) style](http://www.simplestgallery.com/what-is-simplest-gallery-plugin/ "Demo page for FancyBox (default) style")
* [Demo page for jQuery Cycle Slideshow style](http://www.simplestgallery.com/add-ons/jquery-cycle-slideshow-gallery-style-plugin/ "Demo page for jQuery Cycle Slideshow style")
* [2-minute Video Tutorial](http://www.simplestgallery.com/support/tutorial-how-to-create-a-perfect-image-gallery/ "2-minute Video Tutorial")


== Changelog ==

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