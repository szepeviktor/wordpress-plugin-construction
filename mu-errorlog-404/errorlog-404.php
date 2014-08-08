<?php
/*
Plugin Name: Attack logging for fail2ban
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Reports 404s and various attacks in error.log for fail2ban
Version: 2.4
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
*/

if ( ! function_exists( 'add_filter' ) ) {
    error_log( 'File does not exist: errorlog_direct_access ' . $_SERVER['REQUEST_URI'] );
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class ErrorLog404_MU {

    private $prefix = 'File does not exist: ';

    public function __construct() {

        // don't redirect to admin
        remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );

        // login failures
        add_action( 'wp_login_failed', array( $this, 'login_failed' ) );

        // non-existent URLs
        add_action( 'init', array( $this, 'url_hack' ) );
        add_filter( 'redirect_canonical', array( $this, 'redirect' ), 1, 2 );
        add_action( 'template_redirect', array( $this, 'wp_404' ) );

        // bailouts for security reasons
        add_filter( 'wp_die_ajax_handler', array( $this, 'wp_die' ) );
        add_filter( 'wp_die_xmlrpc_handler', array( $this, 'wp_die' ) );
        add_filter( 'wp_die_handler', array( $this, 'wp_die' ) );

        // ban spammers (Contact Form 7 Robot Trap)
        add_action( 'robottrap_hiddenfield', array( $this, 'wpcf7_spam' ) );
        add_action( 'robottrap_mx', array( $this, 'wpcf7_spam_mx' ) );

    }

    public function wp_404() {

        if ( is_404() )
            error_log( $this->prefix . 'errorlog_404 ' . $_SERVER['REQUEST_URI'] );
    }

    public function url_hack() {

        $req_uri = $_SERVER['REQUEST_URI'];
        if ( substr( $req_uri, 0, 2 ) === '//'
            || strstr( $req_uri, '../' ) !== false
            || strstr( $req_uri, '/..' ) !== false )
            error_log( $this->prefix . 'errorlog_url_hack ' . $req_uri );
    }

    public function redirect( $redirect_url, $requested_url ) {

        error_log( $this->prefix . 'errorlog_redirect ' . $_SERVER['REQUEST_URI'] );
        return $redirect_url;
    }

    public function login_failed() {

        error_log( $this->prefix . 'errorlog_login_failed' );
    }

    public function wp_die( $arg ) {

        error_log( $this->prefix . 'errorlog_wpdie' );
        return $arg;
    }

    public function wpcf7_spam( $text ) {

        error_log( $this->prefix . 'errorlog_wpcf7_spam' . ' (' . $text . ')' );
    }

    public function wpcf7_spam_mx( $domain ) {

            error_log( $this->prefix . 'errorlog_wpcf7_spam_mx' . ' (' . $domain . ')' );
    }

}

new ErrorLog404_MU();

