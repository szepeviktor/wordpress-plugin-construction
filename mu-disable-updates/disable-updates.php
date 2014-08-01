<?php
/*
  Plugin Name: Disable Updates and update HTTP requests
  Plugin URI: http://www.online1.hu/
  Description: Disable core, theme and plugin updates plus the browser nag
  Version: 0.2
  Author: Viktor Szépe
*/

if ( ! function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

// mostly from https://github.com/Websiteguy/disable-updates-manager/
// and http://wordpress.org/plugins/no-browser-nag/

class Disable_Version_Check_MU {

    public function __construct() {
        // don't block updates on the frontend
        // block updates during WP-Cron
        // don't block updates when "Check again" is pressed
        if ( ! ( ( is_admin() && empty( $_GET['force-check'] ) ) || ( defined('DOING_CRON') && DOING_CRON ) ) )
            return false;

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
        add_filter( 'pre_site_transient_update_core', array( $this, 'last_checked' ) );
        // wp-includes/update.php:632-633
        remove_action( 'admin_init', '_maybe_update_core' );
        remove_action( 'wp_version_check', 'wp_version_check' );
    }

    private function disable_plugin_updates() {

        // wp-includes/update.php:308
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
        add_filter( 'pre_site_transient_update_themes', array( $this, 'last_checked' ) );
        // wp-includes/update.php:643-647
        remove_action( 'load-themes.php', 'wp_update_themes' );
        remove_action( 'load-update.php', 'wp_update_themes' );
        remove_action( 'load-update-core.php', 'wp_update_themes' );
        remove_action( 'admin_init', '_maybe_update_themes' );
        remove_action( 'wp_update_themes', 'wp_update_themes' );
    }

    private function disable_browser_nag() {
        if (! isset( $_SERVER['HTTP_USER_AGENT'] ) ) return false;

        add_action( 'admin_init', array( $this, 'updated_browser' ) );
    }

}

new Disable_Version_Check_MU();

