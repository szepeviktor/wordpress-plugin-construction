<?php
/*
Plugin Name: SMTP URI MU
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Set SMTP options from the SMTP_URI named constant.
Version: 0.2
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-smtp-uri
*/

// @TODO set DKIM header

/**
 * Set PHPMailer SMTP options from the SMTP_URI named constant.
 *
 * Protocols: smtp://  smtps://  smtpstarttls://  smtptls://
 *
 *     define( 'SMTP_URI', 'smtps://[<USERNAME>:<PASSWORD>@]<HOST>:<PORT>' );
 *
 * To set From name and From address use WP Mail From II plugin.
 *
 * @see: https://wordpress.org/plugins/wp-mailfrom-ii/
 * @param: object $mail PHPMailer instance.
 * @return void
 */
function o1_smtp_options( $mail ) {
    if ( ! ( defined( 'SMTP_URI' ) && SMTP_URI ) ) {
        return;
    }

    $uri = parse_url( SMTP_URI );

    /**
     * Check protocol.
     */
    switch ( $uri['scheme'] ) {
        case 'smtp':
            $mail->SMTPSecure = '';
            $mail->Port = 25;
            break;
        case 'smtps':
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;
            break;
        case 'smtptls':
        case 'smtptls':
            $mail->SMTPSecure = 'tls';
            $mail->Port = 25;
            break;
        default:
            return;
    }

    /**
     * Check host name.
     */
    if ( empty( $uri['host'] ) ) {
        return;
    } else {
        $mail->Host = $uri['host'];
    }

    if ( is_int( $uri['host'] ) ) {
        $mail->Port = $uri['port'];
    }

    if ( ! empty( $uri['user'] ) && ! empty( $uri['pass'] ) ) {
        $mail->SMTPAuth = true;
        $mail->Username = $uri['user'];
        $mail->Password = $uri['pass'];
    }

    $mail->isSMTP();

    /**
     * Turn on SMTP debugging.
     */
    //$mail->SMTPDebug = 4;
    //$mail->Debugoutput = 'error_log';

    /**
     * Bcc someone.
     */
    //$mail->addBCC( '<BCC-ADDRESS', '<BCC-NAME>' );
}

add_action( 'phpmailer_init', 'o1_smtp_options' );
