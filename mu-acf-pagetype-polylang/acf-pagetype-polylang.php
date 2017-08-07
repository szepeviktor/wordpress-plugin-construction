<?php
/*
Plugin Name: ACF Page Type fix with Polylang (MU)
Version: 0.1.0
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

class ACF_Page_Type_Polylang {

    // Whether we hooked page_on_front
    private $filtered = false;

    public function __construct() {

        add_filter( 'acf/location/rule_match/page_type', array( $this, 'hook_page_on_front' ) );
    }

    public function hook_page_on_front( $match ) {

        if ( ! $this->filtered ) {
            add_filter( 'option_page_on_front', array( $this, 'translate_page_on_front' ) );
            // Prevent second hooking
            $this->filtered = true;
        }

        return $match;
    }

    public function translate_page_on_front( $value ) {

        if ( function_exists( 'pll_get_post' ) ) {
            // Make page_on_front multilingual
            $value = pll_get_post( $value );
        }

        return $value;
    }
}

new ACF_Page_Type_Polylang();
