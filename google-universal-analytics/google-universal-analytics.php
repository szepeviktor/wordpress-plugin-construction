<?php
/*
Plugin Name: Google Universal Analytics for WordPress
Version: 1.0.0
Description: Insert Google Analytics' tracking code.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: GPLv2 or later
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

final class GUA {

    // Google Analytics tracking snippet
    // https://developers.google.com/analytics/devguides/collection/analyticsjs/
    private $snippet_template = "<!-- Google Analytics -->
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', '%s', 'auto');
%s
ga('send', 'pageview');
</script>
<!-- End Google Analytics -->";

    private $snippet = '';

    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new GUA();
        }

        return self::$_instance;
    }

    public function __construct() {

        // Disable on development sites
        if ( ( defined( 'WP_ENV' ) && 'production' !== WP_ENV ) ) {

            return;
        }

        // WP-Cron has no visitors to track
        if ( defined( 'DOING_CRON' ) && DOING_CRON ) {

            return;
        }

        if ( is_admin() ) {
            // Settings in Options/Reading
            add_action( 'admin_init', array( $this, 'settings_init' ) );

            return;
        }

        add_action( 'init', array( $this, 'init' ) );
    }

    public function init() {

        // Disable for users with this capability
        $capability = apply_filters( 'gua_capability', 'edit_pages' );
        if ( current_user_can( $capability ) ) {

            return;
        }

        $ua = get_option( 'gua_tracking_id' );

        // Verify ID
        if ( false === $ua || ! preg_match( '/^UA-[0-9]{3,9}-[0-9]{1,4}$/', $ua ) ) {

            return;
        }

        // Render template
        $this->snippet = sprintf( $this->snippet_template,
            $ua,
            apply_filters( 'gua_extra_javascript', '' )
        );

        if ( defined( 'GUA_DISABLE' ) && GUA_DISABLE ) {
            return;
        }

        if ( defined( 'GUA_GOOGLE_RECOMMENDATION' ) && GUA_GOOGLE_RECOMMENDATION ) {
            // Google: before the closing </head> tag
            add_action( 'wp_head', array( $this, 'print_script' ), 20 );
        } else {
            // Speed: before the closing </body> tag
            add_action( 'wp_footer', array( $this, 'print_script' ) );
        }
    }

    public function print_script() {

        print $this->snippet;
    }

    public function get_code() {

        return $this->snippet;
    }

    /**
     * Register in Settings API
     */
    public function settings_init() {

        register_setting( 'general', 'gua_tracking_id' );
        add_settings_section(
            'gua-ua-section',
            'Google Analytics Property ID',
            array( $this, 'admin_section' ),
            'general'
        );
        add_settings_field(
            'gua_tracking_id',
            '<label for="gua_tracking_id">Property ID</label>',
            array( $this, 'admin_field' ),
            'general',
            'gua-ua-section'
        );
    }

    /**
     * Print the section description for Settings API
     */
    public function admin_section() {

        print '<p>Also called the "tracking ID"</p>';
    }

    /**
     * Print the input field for Settings API
     */
    public function admin_field() {

        $ua = esc_attr( get_option( 'gua_tracking_id' ) );

        printf( '<input name="gua_tracking_id" id="gua_tracking_id" placeholder="UA-XXXXX-Y"
            type="text" class="regular-text code" value="%s" />',
            $ua
        );
        print '<p class="description">Leave empty to disable tracking.</p>';
    }
}

function gua() {

    return GUA::instance();
}

gua();
