<?php

/**
 * Example class for your theme with OTOP.
 */
final class Custom_Theme {

    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    public $version = '1.0.1';

    private $current_language = null;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new Custom_Theme();
        }

        return self::$_instance;
    }

    public function __clone() {

        // Cloning instances of the class is forbidden
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; uh?' ), $this->version );
    }

    public function __wakeup() {

        // Unserializing instances of the class is forbidden
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; uh?' ), $this->version );
    }

    public function __construct() {

        if ( function_exists( 'pll_current_language' ) ) {
            $this->current_language = pll_current_language( 'locale' );
            if ( false === $this->current_language ) {
                $this->current_language = pll_default_language( 'locale' );
            }
        } else {
            $this->current_language = get_locale();
        }
    }

    /**
     * Print one field from a specific option as HTML.
     */
    public function print_option2( $field ) {

        print esc_html( $this->get_option2( $field ) );
    }

    /**
     * Print one email field from a specific option as obfuscated HTML.
     */
    public function print_email_option2( $field ) {

        print antispambot( $this->get_option2( $field ) );
    }

    /**
     * Return the value of one field from a specific multilingual option.
     */
    public function get_option2( $field ) {

        return $this->pll_get_field( 'two_theme_settings', $field );
    }

    /**
     * Get field from a multilingual option.
     */
    public function pll_get_field( $option, $field ) {

        $pll_option = $option . '_' . $this->current_language;
        $value = get_option( $pll_option );

        if ( false === $value ) {
            trigger_error( 'Missing option: ' . $pll_option, E_USER_WARNING );

            return false;
        }

        if ( ! isset( $value[ $field ] ) ) {
            trigger_error( 'Missing field: ' . $field . ' in option: ' . $pll_option, E_USER_WARNING );

            return false;
        }

        return $value[ $field ];
    }
}

/**
 * Returns the main instance of Custom_Theme to prevent the need to use globals.
 *
 * @return Custom_Theme
 */
function Custom_Theme() {

    return Custom_Theme::instance();
}

// In templates: Custom_Theme()->print_option2( 'two_theme_text_field_u' );
