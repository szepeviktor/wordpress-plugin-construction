<?php
/*
Plugin Name: Attack logging for fail2ban
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Reports 404s and various attacks in error.log for fail2ban
Version: 0.9
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
*/

if ( ! defined( 'ABSPATH' ) ) {
    error_log( 'File does not exist: errorlog_direct_access ' . $_SERVER['REQUEST_URI'] );
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class O1_ErrorLog404 {

    private $prefix;

    public function __construct() {

        // admin
        if ( is_admin() ) {
            require_once dirname( __FILE__ ) . '/inc/errorlog-404-admin.php';
            $errorlog_404_admin = new O1_Errorlog_404_admin();
        }

        // admin_init() does it register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        //TODO uninstall hook/file

        $general_options = get_option( 'o1_errorlog_general' );
        $request_options = get_option( 'o1_errorlog_request' );
        $login_options = get_option( 'o1_errorlog_login' );

        if ( '0' === $general_options['enabled'] )
            return;

        $this->prefix = $general_options['prefix'] . ' ';

        // non-existent / malicious URLs
        if ( 1 == $request_options['fourohfour'] ) {
            add_action( 'template_redirect', array( $this, 'wp_404' ) );
        }
        if ( 1 == $request_options['urlhack'] ) {
            add_action( 'init', array( $this, 'url_hack' ) );
        }
        if ( 1 == $request_options['redirect'] ) {
            add_filter( 'redirect_canonical', array( $this, 'redirect' ), 1, 2 );
        }

        // don't show 404 for robots
        if ( 1 == $request_options['robot404'] ) {
            add_action( 'plugins_loaded', array( $this, 'robot_404' ), 0 );
        }

        // don't redirect to admin
        if ( 1 == $login_options['adminredirect'] ) {
            remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
        }

        // login failures
        if ( 1 == $login_options['loginfail'] ) {
            add_action( 'wp_login_failed', array( $this, 'login_failed' ) );
        }

        // successful login
        if ( 1 == $login_options['login'] ) {
            add_action( 'wp_login', array( $this, 'login' ) );
        }

        // logout
        if ( 1 == $login_options['logout'] ) {
            add_action( 'wp_logout', array( $this, 'logout' ) );
        }

        // report bailouts for security reasons
        if ( 1 == $login_options['wpdie'] ) {
            add_filter( 'wp_die_ajax_handler', array( $this, 'wp_die' ) );
            add_filter( 'wp_die_xmlrpc_handler', array( $this, 'wp_die' ) );
            add_filter( 'wp_die_handler', array( $this, 'wp_die' ) );
        }

    }

    public function wp_404() {

        if ( is_404() ) {
            $request_uri = $_SERVER['REQUEST_URI'];

            error_log( $this->prefix . 'errorlog_404 ' . $request_uri );
        }
    }

    public function url_hack() {

        $request_uri = $_SERVER['REQUEST_URI'];

        if ( substr( $request_uri, 0, 2 ) === '//'
            || strstr( $request_uri, '../' ) !== false
            || strstr( $request_uri, '/..' ) !== false )
            error_log( $this->prefix . 'errorlog_url_hack ' . $request_uri );
    }

    public function redirect( $redirect_url, $requested_url ) {

        error_log( $this->prefix . 'errorlog_redirect ' . $requested_url );
        return $redirect_url;
    }

    public function login_failed() {

        error_log( $this->prefix . 'errorlog_login_failed' );
    }

    public function login( $username ) {

        error_log( 'WordPress logged in: ' . $username );
    }

    public function logout() {

        $current_user = wp_get_current_user();

        error_log( 'WordPress logout: ' . $current_user->user_login );
    }

    public function wp_die( $arg ) {

        if ( did_action( 'wp_ajax_heartbeat' ) )
            return $arg;

        error_log( $this->prefix . 'errorlog_wpdie' );
        return $arg;
    }

    public function robot_404() {

        $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $request_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
        $admin_path = parse_url( admin_url(), PHP_URL_PATH );
        $content_dir = basename( WP_CONTENT_DIR );

        if ( ! is_user_logged_in()
            // a robot or < IE8
            && ( 'Mozilla/5.0' !== substr( $ua, 0, 11 ) )
            && ( 'Mozilla/4.0 (compatible; MSIE 8.0;' !== substr( $ua, 0, 34 ) )
            // only in WP: wp-admin, wp-includes, wp-content
            && 1 === preg_match( '/\/(wp-admin|wp-includes|' . $content_dir . ')\//', $request_path )
            // make sure the file does not exist
            /*&& ! file_exists( $_SERVER['SCRIPT_FILENAME'] )*/ ) {

            error_log( $this->prefix . 'errorlog_robot404' );
            header( 'Status: 404 Not Found' );
            header( 'HTTP/1.1 404 Not Found' );
            exit();
        }
    }

    public function deactivate() {

        // clean up options
        delete_option( 'o1_errorlog_general' );
        delete_option( 'o1_errorlog_request' );
        delete_option( 'o1_errorlog_login' );
    }

}

new O1_ErrorLog404();

