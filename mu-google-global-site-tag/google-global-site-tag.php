<?php
/*
Plugin Name: Google Analytics Global Site Tag for WordPress (MU)
Version: 1.0.3
Description: Insert Google Analytics Global Site Tag's code.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: GPLv2 or later
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

final class GST {

    /**
     * Google Analytics Global Site Tag
     * @link https://developers.google.com/analytics/devguides/collection/gtagjs/
     */
    private $snippet_template = <<<'EOT'
<!-- Global Site Tag - Google Analytics -->
<script async src='https://www.googletagmanager.com/gtag/js?id=%s'></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments)};
  gtag('js', new Date());
  gtag('config', %s);
  %s
</script>
EOT;

    /**
     * The JavaScript snippet
     */
    private $snippet = '';

    /**
     * Remarketing Conversion ID
     */
    private $cid = '';

    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new GST();
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

        add_action( 'template_redirect', array( $this, 'init' ) );
    }

    public function init() {

        // Disable for users with this capability
        $capability = apply_filters( 'gst_capability', 'edit_pages' );
        if ( current_user_can( $capability ) ) {

            return;
        }

        $tid = get_option( 'gst_tracking_id' );

        // Verify ID
        if ( false === $tid || ! preg_match( '/^[A-Z]+-[0-9A-Z]{9,}$/', $tid ) ) {

            return;
        }

        $cid = get_option( 'gst_conversion_id' );

        if ( false !== $cid && preg_match( '/^[0-9]{8,}$/', $cid ) ) {
            $this->conversion_id = $cid;
            add_filter( 'gst_extra_javascript', array( $this, 'remarketing_tag' ) );
        }

        // Render template
        $this->snippet = sprintf( $this->snippet_template,
            antispambot( $tid ),
            $this->get_js_concat( $tid ),
            apply_filters( 'gst_extra_javascript', '' )
        );

        if ( defined( 'GST_DISABLE' ) && GST_DISABLE ) {

            return;
        }

        if ( defined( 'GST_GOOGLE_RECOMMENDATION' ) && GST_GOOGLE_RECOMMENDATION ) {
            // Google: immediately after the opening <head> tag
            add_action( 'wp_head', array( $this, 'print_script' ), 20 );
        } else {
            // Speed: before the closing </body> tag
            add_action( 'wp_footer', array( $this, 'print_script' ) );
        }
    }

    public function remarketing_tag( $extra_javascript ) {

        return sprintf( "gtag('config', 'AW-%s');\n", $this->conversion_id ) . $extra_javascript;
    }

    public function print_script() {

        print $this->snippet;
    }

    public function get_code() {

        return $this->snippet;
    }

    private function get_js_concat( $tid ) {

        // 'G-00' + '123' + '456A'
        $js = sprintf( "'%s' + '%s' + '%s'",
            substr( $tid, 0, 4 ),
            substr( $tid, 4, 3 ),
            substr( $tid, 7 )
        );

        return $js;
    }

    /**
     * Register in Settings API
     */
    public function settings_init() {

        register_setting( 'general', 'gst_tracking_id' );
        register_setting( 'general', 'gst_conversion_id' );
        add_settings_section(
            'gst-ua-section',
            'Google Analytics Global Site Tag',
            array( $this, 'admin_section' ),
            'general'
        );
        add_settings_field(
            'gst_tracking_id',
            '<label for="gst_tracking_id">Tracking ID</label>',
            array( $this, 'admin_field_tracking' ),
            'general',
            'gst-ua-section'
        );
        add_settings_field(
            'gst_conversion_id',
            '<label for="gst_conversion_id">Conversion ID</label>',
            array( $this, 'admin_field_conversion' ),
            'general',
            'gst-ua-section'
        );
    }

    /**
     * Print the section description for Settings API
     */
    public function admin_section() {

        print '<p>Analytics tracking code and Adwords remarketing tag.</p>';
    }

    /**
     * Print the input field for Settings API
     */
    public function admin_field_tracking() {

        $tid = esc_attr( get_option( 'gst_tracking_id' ) );

        printf( '<input name="gst_tracking_id" id="gst_tracking_id" placeholder="G-A0A0ABACA"
            type="text" class="regular-text code" value="%s" />',
            $tid
        );
        print '<p class="description">Leave empty to disable tracking.</p>';
    }

    /**
     * Print the input field for Settings API
     */
    public function admin_field_conversion() {

        $cid = esc_attr( get_option( 'gst_conversion_id' ) );

        printf( '<input name="gst_conversion_id" id="gst_conversion_id" placeholder="0000000000"
            type="text" class="regular-text code" value="%s" />',
            $cid
        );
        print '<p class="description">Leave empty to disable tagging.</p>';
    }
}

function gst() {

    return GST::instance();
}

gst();
