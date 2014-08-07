<?php
/*
Plugin Name: Disable Updates and Update HTTP Requests
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Disable core, theme and plugin updates plus the browser nag
Version: 0.2
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/
*/

if ( ! function_exists( 'add_filter' ) ) {
    error_log( 'File does not exist: errorlog_direct_access ' . $_SERVER['REQUEST_URI'] );
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

/**
 * To enable force-check updates:
 *     define( 'ENABLE_FORCE_CHECK_UPDATE', true );
 *
 * idea: https://github.com/Websiteguy/disable-updates-manager/
 * idea: http://wordpress.org/plugins/no-browser-nag/
 */

class Disable_Version_Check_MU {

    private $not_update_core_action = true;
    private $not_update_action = true;

    public function __construct() {

        // don't block updates on the frontend
        // block updates during WP-Cron
        $doing_cron = ( defined( 'DOING_CRON' ) && DOING_CRON );
        if ( ! ( is_admin() || $doing_cron ) )
            return;

        // don't block updates when "Check again" is pressed
        if ( defined( 'ENABLE_FORCE_CHECK_UPDATE' ) && ENABLE_FORCE_CHECK_UPDATE ) {

            $called_script = isset( $_SERVER['SCRIPT_FILENAME'] ) ? basename( $_SERVER['SCRIPT_FILENAME'] ) : '';
            $is_update_core = ( 'update-core.php' === $called_script );
            $is_update = ( 'update.php' === $called_script );

            if ( $is_update_core && ! empty( $_GET['force-check'] ) )
                return;

            $this->not_update_core_action = ( ! $is_update_core || empty( $_GET['action'] ) );
            $this->not_update_action = ( ! $is_update || empty( $_GET['action'] ) );
        }

        $this->disable_core_updates();
        $this->disable_theme_updates();
        $this->disable_plugin_updates();
        $this->disable_browser_nag();
    }

    public function last_checked() {

        global $wp_version;

        return (object) array(
            'last_checked'    => time(),
            'updates'         => array(),
            'version_checked' => $wp_version,
        );
    }

    public function updated_browser() {

        // wp-admin/includes/dashboard.php:1254
        $key = md5( $_SERVER['HTTP_USER_AGENT'] );
        add_filter( 'site_transient_browser_' . $key, '__return_true' );
    }

    private function disable_core_updates() {

        // wp-includes/update.php:156
        if ( $this->not_update_core_action )
            add_filter( 'pre_site_transient_update_core', array( $this, 'last_checked' ) );
        // wp-includes/update.php:632-633
        remove_action( 'admin_init', '_maybe_update_core' );
        remove_action( 'wp_version_check', 'wp_version_check' );
    }

    private function disable_plugin_updates() {

        // wp-includes/update.php:308
        if ( $this->not_update_core_action && $this->not_update_action )
            add_filter( 'pre_site_transient_update_plugins', array( $this, 'last_checked' ) );
        // wp-includes/update.php:636-640
        remove_action( 'load-plugins.php', 'wp_update_plugins' );
        remove_action( 'load-update.php', 'wp_update_plugins' );
        remove_action( 'load-update-core.php', 'wp_update_plugins' );
        remove_action( 'admin_init', '_maybe_update_plugins' );
        remove_action( 'wp_update_plugins', 'wp_update_plugins' );
    }

    private function disable_theme_updates() {

        // wp-includes/update.php:453
        if ( $this->not_update_core_action && $this->not_update_action )
            add_filter( 'pre_site_transient_update_themes', array( $this, 'last_checked' ) );
        // wp-includes/update.php:643-647
        remove_action( 'load-themes.php', 'wp_update_themes' );
        remove_action( 'load-update.php', 'wp_update_themes' );
        remove_action( 'load-update-core.php', 'wp_update_themes' );
        remove_action( 'admin_init', '_maybe_update_themes' );
        remove_action( 'wp_update_themes', 'wp_update_themes' );
    }

    private function disable_browser_nag() {

        if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) return false;

        add_action( 'admin_init', array( $this, 'updated_browser' ) );
    }

}

new Disable_Version_Check_MU();

