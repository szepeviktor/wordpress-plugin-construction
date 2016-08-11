<?php
/*
Plugin Name: Light Profiling (MU)
Version: 0.1.0
Description: Log execution times.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

class Light_Profiling {

    private $time_preplugin;
    private $time_postplugin;
    private $time_wploaded;
    private $time_prethemeload;
    private $time_postthemesetup;
    private $time_end;

    private $file_count;

    public function __construct() {

        /**
         * Bootstrap
         *
         * There is literally 0 msec before timer_start()
         * /index.php, /wp-blog-header.php, /wp-load.php, /wp-config.php, /wp-settings.php
         */
        // timer_start();
        /**
         * Core and MU plugins
         *
         * /wp-includes/* and /wp-content/mu-plugins/*.php
         */
        add_action( 'muplugins_loaded', array( $this, 'preplugin' ), 4294967295 );
        /**
         * Plugins
         *
         * /wp-includes/vars.php, /wp-content/plugins/ACTIVE-PLUGINS/PLUGIN-FILE.php, /wp-includes/pluggable.php
         */
        add_action( 'plugins_loaded', array( $this, 'postplugin' ), 4294967295 );
        /**
         * Theme and user
         *
         * /wp-content/ACTIVE-THEME/functions.php
         */
        add_action( 'after_setup_theme', array( $this, 'postthemesetup' ), 4294967295 );
        /**
         * Execute core and 'init' action
         */
        add_action( 'wp_loaded', array( $this, 'wploaded' ), 4294967295 );
        /**
         * Parse headers, parse query and 'wp' action
         */
        add_action( 'wp', array( $this, 'prethemeload' ), 4294967295 );
        // OR on admin
        add_action( 'wp_dashboard_setup', array( $this, 'prethemeload' ), 4294967295 );
        /**
         * Load current template
         *
         * /wp-content/ACTIVE-THEME/CURRENT-TEMPLATE.php
         */
        add_action( 'wp_print_footer_scripts', array( $this, 'wpend' ), 4294967295 );
        // OR on admin
        add_action( 'admin_print_footer_scripts', array( $this, 'wpend' ), 4294967295 );
    }

    public function thou( $micro ) {

        return round( $micro * 1000 );
    }

    public function preplugin() {

        $this->time_preplugin = microtime( true );
    }

    public function postplugin() {

        $this->time_postplugin = microtime( true );
    }

    public function postthemesetup() {

        // Move these debug lines to any method
        //echo '<pre>'; global $wp_actions; var_export($wp_actions);
        //echo '<pre>'; var_export( array_slice( get_included_files(), $this->file_count ) ); exit;

        // And move this to the previous method
        //$this->file_count = count( get_included_files() );

        $this->time_postthemesetup = microtime( true );
    }

    public function wploaded() {

        $this->time_wploaded = microtime( true );
    }

    public function prethemeload() {

        $this->time_prethemeload = microtime( true );
    }

    public function wpend() {

        global $timestart;

        $this->time_end = microtime( true );

        $times = array(
            $this->thou( $this->time_preplugin - $timestart ),
            $this->thou( $this->time_postplugin - $this->time_preplugin ),
            $this->thou( $this->time_postthemesetup - $this->time_postplugin ),
            $this->thou( $this->time_wploaded - $this->time_postthemesetup ),
            $this->thou( $this->time_prethemeload - $this->time_wploaded ),
            $this->thou( $this->time_end - $this->time_prethemeload ),
        );

        // pre plugin : post plugin : post themesetup : wp loaded : pre themeload : end
        error_log( 'Profiling data: ' . implode( ':', $times ) );
    }
}

new Light_Profiling();
