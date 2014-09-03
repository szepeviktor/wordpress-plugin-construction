<?php

add_shortcode( 'block', 'o1_block_shortcode' );
add_shortcode( 'block2', 'o1_block_shortcode' );
add_shortcode( 'block3', 'o1_block_shortcode' );
add_shortcode( 'block4', 'o1_block_shortcode' );
add_shortcode( 'block5', 'o1_block_shortcode' );
add_shortcode( 'block6', 'o1_block_shortcode' );
add_shortcode( 'block7', 'o1_block_shortcode' );
add_shortcode( 'block8', 'o1_block_shortcode' );
add_shortcode( 'block9', 'o1_block_shortcode' );

function o1_block_shortcode( $atts, $content = '' ) {
    return o1_tag( 'div', $atts, $content );
}

function o1_tag( $tag, $attributes = array(), $content = null ) {

    if ( empty( $tag ) || ! is_array( $attributes ) )
        return '';

    foreach ( $attributes as $attribute => &$data ) {
        if ( empty( $data ) || true === $data ) {
            // empty attributes
            $data = esc_attr( $attribute );
        } else {
            $data = implode( ' ', (array) $data );
            $data = $attribute . '="' . esc_attr( $data ) . '"';
        }
    }

    $attribute_string =  $attributes ? ' ' . implode( ' ', $attributes ) : '';
    $html = '<' . $tag . $attribute_string;
    $html .= is_null( $content ) ? ' />' : '>' . $content . '</' . $tag . '>';
    return wp_kses_post( $html );
}
