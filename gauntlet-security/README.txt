=== Gauntlet Security ===
Contributors: cbergen
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=RGTZ39B4M83SA
Tags: security, secure, vulnerability, exploit, hacks, audit, scanner, virus, gauntlet, checklist, protection
Requires at least: 3.4
Tested up to: 4.0
Stable tag: 1.0.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Performs a detailed security analysis of your WordPress installation. Gives tips on how to make your site more secure.

== Description ==

The Gauntlet Security plugin shows you ways you can make your WordPress site more secure. It does not make changes to your database or to any of your files and it should be compatible with all other security plugins.

Many of the recommendations Gauntlet Security makes involves editing your site's php.ini, wp-config.php, .htaccess, or functions.php files. Doing so is not without risk and it's important to understand what you're doing and how to revert your changes. This is not a "one-click" solution. 

Checks and recommendations include:

* Set correct file and directory permissions
* Turn off directory indexing
* Prevent code execution in the uploads directory
* Block files in the includes directory
* Rename or move the content directory
* Disable dangerous PHP functions
* Disable allow_url_include and allow_url_fopen PHP flags
* Use a strong database password
* Change the default database table prefix
* Keep WordPress up-to-date
* Turn off the display of PHP errors
* Turn off file editing in the control panel
* Set security keys in the WP-Config file
* Don't advertise the WordPress version you are running
* Turn off self-registration
* Force SSL when accessing the admin area
* Review the development activity and reputation of all plugins
* Remove unused themes from the server
* Do not use TimThumb
* Do not use common user names (such as "admin")
* Do not use weak passwords
* Do not have a user with an ID = 1
* Minimize the number of admin users
* Users should not display their login usernames publicly
* Prevent username enumeration through standard author URLs
* ...more tests are planned

Check the [screenshots](screenshots) tab above for more detail on some of the above features.

= Requirements =

* Apache web server
* WordPress 3.4 minimum
* PHP 5.2 minimum
* Single site mode (multisite mode is not supported yet)

= Disclaimer =

I can't guarantee that the recommendations or sample code provided in this plugin will not break your site or that they will prevent it from being hacked. Before attempting any of these fixes, you should be comfortable experimenting and know how to undo any change you make. That includes making appropriate backups and knowing how restore your site from those backups.

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'gauntlet security'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select 'gauntlet-security.zip' from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download 'gauntlet-security.zip'
2. Extract the 'gauntlet-security' directory to your computer
3. Upload the 'gauntlet-security' directory to the '/wp-content/plugins/' directory
4. Activate the plugin in the Plugin dashboard

== Frequently Asked Questions ==

= Why can't this plugin make these changes automatically? =
Most of this plugin's recommendations do not require a plugin - they are server configurations or WordPress configurations that only need to be set once. 

Gauntlet Security can find opportunities for improvement and recommend ways to harden your site. It can also help you identify the risks of making specific changes to your WordPress configuration. Other plugins can automate a few of these hardening techniques for you, but if something breaks it's not always easy to revert the changes. Many of the suggested  fixes cannot be automated.

== Screenshots ==

1. The main page. 
2. All checks include a detailed explanation and instructions on how to fix the issue. 
3. Not all issues need to be fixed. Less important tests are included for the paranoid. 

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

