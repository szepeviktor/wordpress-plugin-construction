<?php
/*
Plugin Name: WordPress fail2ban
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Reports 404s and various attacks for fail2ban
Version: 0.9
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
*/

if ( ! defined( 'ABSPATH' ) ) {
    error_log( 'File does not exist: errorlog_direct_access ' . esc_url( $_SERVER['REQUEST_URI'] ) );
    ob_end_clean();
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class O1_ErrorLog404 {

    private $prefix = 'File does not exist: ';
    private $human_notfound = false;
    private $robot_notfound = false;
    private $wp_die_ajax_handler;
    private $wp_die_xmlrpc_handler;
    private $wp_die_handler;

    private function esc_log( $string ) {

        $string = serialize( $string ) ;
        // trim long data
        $string = mb_substr( $string, 0, 200, 'utf-8' );
        // replace non-printables with "¿" - sprintf( '%c%c', 194, 191 )
        $string = preg_replace( '/[^\P{C}]+/u', "\xC2\xBF", $string );

        return ' (' . $string . ')';
    }

    public function __construct() {

        // admin
        if ( is_admin() ) {
            require_once dirname( __FILE__ ) . '/inc/errorlog-404-admin.php';
            $errorlog_404_obj = new O1_Errorlog_404();
        }

        // admin_init() does it register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        $general_options = get_option( 'o1_errorlog_general' );
        $request_options = get_option( 'o1_errorlog_request' );
        $login_options = get_option( 'o1_errorlog_login' );
        $spam_options = get_option( 'o1_errorlog_spam' );

        if ( '0' === $general_options['enabled'] )
            return;

        $this->prefix = $general_options['prefix'] . ' ';

        // 404
        $this->robot_notfound = ( 1 == $request_options['robot404'] );
        $this->human_notfound = ( 1 == $request_options['fourohfour'] );

        if ( $this->robot_notfound || $this->human_notfound ) {
            add_action( 'template_redirect', array( $this, 'wp_404' ) );
        }

        // non-existent / malicious URLs
        if ( 1 == $request_options['urlhack'] ) {
            add_action( 'init', array( $this, 'url_hack' ) );
        }
        if ( 1 == $request_options['redirect'] ) {
            add_filter( 'redirect_canonical', array( $this, 'redirect' ), 1, 2 );
        }

        // robots seeking for exploits
        if ( 1 == $request_options['robot403'] ) {
            add_action( 'plugins_loaded', array( $this, 'robot_403' ), 0 );
        }

        // don't redirect to admin
        if ( 1 == $login_options['adminredirect'] ) {
            remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
        }

        // login failures
        if ( 1 == $login_options['loginfail'] ) {
            add_action( 'wp_login_failed', array( $this, 'login_failed' ) );
        }

        // login success
        if ( 1 == $login_options['login'] ) {
            add_action( 'wp_login', array( $this, 'login' ) );
        }

        // report bailouts for security reasons
        if ( 1 == $login_options['wpdie'] ) {
            add_filter( 'wp_die_ajax_handler', array( $this, 'wp_die_ajax' ), 1 );
            add_filter( 'wp_die_xmlrpc_handler', array( $this, 'wp_die_xmlrpc' ), 1 );
            add_filter( 'wp_die_handler', array( $this, 'wp_die' ), 1 );
        }

        // filled in the hidden field (Contact Form 7 Robot Trap)
        if ( 1 == $spam_options['hiddenfield'] ) {
            add_action( 'robottrap_hiddenfield', array( $this, 'wpcf7_spam' ) );
        }
        // provided domain name without MX (Contact Form 7 Robot Trap)
        if ( 1 == $spam_options['nomx'] ) {
            add_action( 'robottrap_mx', array( $this, 'wpcf7_spam_mx' ) );
        }
    }

    public function wp_404() {

        if ( ! is_404() )
            return;

        $request_uri = $_SERVER['REQUEST_URI'];

        // no 404 for robots
        if ( $this->robot_notfound
            && ! is_user_logged_in()
            && ( 'Mozilla/5.0' !== substr( $ua, 0, 11 ) )
            && ( 'Mozilla/4.0 (compatible; MSIE 8.0;' !== substr( $ua, 0, 34 ) ) ) {

            ob_end_clean();
            error_log( $this->prefix . 'errorlog_robot404' . $this->esc_log ( $request_uri ) );
            header( 'Status: 404 Not Found' );
            header( 'HTTP/1.0 404 Not Found' );
            exit();
        }

        // humans
        if ( $this->human_notfound )
            error_log( $this->prefix . 'errorlog_404' . $this->esc_log ( $request_uri ) );
    }

    public function url_hack() {

        $request_uri = $_SERVER['REQUEST_URI'];

        if ( substr( $request_uri, 0, 2 ) === '//'
            || strstr( $request_uri, '../' ) !== false
            || strstr( $request_uri, '/..' ) !== false )

            error_log( $this->prefix . 'errorlog_url_hack' . $this->esc_log ( $request_uri ) );
    }

    public function redirect( $redirect_url, $requested_url ) {

        error_log( $this->prefix . 'errorlog_redirect' . $this->esc_log ( $requested_url ) );
        return $redirect_url;
    }

    public function login_failed( $username ) {

        error_log( $this->prefix . 'errorlog_login_failed' . $this->esc_log( $username ) );
    }

    public function login( $username ) {

        error_log( 'WordPress logged in: ' . $username );
    }

    public function wp_die_ajax( $arg ) {

        // remember the previous handler
        $this->wp_die_ajax_handler = $arg;

        return array( $this, 'wp_die_ajax_handler' );
    }

    public function wp_die_ajax_handler( $message, $title, $args ) {

        // wp-admin/includes/ajax-actions.php returns -1 of security breach
        if ( ! is_int( $message ) || (int) $message < 0 )
            error_log( $this->prefix . 'errorlog_wpdie_ajax' );

        // call previous handler
        call_user_func( $this->wp_die_ajax_handler, $message, $title, $args );
    }

    public function wp_die_xmlrpc( $arg ) {

        // remember the previous handler
        $this->wp_die_xmlrpc_handler = $arg;

        return array( $this, 'wp_die_xmlrpc_handler' );
    }

    public function wp_die_xmlrpc_handler( $message, $title, $args ) {

        if ( ! empty( $message ) )
            error_log( $this->prefix . 'errorlog_wpdie_xmlrpc' . $this->esc_log( $message ) );

        // call previous handler
        call_user_func( $this->wp_die_xmlrpc_handler, $message, $title, $args );
    }

    public function wp_die( $arg ) {

        // remember the previous handler
        $this->wp_die_handler = $arg;

        return array( $this, 'wp_die_handler' );
    }

    public function wp_die_handler( $message, $title, $args ) {

        if ( ! empty( $message ) )
            error_log( $this->prefix . 'errorlog_wpdie' . $this->esc_log( $message ) );

        // call previous handler
        call_user_func( $this->wp_die_handler, $message, $title, $args );
    }

    public function robot_403() {

        $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $request_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
        $admin_path = parse_url( admin_url(), PHP_URL_PATH );
        $wp_dirs = 'wp-admin|wp-includes|' . basename( WP_CONTENT_DIR );

        if ( ! is_user_logged_in()
            // a robot or < IE8
            && ( 'Mozilla/5.0' !== substr( $ua, 0, 11 ) )
            && ( 'Mozilla/4.0 (compatible; MSIE 8.0;' !== substr( $ua, 0, 34 ) )

            // robots may only enter on the frontend: /index.php
            && 1 === preg_match( '/\/(' . $wp_dirs . ')\//i', $request_path )

            // XML RPC
            && ! ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )

            // trackback
            //FIXME any robot can add ?tb=1 to the request URI -> req_path conaints 'wp-trackback.php'
            && ! '1' === $GLOBALS['wp_query']->query_var['tb'] ) {

            //FIXME wp-includes/ms-files.php:12 ???
            ob_end_clean();
            error_log( $this->prefix . 'errorlog_robot403' . $this->esc_log ( $request_path ) );
            header( 'Status: 403 Forbidden' );
            header( 'HTTP/1.0 403 Forbidden' );
            exit();
        }
    }

    public function wpcf7_spam( $text ) {

        error_log( $this->prefix . 'errorlog_wpcf7_spam' . $this->esc_log( $text ) );
    }

    public function wpcf7_spam_mx( $domain ) {

        error_log( $this->prefix . 'errorlog_wpcf7_spam_mx' . $this->esc_log( $domain ) );
    }

    public function deactivate() {

        // clean up options
        delete_option( 'o1_errorlog_general' );
        delete_option( 'o1_errorlog_request' );
        delete_option( 'o1_errorlog_login' );
    }

}

new O1_ErrorLog404();

