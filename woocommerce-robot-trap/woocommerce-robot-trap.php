<?php
/*
Plugin Name: WooCommerce product reviews robot trap
Version: 0.1.1
Description: Stops spammer robots, hide the field with CSS <code>.comment-form .email-verify { display:none; }</code>
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

add_filter( 'woocommerce_product_review_comment_form_args', 'wcprrt_print_hidden_field', 99 );
add_filter( 'preprocess_comment', 'wcprrt_check_hidden_field', 0 );

function wcprrt_print_hidden_field( $comment_form ) {

    $hidden_field = '<p class="email-verify"><label for="comment-email-verify">Email verify <span class="required">*</span></label><input id="comment-email-verify" name="comment-email-verify" type="text" value="" size="30" /></p>';
    // Prepend
    $comment_form['comment_field'] = $hidden_field . $comment_form['comment_field'];

    return $comment_form;
}

function wcprrt_check_hidden_field( $commentdata ) {

    $name = 'comment-email-verify';
    if ( ! empty( $_POST[ $name ] ) ) {
        // Trigger firewall
        $value = sanitize_text_field( $_POST[ $name ] );
        do_action( 'robottrap_hiddenfield', $value );
    }

    return $commentdata;
}
