<?php
/*
Plugin Name: Comment form robot trap
Version: 0.3.0
Description: Stops spammer robots, hide the field with CSS <code>.comment-form .email-verify { display:none; }</code>
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
*/

add_filter( 'comment_form_fields', 'cfrt_print_hidden_field', 99 );
add_filter( 'woocommerce_product_review_comment_form_args', 'cfrt_wc_print_hidden_field', 99 );
add_filter( 'preprocess_comment', 'cfrt_check_hidden_field', 0 );

function cfrt_print_hidden_field( $comment_fields ) {

    $output_fields = array();
    foreach ( $comment_fields as $name => $field ) {
        // Insert as second field
        if ( 1 === count( $output_fields ) ) {
            $output_fields['emailverify'] = '<p class="email-verify"><label for="comment-email-verify">Email <span class="required">*</span></label><input id="comment-email-verify" name="comment-email-verify" type="text" value="" size="30" maxlength="100" /></p>';
        }
        $output_fields[ $name ] = $field;
    }

    return $output_fields;
}

function cfrt_wc_print_hidden_field( $comment_form ) {

    $hidden_field = '<p class="email-verify"><label for="comment-email-verify">Email verify <span class="required">*</span></label><input id="comment-email-verify" name="comment-email-verify" type="text" value="" size="30" /></p>';
    // Prepend
    $comment_form['comment_field'] = $hidden_field . $comment_form['comment_field'];

    return $comment_form;
}

function cfrt_check_hidden_field( $commentdata ) {

    $name = 'comment-email-verify';

    // Check referer HTTP header.
    if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
        // Trigger firewall
        do_action( 'robottrap_hiddenfield', 'Comment referer missing.' );
    }

    if ( ! empty( $_POST[ $name ] ) ) {
        // Trigger firewall
        $value = sanitize_text_field( $_POST[ $name ] );
        do_action( 'robottrap_hiddenfield', $value );
    }

    return $commentdata;
}
