<?php
/*
Plugin Name: Themler for admins (MU)
Description: Allow site administrators to edit themes.
Version: 0.1.0
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Author: Viktor Szépe
License: GNU General Public License (GPL) version 2
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'map_meta_cap', 'itone_edit_themes_for_administrators', 10, 3 );

function itone_edit_themes_for_administrators( $caps, $cap, $user_id ) {

    if ( 'edit_themes' === $cap ) {
        $user = get_userdata( $user_id );

        if ( in_array( 'administrator', $user->roles ) ) {
            $caps = array( 'edit_themes' );
        }
    }

    return $caps;
}
