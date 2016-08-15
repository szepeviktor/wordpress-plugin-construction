<?php
/*
Plugin Name: TOTP login
Version: 1.0.2
Description: Log in with your username and a TOTP without your password.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

// @TODO Store TOTP-s in a transients for 2 minutes to prevent a replay attack
//       append to: set_transient( 'totp_used_' . $user->user_login, $password, 2 * MINUTE_IN_SECONDS );
//       and check before checkTotp()

add_action( 'init', 'o1_totp_init' );

function o1_totp_init() {

    // Core hooks
    remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
    remove_action( 'edit_user_created_user', 'wp_send_new_user_notifications', 10 );
    remove_filter( 'authenticate', 'wp_authenticate_username_password', 20 );

    // Old hooks
    //add_action( 'register_new_user', 'o1_totp_register_new_user' );
    //add_action( 'edit_user_created_user', 'o1_totp_register_new_user' );
    add_action( 'user_register', 'o1_totp_register_new_user' );
    add_filter( 'authenticate', 'o1_authenticate_totp', 10, 3 );

    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        require_once dirname( __FILE__ ) . '/includes/class-totp-cli-command.php';
    }
}

/**
 * Authenticate a user, confirming the username and TOTP are valid.
 */
function o1_totp_register_new_user( $user_id ) {

    require_once dirname( __FILE__ ) . '/includes/OtpInterface.php';
    require_once dirname( __FILE__ ) . '/includes/Otp.php';
    require_once dirname( __FILE__ ) . '/includes/GoogleAuthenticator.php';
    require_once dirname( __FILE__ ) . '/includes/Base32.php';

    $secret = Otp\GoogleAuthenticator::generateRandom( 32 );
    $meta_added = update_user_meta( $user_id, '_totp_login_secret_code', $secret );
    // @FIXME if ( ! $meta_added ) {
    $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    $user = get_user_by( 'ID', $user_id );
    $qr_url = Otp\GoogleAuthenticator::getQrCodeUrl( 'totp', 'WordPress site ' . $blogname , $secret );

    $message = 'Your TOTP secret code is ' . rawurlencode( $secret ) . "\r\n\r\n";
    $message .= 'You may use your phone\'s camera to register your secret code. Click this link: ' . "\r\n";
    $message .= $qr_url . "\r\n\r\n";
    $message .= 'Software for Windows 7, 8 and 10: https://winauth.com/download/' . "\r\n";
    $message .= '"ClickOnce" for Windows: https://winauth.com/downloads/WinAuth.application' . "\r\n";
    $message .= 'This website stores the secret code in *your* browser: http://gauth.apps.gbraad.nl/' . "\r\n";
    $message .= 'Android app: https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2' . "\r\n";
    $message .= 'iPhone app: https://itunes.apple.com/us/app/google-authenticator/id388497605' . "\r\n";
    $message .= 'Windows Phone app: https://www.microsoft.com/hu-hu/store/apps/authenticator/9wzdncrfj3rj' . "\r\n";
    $message .= 'KeePass plugin: https://bitbucket.org/devinmartin/keeotp/wiki/Home' . "\r\n";

    wp_mail( $user->user_email, sprintf( '[%s] Your username and secret code', $blogname ), $message );
}

/**
 * Authenticate a user, confirming the username and TOTP are valid.
 */
function o1_authenticate_totp( $user, $username, $password ) {

    if ( $user instanceof WP_User ) {

        return $user;
    }

    if ( empty( $username ) || empty( $password ) ) {
        if ( is_wp_error( $user ) ) {

            return $user;
        }

        $error = new WP_Error();

        if ( empty( $username ) ) {
            $error->add( 'empty_username', __( '<strong>ERROR</strong>: The username field is empty.' ) );
        }

        if ( empty( $password ) ) {
            $error->add( 'empty_password', __( '<strong>ERROR</strong>: The password field is empty.' ) );
        }

        return $error;
    }

    $user = get_user_by( 'login', $username );

    if ( ! $user ) {

        return new WP_Error( 'invalid_username',
            __( '<strong>ERROR</strong>: Invalid username.' )
            . ' <a href="' . wp_lostpassword_url() . '">'
            . __( 'Lost your password?' )
            . '</a>'
        );
    }

    /**
     * Filter whether the given user can be authenticated with the provided $password.
     *
     * @param WP_User|WP_Error $user     WP_User or WP_Error object if a previous
     *                                   callback failed authentication.
     * @param string           $password Password to check against the user.
     */
    $user = apply_filters( 'totp_authenticate_user', $user, $password );
    if ( is_wp_error( $user ) ) {

        return $user;
    }

    $totp_secret_code = get_user_meta( $user->ID, '_totp_login_secret_code', true );
    if ( empty( $totp_secret_code ) ) {

        // TOTP secret code not available
        return $user;
    }

    require_once 'includes/OtpInterface.php';
    require_once 'includes/Otp.php';
    require_once 'includes/GoogleAuthenticator.php';
    require_once 'includes/Base32.php';

    $totp = new Otp\Otp();

    if ( ! $totp->checkTotp( Base32\Base32::decode( $totp_secret_code ), $password ) ) {

        return new WP_Error( 'incorrect_password',
            sprintf(
                __( '<strong>ERROR</strong>: The password you entered for the username %s is incorrect.' ),
                '<strong>' . $username . '</strong>'
            )
        );
    }

    return $user;
}
