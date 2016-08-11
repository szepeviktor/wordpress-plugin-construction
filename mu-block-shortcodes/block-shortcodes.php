<?php
/*
Plugin Name: Block Shortcodes (MU)
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Wrap any content in <code>div</code> elements thus enabling content styling with CSS
Version: 0.2.0
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-block-shortcodes
*/

if ( ! function_exists( 'add_filter' ) ) {
    error_log( 'Break-in attempt detected: block_shortcodes_direct_access '
        . addslashes( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' )
    );
    ob_get_level() && ob_end_clean();
    if ( ! headers_sent() ) {
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.1 403 Forbidden', true, 403 );
        header( 'Connection: Close' );
    }
    exit;
}

add_shortcode( 'block', 'o1_block_shortcode' );
add_shortcode( 'block2', 'o1_block_shortcode' );
add_shortcode( 'block3', 'o1_block_shortcode' );
add_shortcode( 'block4', 'o1_block_shortcode' );
add_shortcode( 'block5', 'o1_block_shortcode' );
add_shortcode( 'block6', 'o1_block_shortcode' );
add_shortcode( 'block7', 'o1_block_shortcode' );
add_shortcode( 'block8', 'o1_block_shortcode' );
add_shortcode( 'block9', 'o1_block_shortcode' );

function o1_block_shortcode( $atts, $content = null ) {

    return o1_tag( 'div', $atts, $content );
}

function o1_tag( $tag, $attributes = array(), $content = null ) {

    if ( empty( $tag ) || ! is_array( $attributes ) ) {
        return $content;
    }

    foreach ( $attributes as $attribute => &$data ) {
        if ( empty( $data ) || true === $data ) {
            // Empty attribute
            // https://www.w3.org/TR/html-markup/syntax.html#syntax-attr-empty
            $data = esc_attr( $attribute );
        } else {
            $data = implode( ' ', (array) $data );
            $data = sprintf( '%s="%s"',
                $attribute,
                esc_attr( $data )
            );
        }
    }

    $attribute_string = '';
    if ( ! empty( $attributes ) ) {
        $attribute_string = ' ' . implode( ' ', $attributes );
    }

    if ( is_null( $content ) ) {
        // Self-closing element
        $html = sprintf( '<%s%s />',
            $tag,
            $attribute_string
        );
    } else {
        $html = sprintf( '<%s%s>%s</%1$s>',
            $tag,
            $attribute_string,
            $content
        );
    }

    return wp_kses_post( $html );
}
