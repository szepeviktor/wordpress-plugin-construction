<?php
/*
Plugin Name: Shortcode checker
Version: 0.1.0
Description: Log undefined shortcodes in error log.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Author: Viktor Szépe
License: The MIT License (MIT)
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

add_filter( 'the_content', 'shortcode_check', 10.5 );

function shortcode_check( $content ) {

    global $shortcode_tags;

    if ( false === strpos( $content, '[' ) ) {
        return $content;
    }

    if ( empty( $shortcode_tags ) || ! is_array( $shortcode_tags ) ) {
        return $content;
    }

    // Find all registered tag names in $content
    preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
    $undefined = array_diff( $matches[1], array_keys( $shortcode_tags ) );

    if ( ! empty( $undefined ) ) {
        error_log( 'Undefined shortcodes: ' . implode( ',', $undefined ) );
    }

    return $content;
}
