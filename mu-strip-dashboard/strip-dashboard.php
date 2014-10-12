<?php
/*
Plugin Name: Strip the Dashboard
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Remove thing visually from the WordPress admin.
Version: 0.1
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-strip-dashboard
*/

class O1_Strip_Dashboard {

    public function __construct() {

        add_action( 'admin_menu', array( $this, 'remove_comments_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'redirect_comments_admin_menu' ) );
        add_action( 'wp_before_admin_bar_render', array( $this, 'remove_admin_bar_links' ) );
        add_action( 'in_admin_footer', array( $this, 'remove_update_footer' ) );
        add_filter( 'admin_footer_text', array( $this, 'online1_webdesign' ) );
    }

    /**
     * Remove "Comments" from the admin menu
     */
    public function remove_comments_admin_menu() {

        remove_menu_page( 'edit-comments.php' );
    }

    /**
     * Redirect any user trying to access comments page
     */
    public function redirect_comments_admin_menu() {

        global $pagenow;

        if ( 'edit-comments.php' === $pagenow ) {
            wp_redirect( admin_url() );
            die();
        }
    }

    /**
     * Remove links from admin bar
     */
    public function remove_admin_bar_links() {

        global $wp_admin_bar;

        // WordPress logo
        $wp_admin_bar->remove_menu( 'wp-logo' );
        // About WordPress link
        $wp_admin_bar->remove_menu( 'about' );
        // WordPress.org link
        $wp_admin_bar->remove_menu( 'wporg' );
        // WordPress documentation link
        $wp_admin_bar->remove_menu( 'documentation' );
        // Support forums link
        $wp_admin_bar->remove_menu( 'support-forums' );
        // Feedback link
        $wp_admin_bar->remove_menu( 'feedback' );

        // Comments link
        $wp_admin_bar->remove_menu( 'comments' );
    }


    /**
     * Remove WP version from the admin footer
     */
    public function remove_update_footer() {

        remove_filter( 'update_footer', 'core_update_footer' );
    }

    /**
     * Remove 'Thank you WP' from admin footer
     */
    public function online1_webdesign( $footer ) {

        return '<span id="footer-thankyou">online1 - honlapkészítés/webdesign</span>';
    }
}

new O1_Strip_Dashboard();
