<?php
/*
Plugin Name: Media URL Column
Description: Add URL column to the Media list page with autoselect.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Version: 0.2.2
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/content-extras
*/

add_filter( 'manage_media_columns', 'muc_column' );
add_action( 'manage_media_custom_column', 'muc_value', 10, 2 );

function muc_column( $cols ) {

    $cols['media_url'] = 'URL';

    return $cols;
}

function muc_value( $column_name, $id ) {

    if ( 'media_url' !== $column_name ) {
        return;
    }

    printf( '<input class="media-url-input nameless-input" style="width:100%%" type="text" readonly onclick="%s" value="%s" />',
        // Equals to 'jQuery(this).select();',
        'this.selectionStart=0; this.selectionEnd=this.value.length;',
        esc_attr( wp_get_attachment_url( $id ) )
    );
}
