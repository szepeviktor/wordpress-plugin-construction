=== What's running ===
Contributors: szepeviktor
Donate link: https://szepe.net/wp-donate/
Tags: debug, debugging, developer, development, performance, profiler, profiling
Requires at least: 3.5
Tested up to: 3.9.1
Stable tag: 1.5
License: GPLv2

Lists WordPress require() calls mainly for plugin code refactoring

== Description ==

= Only for development! =

This plugin dumps the colorized filenames after the normal WordPress output, after the closing html tag.
This generates invalid HTML but gives you an overview of plugins and the current theme.

It lists all files parsed and executed by the PHP engine. It can be used for plugin or theme refactoring.

* files in the plugin directory are BLUE
* files in the themes directory are ORANGE
* files in the wp-includes directory are GREEN
* files in the wp-admin directory are GREY
* all other files are RED

Please watch [this WP core bug](https://core.trac.wordpress.org/ticket/28364) to get more information about WordPress entry points.

You can find some documentaion here what makes a WordPress plugin efficient.
https://github.com/szepeviktor/WPHW

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `wp-requires.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= It messes up my backend, frontend! =

After finishing the refactor, please deactivate and delete this plugin.

== Screenshots ==

1. This is a screen shot of the dashboard. You can see the filenames after the admin footer.

== Changelog ==

= 1.5 =
* Added inline styles
* Link to WP core bug to watch

= 1.4 =
* FIX: don't run on non-AJAX media uploads (async-upload.php)
* tested up to WordPress 3.9

= 1.3 =
* FIX: on file uploads (async-upload.php) DOING_AJAX is defined late

= 1.2 =
* NEW: legend for the colors
* now you don't have to collapse the admin menu

= 1.1 =
* plugin name correction in the readme

= 1.0 =
* Initial release
* Colorized output

