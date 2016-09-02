<?php
/*
Plugin Name: Facebook Pixel for WordPress (MU)
Version: 1.0.0
Description: Insert Facebook Pixel's code.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: GPLv2 or later
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

final class FBP {

    // Facebook Pixel JavaScript snippet
    // https://developers.facebook.com/docs/facebook-pixel/api-reference
    private $snippet_template = "<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','//connect.facebook.net/%s/fbevents.js');
fbq('init', '%s');
%s
</script>
<noscript><img height='1' width='1' style='display:none'
src='https://www.facebook.com/tr?id=%s&amp;ev=PageView&amp;noscript=1'
/></noscript>
<!-- End Facebook Pixel Code -->
";

    private $snippet = '';

    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new FBP();
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
        $capability = apply_filters( 'fbp_capability', 'edit_pages' );
        if ( current_user_can( $capability ) ) {

            return;
        }

        $pid = get_option( 'fbp_tracking_id' );

        // Verify ID
        if ( false === $pid || ! preg_match( '/^[0-9]{12,17}$/', $pid ) ) {

            return;
        }

        // Render template
        // https://www.facebook.com/translations/FacebookLocales.xml
        $lang = get_locale();
        $this->snippet = sprintf( $this->snippet_template,
            $lang,
            $pid,
            apply_filters( 'fbp_extra_javascript', "fbq('track', 'PageView');" ),
            $pid
        );

        if ( defined( 'FBP_DISABLE' ) && FBP_DISABLE ) {
            return;
        }

        if ( defined( 'FBP_FACEBOOK_RECOMMENDATION' ) && FBP_FACEBOOK_RECOMMENDATION ) {
            // Facebook: before the ending </head> tag
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

        register_setting( 'general', 'fbp_tracking_id' );
        add_settings_section(
            'fbp-ua-section',
            'Facebook Pixel ID',
            array( $this, 'admin_section' ),
            'general'
        );
        add_settings_field(
            'fbp_tracking_id',
            '<label for="fbp_tracking_id">Pixel ID</label>',
            array( $this, 'admin_field' ),
            'general',
            'fbp-ua-section'
        );
    }

    /**
     * Print the section description for Settings API
     */
    public function admin_section() {

        // @FIXME How to help admins?
        print '<p></p>';
    }

    /**
     * Print the input field for Settings API
     */
    public function admin_field() {

        $pid = esc_attr( get_option( 'fbp_tracking_id' ) );

        printf( '<input name="fbp_tracking_id" id="fbp_tracking_id" placeholder="NNNNNNNNNNNNNNN"
            type="text" class="regular-text code" value="%s" />',
            $pid
        );
        print '<p class="description">Leave empty to disable tracking.</p>';
    }
}

function fbp() {

    return FBP::instance();
}

fbp();
