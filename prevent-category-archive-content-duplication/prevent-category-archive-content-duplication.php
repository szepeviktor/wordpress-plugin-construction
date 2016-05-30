<?php
/*
Plugin Name: Prevent category archive content duplication
Version: 0.2
Description: Prevent category archive content duplication.
Plugin URI: http://cheatsheet.davidprog.hu/
Author: Latkóczy Dávid
Author URI: http://davidprog.hu/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Options: PCAD_CUSTOM_TAXONOMY
*/

// define( 'PCAD_CUSTOM_TAXONOMY', 'termekkategoria' );

add_action( 'wp', 'prevent_category_archive_duplication' );

function prevent_category_archive_duplication() {

    if ( PCAD_CUSTOM_TAXONOMY !== get_query_var( 'taxonomy' ) ) {
        return false;
    }

    $parent_term = get_query_var( PCAD_CUSTOM_TAXONOMY );
    $parent_data = get_term_by( 'slug', $parent_term, PCAD_CUSTOM_TAXONOMY );

    $child_id = pcad_sub( PCAD_CUSTOM_TAXONOMY, $parent_data->term_taxonomy_id );
    if ( $child_id ) {
        wp_redirect( get_term_link( $child_id, PCAD_CUSTOM_TAXONOMY ) );

        exit;
    }
}

function pcad_sub( $custom_tax, $parent_id, $recursive = false ) {

    $children = get_terms( $custom_tax, array(
        'hide_empty' => false,
        'fields'     => 'ids',
        'parent'     => $parent_id,
    ) );
    // Zero children
    if ( is_wp_error( $children ) ) {
        return $recursive ? $parent_id : false;
    }

    // more children
    if ( 1 !== count( $children ) ) {
        return false;
    }

    // 1 child
    $onechild = intval( $children[0] );
    return $recursive ? pcad_sub( $custom_tax, $onechild, true ) : $onechild;
}
