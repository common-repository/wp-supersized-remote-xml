=== WP-Supersized remote XML ===
Contributors: mproffitt
Tags:  background, full screen, media gallery, nextgen gallery, slideshow, Supersized
Tested up to: 3.5.1
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple plugin to enable remote xml feed and api for wp-supersized

== Description ==

This plugin enables remote access to wp-supersize xml feeds in order to allow
galleries to be shared across multiple wordpress sites.

This plugin is primarily designed to be used with NextGen galleries but will
also display wordpress galleries.

The XML file can be found remotely by accessing http://example.com/?wp-ss-xml.
This will display the default options available to all feeds as an XML file.

To access a particular gallery:

**NextGen galleries:**
Simply paste the feed URL into the XML tab on the page you wish the gallery to
be displayed. The gallery will become active when the plugin recognises the
"gallery=<n>" parameter NextGen automatically generates.

Alternatively you may specify the parameter directly by calling the URL as:
http://example.com/?wp-ss-xml&nggallery=2

**Wordpress galleries**
For a wordpress gallery to be displayed it must be published on a given page.
simply point the XML tab at http://example.com/?wp-ss-xml&wpgallery=<your_post_id>

**Custom galleries**
If you wish to share a custom gallery, again it must first be published.
to share it remotely paste http://example.com/?wp-ss-xml&customgallery=<your_post_id>

All options available to WP-Supersize can be over-ridden using the remote xml file
either by passing them in to the URL or posting them to the site via a form.

*Example:*
http://www.example.com/?wp-ss-xml&customgallery=1&slideshow=1&autoplay=1&transition=2

== Installation ==
1. Upload `wp-supersized-xml` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Optionally copy wp-ss-gallery.xml to your theme directory to override WP-Supersized global options for remote viewing.

Requirements:

- PHP 5.3
- WP-Supersized (http://wordpress.org/extend/plugins/wp-supersized/)

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 1.2 =
* Fixed issue with removed method WPSupersized::convert_empty_options_to_zero
* Tidied up output xml
* Removed generation of elements which would become nested un-necessarity causing "Array" to be printed into the generated XML.

= 1.1 =
* Fixed critical bug with trying to read from file pointer even if it wasn't open.
* Fixed issue where some servers prevent allowing fopen to the same domain. Now falls
  back to using fsocketopen. See http://www.jitsc.co.uk/blog/programming/retrieving-content-using-fsocketopen/
  for more information.
* Improved error handling around opening remote URLs

= 1.0 =
Initial release
