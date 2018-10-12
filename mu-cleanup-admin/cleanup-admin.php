<?php
/*
Plugin Name: Clean up WordPress admin (MU)
Version: 0.4.3
Description: Remove things visually from WordPress admin.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

class O1_Cleanup_Admin {

    private $footer_html = '<span id="footer-thankyou">Szépe Viktor - üzemeltetés/maintenance</span>';

    public function __construct() {

        add_action( 'wp_before_admin_bar_render', array( $this, 'remove_admin_bar_links' ) );
        add_action( 'in_admin_footer', array( $this, 'remove_update_footer' ) );
        add_filter( 'admin_footer_text', array( $this, 'footer_content' ) );

        // @TODO Hide for everyone: WordPress News widget

        add_action( 'admin_enqueue_scripts', array( $this, 'yoast_seo_help_center' ), 99 );
        add_filter( 'wpseo_submenu_pages', array( $this, 'yoast_seo_submenu_pages' ), 99 );

        add_action( 'admin_enqueue_scripts', array( $this, 'acf_css' ), 20 );

        //add_action( 'admin_enqueue_scripts', array( $this, 'hide_with_css' ), 20 );
        //add_action( 'admin_menu', 'remove_menu', 9999 );
    }

    /**
     * Yoast SEO plugin - Hide Help Center
     */
    public function yoast_seo_help_center() {

        // Remove Help Center container
        wp_add_inline_script( 'yoast-seo-admin-script', 'document.getElementById("yoast-help-center-container").remove();' );
        // Dequeue Help Center JavaScript
        wp_dequeue_script( 'yoast-seo-help-center' );
    }

    /**
     * Yoast SEO plugin - Remove Premium submenu
     */
    public function yoast_seo_submenu_pages( $submenu_pages ) {

        foreach ( $submenu_pages as $key => $submenu_page ) {
            // Remove Premium page
            if ( 'wpseo_licenses' === $submenu_page[4] ) {
                unset($submenu_pages[ $key ]);
            }
        }

        return $submenu_pages;
    }

    /**
     * ACF plugin - Hide right sidebar
     */
    public function acf_css( $hook ) {

        if ( 'edit.php' !== $hook ) {
            return;
        }
        $screen = get_current_screen();
        if( 'acf-field-group' !== $screen->post_type ) {
            return;
        }

        $style = '.acf-columns-2 .acf-column-2 { display: none !important; }';
        wp_add_inline_style( 'wp-admin', $style );
    }

    /**
     * Example for hiding elements with CSS
     */
    public function hide_with_css( $hook ) {

        if ( 'PAGE-NAME' !== $hook ) {
            return;
        }

        $style = '.page-body-class #selector { display:none !important; }';
        wp_add_inline_style( 'wp-admin', $style );
    }

    public function hide_waf_postbox() {
    }

    /**
     * Example for hiding elements with JavaScript
     */
    public function hide_with_js() {
    }

    /**
     * Example for removing menu items
     */
    public function remove_menu() {

        remove_menu_page( $menu_slug );
        remove_submenu_page( $menu_slug, $submenu_slug );
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
     * Change 'Thank you WP' in admin footer
     */
    public function footer_content( $footer ) {

        return $this->footer_html;
    }
}

new O1_Cleanup_Admin();
