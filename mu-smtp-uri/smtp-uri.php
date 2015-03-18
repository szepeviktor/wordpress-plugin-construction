<?php
/*
Plugin Name: SMTP URI
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Set SMTP options from the SMTP_URI named constant.
Version: 0.1
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-smtp-uri
*/

/**
 * Set PHPMailer SMTP options from the SMTP_URI named constant.
 *
 * smtp://  smtps://  smtpstarttls://  smtptls://
 *
 *     define( 'SMTP_URI', 'smtps://<USERNAME>:<PASSWORD>@<HOST>:<PORT>/<FROM-NAME>#<FROM-ADDRESS>' );
 *
 * @param: object $mail PHPMailer instance.
 * @return void
 */
function o1_smtp_options( $mail ) {
    if ( ! ( defined( 'SMTP_URI' ) && SMTP_URI ) ) {
        return false;
    }

    $uri = parse_url( SMTP_URI );
    $smtp_options = array();

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
            return false;
    }

    if ( empty( $uri['host'] ) ) {
        return false;
    }
    $mail->Host = $uri['host'];

    if ( is_int( $uri['host'] ) ) {
        $mail->Port = $uri['port'];
    }

    if ( empty( $uri['user'] ) ) {
        return false;
    }
    $mail->Username = $uri['user'];

    if ( empty( $uri['pass'] ) ) {
        return false;
    }
    $mail->Password = $uri['pass'];

    if ( empty( $uri['path'] ) || '/' === $uri['path'] || empty( $uri['fragment'] ) ) {
        return false;
    }
    // true = set "MAIL FROM"
    $mail->setFrom( $uri['fragment'], ltrim( $uri['path'], '/' ), true );

    // @TODO  add DKIM + DNS

    // All OK
    $mail->isSMTP();
    $mail->SMTPAuth = true;

    /**
     * Bcc someone.
     */
    //$mail->addBCC( '<BCC-ADDRESS', '<BCC-NAME>' );
    /**
     * Turn on debugging.
     */
    //$mail->SMTPDebug = 4;
    //$mail->Debugoutput = 'error_log';
}
add_action( 'phpmailer_init', 'o1_smtp_options' );
