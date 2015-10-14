<?php
/*
Plugin Name: Strip the Dashboard MU
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Remove things visually from the WordPress admin.
Version: 0.4.0
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-strip-dashboard
*/

class O1_Strip_Dashboard {

    public function __construct() {

        add_action( 'wp_before_admin_bar_render', array( $this, 'remove_admin_bar_links' ) );
        add_action( 'in_admin_footer', array( $this, 'remove_update_footer' ) );
        add_filter( 'admin_footer_text', array( $this, 'online1_webdesign' ) );
    // '#sidebar-container { display: none !important; }'
    // '.acf-columns-2 .acf-column-2 { display: none !important; }'
    }

    /**
     * Remove links from the admin bar
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

        return '<span id="footer-thankyou">Szépe Viktor - web technologies / webes technológiák</span>';
    }
}

new O1_Strip_Dashboard();
