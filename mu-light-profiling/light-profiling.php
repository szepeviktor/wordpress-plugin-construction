<?php // phpcs:disable WordPress.Files.FileName.InvalidClassFileName
/**
 * Light Profiling (MU)
 *
 * @wordpress-plugin
 * Plugin Name: Light Profiling (MU)
 * Version:     0.2.1
 * Description: Log execution times.
 * Plugin URI:  https://github.com/szepeviktor/wordpress-plugin-construction
 * License:     The MIT License (MIT)
 * Author:      Viktor Szépe
 * GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
 */

class Light_Profiling {

    private $time_preplugin;
    private $time_postplugin;
    private $time_postthemesetup;
    private $time_wploaded;

    private $file_count;

    public function __construct() {

        global $timestart;

        // Prevent errors
        $this->time_preplugin = $timestart;
        $this->time_postplugin = $timestart;
        $this->time_postthemesetup = $timestart;
        $this->time_wploaded = $timestart;

        // On CLI error_log() writes to the terminal.
        if ( 'cli' === php_sapi_name() ) {
            return;
        }

        // Bootstrap: /index.php, /wp-blog-header.php, /wp-load.php, /wp-config.php, /wp-settings.php
        // There is literally 0 msec before timer_start()

        // Core and MU plugins: /wp-includes/*, /wp-content/mu-plugins/*.php
        add_action( 'muplugins_loaded', array( $this, 'preplugin' ), PHP_INT_MAX );

        // Plugins: /wp-includes/vars.php, /wp-content/plugins/ACTIVE-PLUGIN/PLUGIN-FILE.php, /wp-includes/pluggable.php
        add_action( 'plugins_loaded', array( $this, 'postplugin' ), PHP_INT_MAX );

        // Theme and user: /wp-content/ACTIVE-THEME/functions.php
        add_action( 'after_setup_theme', array( $this, 'postthemesetup' ), PHP_INT_MAX );

        // Do the rest during 'init' hook, WC_DOING_AJAX is available at init:0
        add_action( 'init', array( $this, 'init' ), 10 );
    }

    public function init() {

        // Execute core and 'init' action
        add_action( 'wp_loaded', array( $this, 'wploaded' ), PHP_INT_MAX );

        // Load current template: /wp-content/ACTIVE-THEME/CURRENT-TEMPLATE.php
        // In order of specificity
        switch ( true ) {
            case ( defined( 'WC_DOING_AJAX' ) && true === WC_DOING_AJAX && ! empty( $_GET['wc-ajax'] ) ):
                // WooCommerce AJAX
                $action = sanitize_text_field( wp_unslash( $_GET['wc-ajax'] ) );
                // prio:9 just before WooComerce AJAX actions
                add_action( 'wp_ajax_woocommerce_' . $action, array( $this, 'wpend' ), 9 );
                add_action( 'wp_ajax_nopriv_woocommerce_' . $action, array( $this, 'wpend' ), 9 );
                add_action( 'wc_ajax_' . $action, array( $this, 'wpend' ), 9 );
                break;
            case ( wp_doing_ajax() && isset( $_REQUEST['action'] ) ):
                // AJAX
                // prio:0 Before AJAX action runs :(
                add_action( 'wp_ajax_' . $_REQUEST['action'], array( $this, 'wpend' ), 0 );
                add_action( 'wp_ajax_nopriv_' . $_REQUEST['action'], array( $this, 'wpend' ), 0 );
                break;
            case ( defined( 'REST_REQUEST' ) && REST_REQUEST ):
                // REST API
                add_filter( 'rest_pre_echo_response', array( $this, 'wpend_filter' ), PHP_INT_MAX );
                break;
            case ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ):
                // XML-RPC
                // Before serving request :(
                add_filter( 'wp_xmlrpc_server_class', array( $this, 'wpend_filter' ), PHP_INT_MAX );
                break;
            case ( defined( 'WP_USE_THEMES' ) && WP_USE_THEMES ):
                // Frontend
                add_action( 'wp_print_footer_scripts', array( $this, 'wpend' ), PHP_INT_MAX );
                break;
            case ( isset( $_SERVER['SCRIPT_FILENAME'] ) && ABSPATH . 'wp-admin/async-upload.php' === $_SERVER['SCRIPT_FILENAME'] ):
                // Media upload through AJAX
                add_filter( 'wp_update_attachment_metadata', array( $this, 'wpend_filter' ), PHP_INT_MAX );
                break;
            case ( is_admin() ):
                // Admin
                add_action( 'admin_print_footer_scripts', array( $this, 'wpend' ), PHP_INT_MAX );
                break;
            case ( is_feed() ):
                // Feeds
                // There is no hook at the end of feeds :(
                add_action( 'atom_head', array( $this, 'wpend' ), PHP_INT_MAX );
                add_action( 'rdf_header', array( $this, 'wpend' ), PHP_INT_MAX );
                add_action( 'rss2_head', array( $this, 'wpend' ), PHP_INT_MAX );
                add_action( 'rss_head', array( $this, 'wpend' ), PHP_INT_MAX );
                add_action( 'comments_atom_head', array( $this, 'wpend' ), PHP_INT_MAX );
                add_action( 'commentsrss2_head', array( $this, 'wpend' ), PHP_INT_MAX );
                break;
        }
    }

    public function preplugin() {

        // Time of 'muplugins_loaded' action
        $this->time_preplugin = microtime( true );
    }

    public function postplugin() {

        // Time of 'plugins_loaded' action
        $this->time_postplugin = microtime( true );
    }

    public function postthemesetup() {

        // Move these debug lines to any method
        //echo '<pre>'; global $wp_actions; var_export($wp_actions); exit;
        //echo '<pre>'; var_export( array_slice( get_included_files(), $this->file_count ) ); exit;

        // And move this to the *previous* method
        //$this->file_count = count( get_included_files() );

        // Time of 'after_setup_theme' action
        $this->time_postthemesetup = microtime( true );
    }

    public function wploaded() {

        // Time of 'wp_loaded' action
        $this->time_wploaded = microtime( true );

        // TODO Last time value will be zero.
        if ( ! headers_sent() ) {
            $this->send_header();
        }
    }

    /**
     * At the end of profiling - near WordPress shutdown - log results.
     */
    public function wpend() {

        $times = $this->get_results();
        // Log results: pre plugin : post plugin : post themesetup : wp loaded : pre themeload : wp end
        error_log( 'Profiling data: ' . implode( ':', $times ) );
    }

    /**
     * Calculate results of profiling.
     *
     * @return array
     */
    private function get_results() {

        global $timestart;

        $timeend = microtime( true );

        return [
            $this->msec( $this->time_preplugin - $timestart ),
            $this->msec( $this->time_postplugin - $this->time_preplugin ),
            $this->msec( $this->time_postthemesetup - $this->time_postplugin ),
            $this->msec( $this->time_wploaded - $this->time_postthemesetup ),
            $this->msec( $timeend - $this->time_wploaded ),
        ];
    }

    /**
     * Add HTTP header.
     */
    private function send_header() {

        $times = $this->get_results();
        $header = implode( ', ', $times );
        header( 'X-Page-Speed: ' . $header, false );
    }

    /**
     * Log results in a filter.
     *
     * @param array $data
     * @return array
     */
    public function wpend_filter( $data ) {

        $this->wpend();

        return $data;
    }

    /**
     * Convert microseconds to miliseconds.
     *
     * @param float $micro
     * @return float
     */
    private function msec( $micro ) {

        return round( $micro * 1000 );
    }
}

new Light_Profiling();
