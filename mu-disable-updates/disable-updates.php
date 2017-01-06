<?php
/*
Plugin Name: Disable Updates and Update HTTP Requests (MU)
Version: 0.5.4
Description: Disable core, theme and plugin updates plus the browser nag
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Options: ENABLE_FORCE_CHECK_UPDATE
*/

// @FIXME https://core.trac.wordpress.org/ticket/30855#ticket
//        wp_get_update_data() calls are not pluggable (wp-admin/menu.php ×2)

/**
 * Disable Updates and Update HTTP Requests
 *
 * This is a one-class mu-plugin.
 * Enable force-check updates by copying this to wp-config.php:
 *
 *     define( 'ENABLE_FORCE_CHECK_UPDATE', true );
 *
 * @package disable-updates
 * @author Viktor Szépe <viktor@szepe.net>
 * @link https://github.com/szepeviktor/wordpress-plugin-construction
 */

if ( ! function_exists( 'add_filter' ) ) {
    error_log( 'Break-in attempt detected: disable_updates_direct_access '
        . addslashes( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' )
    );
    ob_get_level() && ob_end_clean();
    if ( ! headers_sent() ) {
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.1 403 Forbidden', true, 403 );
        header( 'Connection: Close' );
    }
    exit;
}

/**
 * Disable core, theme and plugin updates plus the browser nag
 *
 * @link https://github.com/Websiteguy/disable-updates-manager/
 * @link http://wordpress.org/plugins/no-browser-nag/
 */
class O1_Disable_Version_Check {

    /**
     * Prevent core updates
     * @var bool|true
     */
    private $disable_update_core_action = true;
    /**
     * Prevent plugin and theme updates
     * @var bool|true
     */
    private $disable_update_action = true;

    /**
     * Disable all types of updates after checking for WP-cron
     */
    public function __construct() {

        // Don't block updates when "Check again" is pressed
        if ( defined( 'ENABLE_FORCE_CHECK_UPDATE' ) && ENABLE_FORCE_CHECK_UPDATE ) {

            $called_script = isset( $_SERVER['SCRIPT_FILENAME'] ) ? basename( $_SERVER['SCRIPT_FILENAME'] ) : '';
            $is_update_core = ( 'update-core.php' === $called_script );
            $is_update = ( 'update.php' === $called_script );

            if ( $is_update_core && ! empty( $_GET['force-check'] ) ) {
                return;
            }

            // Allow actual updates
            $this->disable_update_core_action = ( ! $is_update_core || empty( $_GET['action'] ) );
            $this->disable_update_action = ( ! $is_update || empty( $_GET['action'] ) );
        }

        // Would show up on the frontend
        add_action( 'add_admin_bar_menus', array( $this, 'disable_admin_bar_updates_menu' ) );

        // Don't block updates on the frontend, block updates during WP-Cron
        $doing_cron = ( defined( 'DOING_CRON' ) && DOING_CRON );
        if ( ! ( is_admin() || $doing_cron ) ) {
            return;
        }

        $this->disable_core_updates();
        $this->disable_theme_updates();
        $this->disable_plugin_updates();
        $this->disable_browser_nag();
    }

    /**
     * Prevent core updates
     *
     * @see last_checked_core() for the returned value
     */
    private function disable_core_updates() {

        // wp-includes/update.php:156
        if ( $this->disable_update_core_action ) {
            // Prevent HTTP requests too
            if ( isset( $_GET['force-check'] ) ) {
                unset( $_GET['force-check'] );
            }
            add_filter( 'pre_site_transient_update_core', array( $this, 'last_checked_core' ) );
        }
        // wp-includes/update.php:677-678
        remove_action( 'admin_init', '_maybe_update_core' );
        remove_action( 'wp_version_check', 'wp_version_check' );
    }

    /**
     * Prevent theme updates
     *
     * @see last_checked_themes() for the returned value
     */
    private function disable_theme_updates() {

        // wp-includes/update.php:479
        if ( $this->disable_update_core_action && $this->disable_update_action ) {
            add_filter( 'pre_site_transient_update_themes', array( $this, 'last_checked_themes' ) );
        }
        // wp-includes/update.php:688-692
        remove_action( 'load-themes.php', 'wp_update_themes' );
        remove_action( 'load-update.php', 'wp_update_themes' );
        remove_action( 'load-update-core.php', 'wp_update_themes' );
        remove_action( 'admin_init', '_maybe_update_themes' );
        remove_action( 'wp_update_themes', 'wp_update_themes' );
    }

    /**
     * Prevent plugin updates
     *
     * @see last_checked_plugins() for the returned value
     */
    private function disable_plugin_updates() {

        // wp-includes/update.php:327
        if ( $this->disable_update_core_action && $this->disable_update_action ) {
            add_filter( 'pre_site_transient_update_plugins', array( $this, 'last_checked_plugins' ) );
        }
        // wp-includes/update.php:681-685
        remove_action( 'load-plugins.php', 'wp_update_plugins' );
        remove_action( 'load-update.php', 'wp_update_plugins' );
        remove_action( 'load-update-core.php', 'wp_update_plugins' );
        remove_action( 'admin_init', '_maybe_update_plugins' );
        remove_action( 'wp_update_plugins', 'wp_update_plugins' );
    }

    /**
     * Prevent browser check
     *
     * @see updated_browser() for the site_transient hook
     */
    private function disable_browser_nag() {

        if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
            return;
        }

        add_action( 'admin_init', array( $this, 'updated_browser' ) );
    }

    /**
     * Remove the updates menu from the admin bar
     */
    public function disable_admin_bar_updates_menu() {

        // wp-includes/class-wp-admin-bar.php:499
        if ( $this->disable_update_core_action ) {
            // Line 40 -> 50 in WP 4.3.1
            remove_action( 'admin_bar_menu', 'wp_admin_bar_updates_menu', 50 );
        }
    }

    /**
     * Return the current time and core version
     */
    public function last_checked_core( $transient ) {

        return (object) array(
            'last_checked'    => time(),
            'updates'         => array(),
            'version_checked' => get_bloginfo( 'version' ),
        );
    }

    /**
     * Return the current time and theme versions
     */
    public function last_checked_themes( $transient ) {

        $current = array();
        $installed_themes = wp_get_themes();
        foreach ( $installed_themes as $theme ) {
            $current[ $theme->get_stylesheet() ] = $theme->get( 'Version' );
        }

        return (object) array(
            'last_checked' => time(),
            'updates'      => array(),
            'checked'      => $current,
        );
    }

    /**
     * Return the current time and plugin versions
     */
    public function last_checked_plugins( $transient ) {

        $current = array();
        $plugins = get_plugins();
        foreach ( $plugins as $file => $plugin ) {
            $current[ $file ] = $plugin['Version'];
        }

        return (object) array(
            'last_checked' => time(),
            'updates'      => array(),
            'checked'      => $current,
        );
    }

    /**
     * Hook the transient of the current user's browser
     */
    public function updated_browser() {

        // wp-admin/includes/dashboard.php:1260
        $key = md5( $_SERVER['HTTP_USER_AGENT'] );
        add_filter( 'site_transient_browser_' . $key, '__return_true' );
    }
}

new O1_Disable_Version_Check();
