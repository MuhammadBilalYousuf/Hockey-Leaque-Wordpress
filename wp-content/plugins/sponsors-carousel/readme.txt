=== Sponsors Carousel ===
Contributors: Nitay
Donate link: http://elfiny.top/donate
Tags: gallery, images, banners, sponsors, jcarousel, carousel, slider
Requires at least: 4.6
Requires PHP: 5.2
Tested up to: 5.0
Stable tag: trunk


This plugin displays thumbnail images or banners in a carousel using jQuery jCarousel.


== Description ==

NEW! Introduced Wordpress 5.0 Gutenberg Editor block!

Sponsors Carousel implements the jCarousel as a WordPress plugin.

You can set internal link for all image or custom link for any image.

Upload images use Wordpress Media.

Drag'n'drop for change image order

Multiple carousels on one page.

The plugin uses the shortcode [sponsors_carousel], [sponsors_carousel id=1] ...

The plugin uses jQuery, and if your site doesn't already use jQuery, it'll add the script for you.

It was inspired by jCarousel by Jan Sorgalla.


== Installation ==

1. Upload the folder sponsors-carousel to the /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add <code><?php echo sponsors_carousel(); ?></code> in theme PHP files or [sponsors_carousel] on page or post content.
4. Add images on Sponsors Carousel admin page
5. Enjoy



== Screenshots ==

1. This shows the default look of the carousel.

2. This shows admin panel.

== Changelog ==

* 1.0: First release.
* 1.01: Fix bag with add new image.
* 1.02: Fix bags with _media_button() (Thanks for <a href="http://wordpress.org/support/profile/karamba">karamba</a> and Adam Gillis) and i18s.
* 1.03: Fix crash adding images to posts/pages through the media upload (Thanks for Adam Gillis).
* 2.00: New functionality:open link in new window and autoscroll (Thanks for <a href="http://wordpress.org/support/topic/plugin-sponsors-carousel-another-patch-new-window-and-auto-scroll">elija</a> ).
* 2.01: i18n update
* 2.02: Continuous Mode (Thanks for Sebastián Valerio G.)
* 2.03: fixed media upload
* 3.00: added possibility to make some carousels, added resize images from admin area, new version of jCarousel
* 3.01: made carousel to stop on hover, thanks dadi2404
* 3.02: fix bug, brought into line with the Coding Standards
* 3.03: fix continuous scrolling, thanks @fptech
* 3.04: fix bug "Featured Image not working with this plugin", thanks @vescovo
* 3.05: fix trash notices in the log files, thanks Svetoslav Marinov
* 3.06: fixed bug "custom link is not been saved", thanks @geraldhammenga
* 3.07: added link to settings page in plugins list
* 3.08: WP 5.0 + some css
* 4.00: Introduced Gutenberg editor block
