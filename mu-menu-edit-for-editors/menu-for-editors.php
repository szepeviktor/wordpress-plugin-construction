<?php
/*
Plugin Name: Menu management for Editors (MU)
Version: 0.1.2
Description: Show Menus page for Editors.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
Alternative: https://role-editor.storage.googleapis.com/downloads/appearance-permissions.zip
*/

// Before the administration menu loads
add_action( '_admin_menu', 'o1_menu_edit_for_editors', 0 );
// Add menu item uses AJAX
add_action( 'wp_ajax_add-menu-item', 'o1_menu_edit_for_editors', 0 );

function o1_menu_edit_for_editors() {

    $current_user = wp_get_current_user();

    // This is not an editor
    if ( property_exists( $current_user , 'roles' ) && ! in_array( 'editor', $current_user->roles ) ) {

        return;
    }

    // Add Menus as a top-level menu page
    add_menu_page( __( 'Menus' ), __( 'Menus' ), 'edit_pages', 'nav-menus.php', '', 'dashicons-admin-appearance', 60 );
    // Remove Appearance menu (gets added because of "edit_theme_options")
    remove_menu_page( 'themes.php' );

    global $pagenow;

    // We are on the Menus page
    if ( 'nav-menus.php' === $pagenow || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
        // Elevate editors from _admin_menu on + on "add-menu-item" AJAX call
        add_filter( 'user_has_cap', 'o1_add_edit_theme_options_to_editor' );
        // Hide Customizer
        $style = '.hide-if-no-customize { display: none; }';
        wp_add_inline_style( 'wp-admin', $style );
    }
}

function o1_add_edit_theme_options_to_editor( $allcaps ) {

    $allcaps['edit_theme_options'] = true;

    return $allcaps;
}
