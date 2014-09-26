=== Multipart robots.txt editor ===
Contributors: szepe.viktor
Donate link: https://szepe.net/wp-donate/
Tags: google, robot, robots, robots.txt, search, seo, crawlers, spiders, editor
Requires at least: 3.8
Tested up to: 4.0
Stable tag: 0.2
License: GPLv2

Customize your site's robots.txt and include remote content to it

== Description ==

= This plugin need more documentation! =

You can edit your robots.txt and add remote content to it.
E.g. you have several sites and want to use a centralized robots.txt.

= Features =

- Include or exclude WordPress' own robots.txt (core function)
- Include or exclude plugins - e.g. sitemap plugins - output to robots.txt (filter output)
- Include or exclude a remote text file (the common part)
- Include or exclude custom records from the settings page (the site specific part)

= TODO =

- add more description here
- add a video too
- add an admin notice for subdir installs (robots.txt is useless in a subdir)
- 'At least one "Disallow" field must be present in the robots.txt file.' - check for that

[GitHub repo](https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/multipart-robotstxt-editor)

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the content of the ZIP feil to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How often will be the remote text file downloaded? =

Every 24 hours and when you press the Sava Changes button on the setting page.

== Changelog ==

= 0.2 =
* Fixed some serious PHP Notices, sorry

= 0.1 =
* Initial release
