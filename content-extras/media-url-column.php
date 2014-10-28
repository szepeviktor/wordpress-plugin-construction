<?php
/*
Plugin Name: Media URL Column
Plugin Description: Adds URL column to the Media list page with autoselect.
*/


add_filter( 'manage_media_columns', 'muc_column' );
add_action( 'manage_media_custom_column', 'muc_value', 10, 2 );

function muc_column( $cols ) {
    $cols['media_url'] = "URL";
    return $cols;
}

function muc_value( $column_name, $id ) {
    if ( 'media_url' === $column_name )
        printf( '<input class="media-url-input nameless-input" style="width:100%%" type="text" readonly="" onclick="%s" value="%s" />',
            'jQuery(this).select();',
            wp_get_attachment_url( $id )
        );
}
