<?php
/*
Plugin Name: Multipart robots.txt editor
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Reports 404s and various attacks in error.log for fail2ban
Version: 0.1
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
*/


// WARNING! Garbage. Should be a class.


function vs_readme_func() {

if( ! class_exists( 'Voce_Settings_API' ) ) {
	require_once( dirname( __FILE__ ) . '/voce-settings-api.php' );
}

Voce_Settings_API::GetInstance()->add_page( 'Multipart robots.txt editor', 'Multipart robots.txt', 'mp-robotstxt',
    'manage_options', 'The robots.txt consists of several records, you can edit them below.', 'options-general.php', 'no')
	->add_group( 'Parts of robots.txt', 'mprobotstxt' )
		->add_setting( 'Enable WordPress generated records', 'enable_robotstxt_hook', array(
			'default_value' => true,
			'display_callback' => 'vs_display_checkbox',
			'description' => 'Enable or disable this part.',
			'sanitize_callbacks' => array( 'vs_santize_checkbox' )
		))->group
		->add_setting( 'WordPress generated records', 'robotstxt_hook', array(
			'default_value' => mprt_do_robots(),
			'display_callback' => 'vs_display_textarea',
			'description' => 'This makes up the TOP part of your robots.txt.',
			'sanitize_callbacks' => array( 'vs_sanitize_robots_txt' ),
			'attributes' => array( 'disabled' => true )
		))->group
		->add_setting( 'Enable remote robots.txt', 'enable_remote_url', array(
			'default_value' => true,
			'display_callback' => 'vs_display_checkbox',
			'description' => 'Enable or disable this part.',
			'sanitize_callbacks' => array( 'vs_santize_checkbox' )
		))->group
		->add_setting( 'URL of the remote robots.txt', 'remote_url', array(
			'default_value' => 'http://www.szepe.net/badbots.txt',
			'description' => 'The records from this file is updated every day. This makes up the MIDDLE part.',
			'sanitize_callbacks' => array( 'vs_sanitize_url' )
		))->group
		->add_setting( 'Enable custom records', 'enable_manual_records', array(
			'default_value' => true,
			'display_callback' => 'vs_display_checkbox',
			'description' => 'Enable or disable this part.',
			'sanitize_callbacks' => array( 'vs_santize_checkbox' )
		))->group
		->add_setting( 'Custom records', 'manual_records', array(
			'default_value' => 'User-agent: MJ12bot
Crawl-delay: 10
Disallow: $site_url-path/wp-admin/
Disallow: $/wp-includes/

# all robots
User-agent: *
Crawl-delay: 10
Disallow: $site_url-path/wp-admin/
Disallow: $/wp-includes/

Sitemap: ' . home_url( 'sitemap.xml' ) . PHP_EOL,
			'display_callback' => 'vs_display_textarea',
			'description' => 'Site specific records. This makes up the BOTTOM part.',
			'sanitize_callbacks' => array( 'vs_sanitize_text' )
		))->group
		->add_setting( 'Delete records on uninstallation', 'forget_records', array(
			'default_value' => false,
			'display_callback' => 'vs_display_checkbox',
			'description' => 'All these robots.txt records will be deleted when the plugin is Uninstalled.',
			'sanitize_callbacks' => array( 'vs_santize_checkbox' )
	    ) );
}

vs_readme_func();

function mprt_uninstall() {

    if ( ! current_user_can( 'activate_plugins' ) )
        return;

    check_admin_referer( 'bulk-plugins' );

    $mprobotstxt = get_option( 'mprobotstxt' );
    if ( $mprobotstxt && true == $mprobotstxt['forget_records'] )
        del_option( 'mprobotstxt' );
}

register_uninstall_hook( __FILE__, 'mprt_uninstall' );


/*
function mprt_init() {
//		if ( get_option('blog_public') ) {
			remove_action( 'do_robots', 'do_robots' );
			add_action( 'do_robots', 'mprt_do_robots' );
//		}
}
add_action( 'init', 'mprt_init' );
*/

/**
 * Display the robots.txt file content. (copy of WP core function, without do_action)
 *
 * The echo content should be with usage of the permalinks or for creating the
 * robots.txt file.
 *
 * @since 2.1.0
 */
function mprt_do_robots() {
	header( 'Content-Type: text/plain; charset=utf-8' );

	$output = "User-agent: *\n";
    //into construct
	$public = get_option( 'blog_public' );
	if ( '0' == $public ) {
		$output .= "Disallow: /\n";
	} else {
		$site_url = parse_url( site_url() );
		$path = ( !empty( $site_url['path'] ) ) ? $site_url['path'] : '';
		$output .= "Disallow: $path/wp-admin/\n";
	}

	/**
	 * Filter the robots.txt output.
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Robots.txt output.
	 * @param bool   $public Whether the site is considered "public".
	 */
	return apply_filters( 'robots_txt', $output, $public );
}


/*TODO
- new func: update_option with defaults (on activation)
- new func: delete_option (on deactivation)
- new display cb arg: add attr (class="code", disabled=HTML5)

i18n
row, col, size args
legends! where?
TODO radios
TODO multi checkboxes
HTML textarea + editor (settings-api-tabs-demo-ban megnézni)
issue: ideas for Tabs (separate pages, 1 page + js hide/show, 1 page + ?tab=)

add option after activation: (no autoload?)
SELECT option_value
FROM subd_options
WHERE option_name = 'ftypes3'
LIMIT 1


*/