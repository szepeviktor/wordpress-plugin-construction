<?php
/*
Plugin Name: No mail (MU)
Version: 0.1.1
Description: Log emails instead of sending them.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {

    global $phpmailer;

    if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
        require_once ABSPATH . WPINC . '/class-phpmailer.php';
        require_once ABSPATH . WPINC . '/class-smtp.php';
        $phpmailer = new PHPMailer( true );
    }

    $atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );
    if ( isset( $atts['to'] ) ) {
        $to = $atts['to'];
    }
    if ( isset( $atts['subject'] ) ) {
        $subject = $atts['subject'];
    }
    if ( ! is_array( $to ) ) {
        $to = explode( ',', $to );
    }

    $phpmailer->From = apply_filters( 'wp_mail_from', $from_email );
    $phpmailer->FromName = apply_filters( 'wp_mail_from_name', $from_name );
    apply_filters( 'wp_mail_content_type', $content_type );
    $phpmailer->CharSet = apply_filters( 'wp_mail_charset', $charset );
    do_action_ref_array( 'phpmailer_init', array( &$phpmailer ) );

    error_log( sprintf( 'Email NOT sent To: %s Subject: %s',
        implode( ', ', $to ),
        $subject
    ) );

    return true;
}
