<?php
/*
Plugin Name: Disable Updates and Update HTTP Requests
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Disable core, theme and plugin updates plus the browser nag
Version: 0.5
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-disable-updates
*/

//FIXME: https://core.trac.wordpress.org/ticket/30855#ticket
// wp_get_update_data() calls are not pluggable (wp-admin/menu.php ×2)

/**
 * Disable Updates and Update HTTP Requests.
 * This is a one-class mu-plugin
 *
 * Enable force-check updates by adding a define to wp-config:
 * <code>
 * define( 'ENABLE_FORCE_CHECK_UPDATE', true );
 * </code>
 *
 * @package disable-updates
 * @version v0.4
 * @author Viktor Szépe <viktor@szepe.net>
 * @link https://github.com/szepeviktor/wordpress-plugin-construction
 */

if ( ! function_exists( 'add_filter' ) ) {
    // for fail2ban
    error_log( 'File does not exist: errorlog_direct_access '
        . esc_url( $_SERVER['REQUEST_URI'] ) );

    ob_get_level() && ob_end_clean();
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

/**
 * Disable core, theme and plugin updates plus the browser nag.
 *
 * @link https://github.com/Websiteguy/disable-updates-manager/
 * @link http://wordpress.org/plugins/no-browser-nag/
 */
class Disable_Version_Check_MU {

    /**
     * Prevent core updates.
     * @var bool|true
     */
    private $disable_update_core_action = true;
    /**
     * Prevent plugin and theme updates.
     * @var bool|true
     */
    private $disable_update_action = true;

    /**
     * Disable all types of updates after checking for WP-cron.
     */
    public function __construct() {

        // don't block updates when "Check again" is pressed
        if ( defined( 'ENABLE_FORCE_CHECK_UPDATE' ) && ENABLE_FORCE_CHECK_UPDATE ) {

            $called_script = isset( $_SERVER['SCRIPT_FILENAME'] ) ? basename( $_SERVER['SCRIPT_FILENAME'] ) : '';
            $is_update_core = ( 'update-core.php' === $called_script );
            $is_update = ( 'update.php' === $called_script );

            if ( $is_update_core && ! empty( $_GET['force-check'] ) )
                return;

            // allow actual updates
            $this->disable_update_core_action = ( ! $is_update_core || empty( $_GET['action'] ) );
            $this->disable_update_action = ( ! $is_update || empty( $_GET['action'] ) );
        }

        // would show up on the frontend
        add_action( 'add_admin_bar_menus', array( $this, 'disable_admin_bar_updates_menu' ) );

        // don't block updates on the frontend
        // block updates during WP-Cron
        $doing_cron = ( defined( 'DOING_CRON' ) && DOING_CRON );
        if ( ! ( is_admin() || $doing_cron ) )
            return;

        $this->disable_core_updates();
        $this->disable_theme_updates();
        $this->disable_plugin_updates();
        $this->disable_browser_nag();
    }

    /**
     * Prevent core updates.
     * @see last_checked_core() for the returned value
     */
    private function disable_core_updates() {

        // wp-includes/update.php:156
        if ( $this->disable_update_core_action ) {
            // prevent HTTP request too
            if ( isset( $_GET['force-check'] ) )
                unset( $_GET['force-check'] );
            add_filter( 'pre_site_transient_update_core', array( $this, 'last_checked_core' ) );
        }
        // wp-includes/update.php:677-678
        remove_action( 'admin_init', '_maybe_update_core' );
        remove_action( 'wp_version_check', 'wp_version_check' );
    }

    /**
     * Prevent theme updates.
     * @see last_checked_themes() for the returned value
     */
    private function disable_theme_updates() {

        // wp-includes/update.php:479
        if ( $this->disable_update_core_action && $this->disable_update_action )
            add_filter( 'pre_site_transient_update_themes', array( $this, 'last_checked_themes' ) );
        // wp-includes/update.php:688-692
        remove_action( 'load-themes.php', 'wp_update_themes' );
        remove_action( 'load-update.php', 'wp_update_themes' );
        remove_action( 'load-update-core.php', 'wp_update_themes' );
        remove_action( 'admin_init', '_maybe_update_themes' );
        remove_action( 'wp_update_themes', 'wp_update_themes' );
    }

    /**
     * Prevent plugin updates.
     * @see last_checked_plugins() for the returned value
     */
    private function disable_plugin_updates() {

        // wp-includes/update.php:327
        if ( $this->disable_update_core_action && $this->disable_update_action )
            add_filter( 'pre_site_transient_update_plugins', array( $this, 'last_checked_plugins' ) );
        // wp-includes/update.php:681-685
        remove_action( 'load-plugins.php', 'wp_update_plugins' );
        remove_action( 'load-update.php', 'wp_update_plugins' );
        remove_action( 'load-update-core.php', 'wp_update_plugins' );
        remove_action( 'admin_init', '_maybe_update_plugins' );
        remove_action( 'wp_update_plugins', 'wp_update_plugins' );
    }

    /**
     * Prevent browser check.
     * @see updated_browser() for the site_transient hook
     */
    private function disable_browser_nag() {

        if ( empty( $_SERVER['HTTP_USER_AGENT'] ) )
            return;

        add_action( 'admin_init', array( $this, 'updated_browser' ) );
    }

    /**
     * Remove the updates menu from the admin bar.
     */
    public function disable_admin_bar_updates_menu() {

        // wp-includes/class-wp-admin-bar.php:499
        if ( $this->disable_update_core_action )
            remove_action( 'admin_bar_menu', 'wp_admin_bar_updates_menu', 40 );
    }

    /**
     * Return the current time and WordPress version.
     */
    public function last_checked_core() {

        return (object) array(
            'last_checked'    => time(),
            'updates'         => array(),
            'version_checked' => get_bloginfo( 'version' )
        );
    }

    /**
     * Return the current time and theme versions.
     */
    public function last_checked_themes() {

        $current = array();
        $installed_themes = wp_get_themes();
        foreach ( $installed_themes as $theme )
            $current[$theme->get_stylesheet()] = $theme->get( 'Version' );

        return (object) array(
            'last_checked'    => time(),
            'updates'         => array(),
            'checked'         => $current
        );
    }

    /**
     * Return the current time and plugin versions.
     */
    public function last_checked_plugins() {

        $current = array();
        $plugins = get_plugins();
        foreach ( $plugins as $file => $p )
            $current[$file] = $p['Version'];

        return (object) array(
            'last_checked'    => time(),
            'updates'         => array(),
            'checked'         => $current
        );
    }

    /**
     * Hook the transient of the current user's browser.
     */
    public function updated_browser() {

        // wp-admin/includes/dashboard.php:1260
        $key = md5( $_SERVER['HTTP_USER_AGENT'] );
        add_filter( 'site_transient_browser_' . $key, '__return_true' );
    }
}

new Disable_Version_Check_MU();
