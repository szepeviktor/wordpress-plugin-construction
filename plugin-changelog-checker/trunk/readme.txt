=== Plugin changelog checker ===
Contributors: szepe.viktor
Donate link: https://szepe.net/wp-donate/
Tags: developer, development, plugin, changelog, upload, svn
Requires at least: 3.8
Tested up to: 4.0
Stable tag: 0.2.0
License: GPLv2

Notifies you - the plugin developer - about WP.org not displaying changelog correctly.

== Description ==

You get an email daily if there is some difference between Wordpress.org's Changelog page and readme.txt in SVN trunk.

= Only for development! =

= Features =

After activation a "Watch" link is added to each plugin.
You may add there a plugin to watch for changelog differences.
After clicking on "Watch" it toggles to Unwatch and vice versa.

= Activation =

The daily (wp-cron) cycle begins when you activate the plugin.
The email is sent to the **admin email** address with subject `[<plugin-name>] Changelog mismatch`.

The email's body is the first line (the latest release listed) of the SVN version and the WordPress.org Changelog page.
You can write to plugins AT wordpress.org to correct the problem or wait for your next release.

= Links =

[GitHub repo](https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/plugin-changelog-checker/trunk)

== Installation ==

This section describes how to install the plugin and get it working.

1. Unzip the plugin to to the `wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How is it checked precisely? =

- WordPress.org's Plugin Changelog page is parsed ( `div.block-content` )
- readme.txt downloaded from http://plugins.svn.wordpress.org
- and parsed by posting to [Readme Validator](https://wordpress.org/plugins/about/validator/)
- the first 20 lines are compared

= Use in production? =

Please don't!

== Screenshots ==

No UI.

== Changelog ==

= 0.2.0 =
* Initial release

== Upgrade Notice ==

