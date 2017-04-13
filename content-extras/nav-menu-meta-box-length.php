<?php
/*
Plugin Name: Menu editor meta box length
Description: Add URL column to the Media list page with autoselect.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Version: 0.2.1
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/content-extras
*/

add_filter( 'nav_menu_meta_box_object', 'o1_nav_menu_meta_box_length' );
add_action( 'admin_enqueue_scripts', 'o1_nav_menu_meta_box_length_style' );

/**
 * Set nav-menu meta box length
 *
 *     define( 'O1_NAV_MENU_METABOX_TYPE, 'post,category' );
 *     define( 'O1_NAV_MENU_METABOX_PER_PAGE', 200 );
 */
function o1_nav_menu_meta_box_length( $meta_box_object ) {

    // https://core.trac.wordpress.org/ticket/32237
    $object_types = defined( 'O1_NAV_MENU_METABOX_TYPE' ) ?
        explode( ',', O1_NAV_MENU_METABOX_TYPE ) : array( 'post' );

    if ( property_exists( $meta_box_object, 'name' )
        && in_array( $meta_box_object->name, $object_types )
    ) {
        $per_page = defined( 'O1_NAV_MENU_METABOX_PER_PAGE' ) ?
            O1_NAV_MENU_METABOX_PER_PAGE : 200;
        $post_type_name = $meta_box_object->name;
        $pagenum = ( isset( $_REQUEST[ $post_type_name . '-tab' ] ) && isset( $_REQUEST['paged'] ) ) ?
            absint( $_REQUEST['paged'] ) : 1;
        $offset = 0 < $pagenum ?
            $per_page * ( $pagenum - 1 ) : 0;

        // Must be set in _wp_nav_menu_meta_box_object()
        if ( ! property_exists( $meta_box_object, '_default_query' ) ) {
            $meta_box_object->_default_query = array();
        }
        $meta_box_object->_default_query['posts_per_page'] = $per_page;
        $meta_box_object->_default_query['offset'] = $offset;
    }

    return $meta_box_object;
}

function o1_nav_menu_meta_box_length_style() {

    // 1000 pixels max-height
    $style = '.categorydiv div.tabs-panel, .customlinkdiv div.tabs-panel,
        .posttypediv div.tabs-panel, .taxonomydiv div.tabs-panel, .wp-tab-panel
        { max-height: 1000px !important; }';

    wp_add_inline_style( 'wp-admin', $style );
}
