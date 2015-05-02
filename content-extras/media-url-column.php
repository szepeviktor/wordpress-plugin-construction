<?php
/*
Plugin Name: Media URL Column
Description: Add URL column to the Media list page with autoselect.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Version: 0.2
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/content-extras
*/

function muc_column( $cols ) {
    $cols['media_url'] = "URL";

    return $cols;
}

function muc_value( $column_name, $id ) {
    if ( 'media_url' === $column_name ) {
        printf( '<input class="media-url-input nameless-input" style="width:100%%" type="text" readonly onclick="%s" value="%s" />',
            // 'jQuery(this).select();',
            'this.selectionStart=0; this.selectionEnd=this.value.length;',
            wp_get_attachment_url( $id )
        );
    }
}

add_filter( 'manage_media_columns', 'muc_column' );
add_action( 'manage_media_custom_column', 'muc_value', 10, 2 );
