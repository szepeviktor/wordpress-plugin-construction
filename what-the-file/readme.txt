=== What The File ===
Contributors: barrykooij
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Q7QDZTLCRKSMG
Tags: toolbar, development, file, template, template editing, Template Hierarchy, theme, themes, php, php file, template part
Requires at least: 3.1
Tested up to: 3.8.1
Stable tag: 1.4.1
License: GPL v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

What The File adds an option to your toolbar showing what file and template parts are used to display the page you're currently viewing.

== Description ==

What The File adds an option to your toolbar showing what file and template parts are used to display the page you're currently viewing. You can click the file name to directly edit it through the theme editor, though I don't recommend this for bigger changes. What The File supports BuddyPress and Roots Theme based themes.

More information can be found <a href='http://www.barrykooij.com/what-the-file/'>here</a>.
For support please visit the <a href='http://wordpress.org/support/plugin/what-the-file'>Support forum</a>.

== Installation ==

1. Upload `what-the-file` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently asked questions ==

= Where can I see what template file is used? =

In the toolbar you will find the "What The File" option. Hovering this option will display the currently used template file, clicking the template file name will allow you to edit the template file with the WordPress file editor. Please note that some BuddyPress files can't be edited in the WordPress editor.

= I can't find the "What The File" option in the toolbar =

You have to be an Administrator to see the "What The File" option.

= Does What The File supports BuddyPress =

Yes it does.

= Does What The File supports Roots Theme =

Yes it does.

== Screenshots ==

1. What The File shows you what template file is used.

== Changelog ==

= 1.4.1 =
* Fixed wrongly aligned arrow in MP6 - props [remyvv](https://github.com/remyvv).
* Template parts are now correctly shown in child themes - props [remyvv](https://github.com/remyvv).
* Code style change.

= 1.4.0 =
* Fixed a template part bug, props remyvv
* Code style change

= 1.3.2 =
* Plugin now check if file exists in child theme or parent theme.

= 1.3.1 =
* Editing files directly through the theme editor now supports child themes.

= 1.3.0 =
* Added template part support.

= 1.2.1 =
* Improved the admin panel and administrator role check.

= 1.2.0 =
* Added BuddyPress support.
* Added WordPress.org review notice.
* Fixed admin check.
* Small code changes and refactoring.
* Extended GPL license.

= 1.1.2 =
* Fixed admin url bug caused when WordPress is installed in a subdirectory.

= 1.1.1 =
* Small meta information changes.

= 1.1.0 =
* Added Roots Theme support.
* Added WordPress 3.5.1 support.
* Meta information changed.

= 1.0.3 =
* Added WordPress 3.5 support.
* Small meta information changes.

= 1.0.2 =
* Fixed incorrect url when theme directory name differs from theme name.

= 1.0.1 =
* Changed the way the plugin initializes.
* Moved CSS from file to inline CSS.