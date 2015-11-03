<?php
/*
Plugin Name: Automatically log in MU
Version: 1.2.1
Description: Just press the Log in button on the empty login page.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Options: AUTO_LOGIN_USER
*/

/**
 * Only activate on the login page
 */
add_action( 'login_init' , 'o1_auto_login_add_filter' );

/**
 * Activate auto login.
 */
function o1_auto_login_add_filter() {

    if ( defined( 'AUTO_LOGIN_USER' ) ) {
        add_filter( 'authenticate', 'o1_auto_login', 10, 3 );
    }
}

/**
 * Automatically log in.
 *
 * To set auto user name copy this line to your wp-config.php:
 *
 *     define( 'AUTO_LOGIN_USER', 'username' );
 */
function o1_auto_login( $user, $username, $password ) {

    $action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';

    if ( 'login' === $action             // Currently logging in
        && isset( $_POST['wp-submit'] )  // Pressed the login button
        && empty( $username )            // Empty user name
        && empty( $password )            //     and password
    ) {
        $auto_user = get_user_by( 'slug', AUTO_LOGIN_USER );

        if ( false !== $auto_user ) {
            $user = $auto_user;
        }
    }

    return $user;
}
