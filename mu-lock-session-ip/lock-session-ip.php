<?php
/*
Plugin Name: Lock Session IP (MU)
Version: 1.0.0
Description: Log out user when his IP address changes.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

/**
 * Inspect IP address after default authentication filters.
 */
add_filter( 'determine_current_user', 'o1_check_session_ip', 30 );

/**
 * Compare IP in session and destroy session on mismatch.
 */
function o1_check_session_ip( $user_id ) {

    if ( false !== $user_id ) {

        // @fjarrett helped
        $sessions = WP_Session_Tokens::get_instance( $user_id );
        $session = $sessions->get( wp_get_session_token() );
        if ( empty( $session )
            || empty( $session['ip'] )
            || empty( $_SERVER['REMOTE_ADDR'] )
            || $session['ip'] !== $_SERVER['REMOTE_ADDR']
        ) {
            // User's IP address has changed, log him out
            add_action( 'init', 'wp_destroy_current_session', 0 );
            error_log( 'Destroying session for user #' . $user_id );
        }
    }

    return $user_id;
}
