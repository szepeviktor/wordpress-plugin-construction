<?php
/*
Plugin Name: Nofollow Robot Trap
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Catch malicious robots not obeying nofollow meta tag/attribute
Version: 0.1
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
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
 * Catch malicious robots
 *
 * 1. Add this line to your style.css:
 *
 * .nfrt { display: none !important; }
 *
 * 2. Add the allow page and the nofollow page to your sitemap.
 *
 * Bait pages and links
 *  - invisible link on the front page: allow page
 *  - allow page links to:
 *      - nofollow page
 *      - rel=nofollow block URL
 *      - protocol relative URL
 *  - nofollow (meta tag) page links to: block URL
 *  - robots.txt:
 *      - Disallow: block URL
 *      - Allow: allow page
 *      - Allow: nofollow page
 *  - sitemap item:
 *      - allow page
 *      - nofollow page
 *  - the immediate block URL
 */

class NofollowTrap {

    private $version = '0.1';
    private $activation;

    private $prefix = 'File does not exist: ';
    private $trigger_count = 6;

    private $block_url;
    private $allow_url;
    private $nofollow_url;
    private $protocol_relative_url;

    private $hide_class;
    private $anchor_text;

    public function __construct() {

        // Must-Use plugins don't have activation
        //register_activation_hook( __FILE__, array( $this, 'activate' ) );

        // generate URLs
        /*$sprintf('%u', crc32( get_bloginfo( 'url' ) ) );
        defined();
        // options-general.php fieldset
        get_option();*/

        $this->block_url = 'disallow/';
        $this->allow_url = 'allow/';
        $this->nofollow_url = 'nofollow/';
        $this->protocol_relative_url = '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js';

        $this->hide_class = 'nfrt';
        $this->anchor_text = '&nbsp;';

        // setup - also on admin
        add_action( 'init', array( $this, 'register_urls' ) );

        // frontend only
        if ( is_admin() )
            return;

        // add lines to robots.txt
        add_filter('robots_txt', array( $this, 'robotstxt_disallow' ), 2, 1);
        // add the hidden link to the front page
        add_action('wp_footer', array( $this, 'add_allow_url' ), 100 );
        // generate output or block
        add_action( 'template_redirect', array( $this, 'generate_pages' ) );
        // detect protocol relative URL
        add_filter( 'redirect_canonical', array( $this, 'protocol_relative' ), 1, 2 );
    }

    public function robotstxt_disallow( $output ) {

        $output .= sprintf( "\nUser-agent: *\nDisallow: %s\nAllow: %s\nAllow: %s\n",
            home_url( $this->block_url, 'relative' ),
            home_url( $this->allow_url, 'relative' ),
            home_url( $this->nofollow_url, 'relative' )
        );

        return $output;
    }

    public function add_allow_url() {

        if ( ! is_front_page() )
            return;

        printf ( '<div class="%s"><a href="%s">%s</a></div>%s',
            $this->hide_class,
            home_url( $this->allow_url ),
            $this->anchor_text,
            PHP_EOL
        );
    }

    public function register_urls() {

        $this->activation = get_site_option( 'nfrt_activate' );

        // permit missing trailing slash
        add_rewrite_rule( '^' . $this->block_url . '?$', 'index.php?nfrt=block', 'top' );
        add_rewrite_rule( '^' . $this->allow_url . '?$', 'index.php?nfrt=allow', 'top' );
        add_rewrite_rule( '^' . $this->nofollow_url . '?$', 'index.php?nfrt=nofollow', 'top' );

        // Rewrite Api cannot handle this
        // see: function protocol_relative() below
        //add_rewrite_rule( '^' . preg_quote( $this->protocol_relative_url ) . '$', 'index.php?nfrt=relprot', 'top' );

        add_rewrite_tag( '%nfrt%', '(block|allow|nofollow)');

        // flush rules on first run
        if ( ! $this->activation || $this->activation !== $this->version )
            // flush at shutdown to be safe
            add_action( 'shutdown', array( $this, 'activate' ) );

    }

    public function activate() {

        update_site_option( 'nfrt_activate', $this->version );
        flush_rewrite_rules();
    }

    public function generate_pages() {

        $nfrt = get_query_var( 'nfrt' );
        // for speed
        if ( empty ( $nfrt ) )
            return;

        switch ( $nfrt ) {
            case 'block':
                $this->trigger();
                exit();

            case 'allow':
                $this->generate_allow_page();
                exit();

            case 'nofollow':
                $this->generate_nofollow_page();
                exit();
        }
    }

    public function protocol_relative( $redirect_url, $requested_url ) {

        if ( $this->str_endswith( $requested_url, $this->protocol_relative_url ) )
            $this->trigger();

        return $redirect_url;
    }

    private function generate_allow_page() {
        status_header( 200 );
        header( 'Content-Type: text/html; charset=utf-8' );
        // prevent indexing
        header( 'X-Robots-Tag: noindex,follow', true );

        printf( '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title> </title>
    <meta name="robots" content="noindex">
    <meta http-equiv="refresh" content="1; url=%s">
    <style type="text/css">body{background:white} a{color:white}</style>
</head>
<body>
    <a href="%s">%s</a>
    <a rel="nofollow" href="%s">%s</a>
    <a href="%s">%s</a>
</body>
</html>',
            home_url( '/' ),
            home_url( $this->nofollow_url ),
            $this->anchor_text,
            home_url( $this->block_url ),
            $this->anchor_text,
            $this->protocol_relative_url,
            $this->anchor_text
        );
    }

    public function generate_nofollow_page() {
        status_header( 200 );
        header( 'Content-Type: text/html; charset=utf-8' );
        // prevent indexing
        header( 'X-Robots-Tag: noindex,nofollow', true );

        printf( '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title> </title>
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="refresh" content="1; url=%s">
    <style type="text/css">body{background:white} a{color:white}</style>
</head>
<body>
    <a href="%s">%s</a>
</body>
</html>',
            home_url( '/' ),
            home_url( $this->block_url ),
            $this->anchor_text
        );
    }

    private function trigger() {
        // trigger fail2ban
        for ( $i = 0; $i < $this->trigger_count; $i++ ) {
            error_log( $this->prefix  . 'nofollow_robot_trap' );
        }

        ob_get_level() && ob_end_clean();
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.0 403 Forbidden' );
        exit();
    }

    private function str_endswith( $haystack, $needle ) {
        return strpos( $haystack, $needle ) + strlen( $needle ) === strlen( $haystack );
    }
}

new NofollowTrap();

/*TODO
- trap type: fail2ban, .htaccess, nginx.conf, CloudFlare API, call itsec
- set cookie for robots -> measure next request frequency -> log
- different traps?? for: rel nofollow, robots meta, robots.txt, realtive protocol

*/
