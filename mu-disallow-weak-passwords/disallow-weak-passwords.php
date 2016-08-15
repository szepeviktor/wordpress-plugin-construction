<?php
/*
Plugin Name: Disallow weak passwords (MU)
Description: Hide "Confirm use of weak password" checkbox
Version: 0.1.0
Author: Viktor Szépe
Author URI: https://github.com/szepeviktor
*/

add_action( 'admin_enqueue_scripts', 'o1_disallow_weak_passwords', 20 );

function o1_disallow_weak_passwords( $hook ) {

        if ( 'profile.php' !== $hook ) {

            return;
        }

        $style = '.pw-weak { display: none !important; }';
        wp_add_inline_style( 'wp-admin', $style );
}
