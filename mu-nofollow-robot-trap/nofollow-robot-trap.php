<?php
/*
Plugin Name: Nofollow Robot Trap MU
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Catch malicious robots not obeying nofollow meta tag/attribute
Version: 0.4.0
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-nofollow-robot-trap
*/

if ( ! function_exists( 'add_filter' ) ) {
    error_log( 'Break-in attempt detected: wpf2b_mu_direct_access '
        . addslashes( @$_SERVER['REQUEST_URI'] )
    );
    ob_get_level() && ob_end_clean();
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden', true, 403 );
    header( 'Connection: Close' );
    exit();
}

/**
 * Catch malicious robots
 *
 * 1. Add the following line to your style.css.
 *
 *     .nfrt { display: none !important; }
 *
 * 2. Add the allow page and the nofollow page to your sitemap.
 *
 * 3. Optionally add cache exceptions for the four URLs.
 *
 * 4. Flush rules on deletion of this mu-plugin (wp rewrite flush).
 *
 * Bait pages and links
 *  - invisible link on the front page:
 *      - allow page
 *  - allow page links to:
 *      - nofollow page
 *      - rel=nofollow block URL
 *      - protocol relative URL
 *  - nofollow (meta tag) page links to:
 *      - block URL
 *  - robots.txt contains:
 *      - Disallow: block URL
 *      - Allow: allow page
 *      - Allow: nofollow page
 *  - sitemap contains:
 *      - allow page
 *      - nofollow page
 *  - the immediate block URL
 */

class O1_Nofollow_Robot_Trap {

    private $version = '0.4.0';

    private $prefix = 'Break-in attempt detected: ';

    private $block_url;
    private $allow_url;
    private $nofollow_url;
    private $protocol_relative_url;

    private $hide_class;
    private $anchor_text;

    public function __construct() {

        // Must-Use plugins don't have activation
        //register_activation_hook( __FILE__, array( $this, 'activate' ) );

        // Generate URL-s
        /*
        $sprintf('%u', crc32( get_bloginfo( 'url' ) ) . '1' ); 1 for block_url, 2 for allow_url ...
        defined();
        // options-general.php fieldset
        get_option();
        */

        $this->block_url = 'disallow/';
        $this->allow_url = 'allow/';
        $this->nofollow_url = 'nofollow/';
        $this->protocol_relative_url = '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js';

        $this->hide_class = 'nfrt';
        $this->anchor_text = '&nbsp;';

        // Setup - also on admin
        add_action( 'init', array( $this, 'register_urls' ) );
        // Add lines to robots.txt
        add_filter( 'robots_txt', array( $this, 'robotstxt_disallow' ), 2, 1 );

        // Frontend only
        if ( is_admin() ) {
            return;
        }

        // Add the hidden link to the front page
        add_action('wp_footer', array( $this, 'add_allow_url' ), 100 );
        // Generate output or block
        add_action( 'template_redirect', array( $this, 'generate_pages' ) );
        // Detect protocol relative URL
        add_filter( 'redirect_canonical', array( $this, 'protocol_relative' ), 1, 2 );
    }

    /**
     * Trigger fail2ban.
     */
    private function trigger() {

        error_log( $this->prefix  . 'nofollow_robot_trap' );

        ob_get_level() && ob_end_clean();
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.1 403 Forbidden' );
        header( 'Connection: Close' );
        header( 'Cache-Control: max-age=0, private, no-store, no-cache, must-revalidate' );
        header( 'X-Robots-Tag: noindex, nofollow' );
        header( 'Content-Type: text/html' );
        header( 'Content-Length: 0' );
        exit();
    }

    public function register_urls() {

        $activation = get_site_option( 'nfrt_activate' );

        // Permit missing trailing slash
        add_rewrite_rule( '^' . $this->block_url . '?$', 'index.php?nfrt=block', 'top' );
        add_rewrite_rule( '^' . $this->allow_url . '?$', 'index.php?nfrt=allow', 'top' );
        add_rewrite_rule( '^' . $this->nofollow_url . '?$', 'index.php?nfrt=nofollow', 'top' );

        // Rewrite API cannot handle this
        // See: protocol_relative() below
        //add_rewrite_rule( '^' . preg_quote( $this->protocol_relative_url ) . '$', 'index.php?nfrt=relprot', 'top' );

        add_rewrite_tag( '%nfrt%', '(block|allow|nofollow)');

        // Flush rules on first run
        if ( ! $activation || $activation !== $this->version ) {
            // Flush at shutdown to be safe
            add_action( 'shutdown', array( $this, 'activate' ) );
        }

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

        printf ( "<div class='%s'><a href='%s'>%s</a></div>%s\n",
            $this->hide_class,
            home_url( $this->allow_url ),
            $this->anchor_text
        );
    }

    public function generate_pages() {

        $nfrt = get_query_var( 'nfrt' );

        // For performance
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

        if ( $this->str_endswith( $requested_url, $this->protocol_relative_url ) ) {
            $this->trigger();
        }

        return $redirect_url;
    }

    private function generate_allow_page() {

        status_header( 200 );
        header( 'Content-Type: text/html; charset=utf-8' );
        // Prevent indexing
        header( 'X-Robots-Tag: noindex, follow', true );
        header( 'Cache-Control: max-age=0, private, no-store, no-cache, must-revalidate' );

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
        // Prevent indexing
        header( 'X-Robots-Tag: noindex, nofollow', true );
        header( 'Cache-Control: max-age=0, private, no-store, no-cache, must-revalidate' );

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

    public function activate() {

        update_site_option( 'nfrt_activate', $this->version );
        flush_rewrite_rules();
    }

    private function str_endswith( $haystack, $needle ) {

        return ( strpos( $haystack, $needle ) + strlen( $needle ) === strlen( $haystack ) );
    }
}

new O1_Nofollow_Robot_Trap();

/* @TODO

- add readme.md
- trap type: fail2ban, .htaccess, nginx.conf, CloudFlare API, call itsec
- set cookie for robots -> measure next request frequency -> log
- different traps?? for: rel nofollow, robots meta, robots.txt, realtive protocol
*/
