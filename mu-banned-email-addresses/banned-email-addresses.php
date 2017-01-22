<?php
/*
Plugin Name: Banned E-mail addresses (MU)
Version: 0.1.0
Description: Deny registration with common email addresses.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

if ( ! function_exists( 'add_filter' ) ) {
    error_log( 'Break-in attempt detected: banned_emails_direct_access '
        . addslashes( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' )
    );
    ob_get_level() && ob_end_clean();
    if ( ! headers_sent() ) {
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.1 403 Forbidden', true, 403 );
        header( 'Connection: Close' );
    }
    exit;
}

add_filter( 'registration_errors', 'o1_banned_emails', 10, 3 );

function o1_banned_emails( $errors, $sanitized_user_login, $user_email ) {

    // http://kb.mailchimp.com/lists/growth/limits-on-role-based-addresses
    $banned_addresses = array(
        'abuse',
        'admin',
        'billing',
        'compliance',
        'devnull',
        'dns',
        'ftp',
        'hostmaster',
        'inoc',
        'ispfeedback',
        'ispsupport',
        'list-request',
        'list',
        'marketing', // Added
        'maildaemon',
        'noc',
        'no-reply',
        'noreply',
        'null',
        'phish',
        'phishing',
        'postmaster',
        'privacy',
        'registrar',
        'root',
        'security',
        'spam',
        'support',
        'sysadmin',
        'tech',
        'test',
        'undisclosed-recipients',
        'unsubscribe',
        'usenet',
        'uucp',
        'webmaster',
        'www',
    );

    $email_parts = explode( '@', $user_email );

    if ( in_array( strtolower( $email_parts[0] ), $banned_addresses ) ) {
        $errors->add( 'banned_email', 'Registering with this email address is not allowed.' );
    }

    return $errors;
}
