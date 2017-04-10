<?php
/*
Plugin Name: Contact Form 7 Robot Trap
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Stops spammer robots, add <code>[robottrap email-verify class:email-verify tabindex:2]</code> and hide the field with CSS.
Version: 0.4.2
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/contact-form-7-robot-trap
Options: WPCF7_ROBOT_TRAP_TOLERATE_DNS_FAILURE
*/

/**
 * Hidden input field for stopping robots
 *
 *  - Add <code>[robottrap email-verify class:email-verify tabindex:2]</code> before email address field
 *  - Hide it with CSS <code>div.wpcf7 .wpcf7-robottrap { display:none; }</code>
 *
 * Fires robottrap_hiddenfield and robottrap_mx hooks to do something with the spammer.
 *
 * WARNING
 *
 * If the nameserver fails domain validation will generate false positives.
 * Disable domain validation by copying this to your wp-config.php:
 *
 *     define( 'WPCF7_ROBOT_TRAP_TOLERATE_DNS_FAILURE', true );
 *
 * @package wpcf7-robottrap
 */
add_action( 'wpcf7_init', 'wpcf7_add_shortcode_robottrap' );
add_filter( 'wpcf7_validate_robottrap', 'wpcf7_robottrap_validation_filter', 10, 2 );
if ( ! ( defined( 'WPCF7_ROBOT_TRAP_TOLERATE_DNS_FAILURE' ) && WPCF7_ROBOT_TRAP_TOLERATE_DNS_FAILURE ) ) {
    add_filter( 'wpcf7_validate_email', 'wpcf7_robottrap_domain_validation_filter', 20, 2 );
    add_filter( 'wpcf7_validate_email*', 'wpcf7_robottrap_domain_validation_filter', 20, 2 );
}

function wpcf7_add_shortcode_robottrap() {

    wpcf7_add_form_tag(
        array( 'robottrap' ),
        'wpcf7_robottrap_shortcode_handler',
        true
    );
}

function wpcf7_robottrap_shortcode_handler( $tag ) {

    $tag = new WPCF7_Shortcode( $tag );

    // default field name
    if ( empty( $tag->name ) )
        $tag->name = 'email-verify';

    // per field errors
    $validation_error = wpcf7_get_validation_error( $tag->name );

    // add wpcf7 specific classes
    $class = wpcf7_form_controls_class( 'text' );

    if ( $validation_error )
        $class .= ' wpcf7-not-valid';

    $atts = array();

    $atts['size'] = $tag->get_size_option( '40' );
    $atts['maxlength'] = $tag->get_maxlength_option();
    $atts['class'] = $tag->get_class_option( $class );
    $atts['id'] = $tag->get_id_option();
    $atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

    /**
     * Robots may look for the word "hidden".
     *
     * @ignore Commented out.
     */
    //$atts['aria-hidden'] = 'true';

    $value = (string) reset( $tag->values );

    if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
        $atts['placeholder'] = $value;
        $value = '';
    } elseif ( '' === $value ) {
        $value = $tag->get_default_option();
    }

    $value = wpcf7_get_hangover( $tag->name, $value );

    $atts['value'] = $value;
    $atts['type'] = 'text';
    $atts['name'] = $tag->name;

    $atts = wpcf7_format_atts( $atts );

    $html = sprintf( '<span class="wpcf7-form-control-wrap %s"><input %s />%s</span>',
        sanitize_html_class( $tag->name ),
        $atts,
        $validation_error
    );

    return $html;
}

/**
 * Detect submitted hidden field.
 *
 * This is the validator function of [robottrap].
 *
 * @param object $result The WPCF7 result object.
 * @param string $tag The source of the tag.
 *
 * @return object The modified WPCF7 object.
 */
function wpcf7_robottrap_validation_filter( $result, $tag ) {

    $tag = new WPCF7_Shortcode( $tag );

    $name = $tag->name;

    /**
     * Should be submitted empty, no $name sanitization.
     */
    if ( ! empty( $_POST[ $name ] ) ) {
        $value = sanitize_text_field( $_POST[ $name ] );

        /**
         * Counteraction for filled-out hidden field
         *
         * Only a robot is able to see fields hidden by CSS.
         *
         * @param string $value  Sanitized value of field.
         */
        do_action( 'robottrap_hiddenfield', $value );

        $result->invalidate( $tag, wpcf7_get_message( 'spam' ) );
    }

    return $result;
}

/**
 * Validate email domain.
 *
 * This is the validator function of [email]. Does robottrap_mx action on invalid email domain.
 *
 * @param object $result WPCF7 result object.
 * @param string $tag    Source of the tag.
 *
 * @return object The modified WPCF7 object.
 */
function wpcf7_robottrap_domain_validation_filter( $result, $tag ) {

    $tag = new WPCF7_Shortcode( $tag );

    $name = $tag->name;

    $value = isset( $_POST[ $name ] )
        ? trim( wp_unslash( sanitize_text_field( (string)$_POST[ $name ] ) ) )
        : '';

    if ( ! $result->is_valid( $name ) || '' === $value ) {
        return $result;
    }

    $domain = sanitize_text_field( substr( strrchr( $value, '@' ), 1 ));

    if ( empty( $domain ) || ! checkdnsrr( $domain, 'MX' ) ) {
        /**
         * Counteraction for empty or MX-less domain part of email addresses
         *
         * Usually this is a spammer robot.
         *
         * @param string $domain  Email domain.
         */
        do_action( 'robottrap_mx', $domain );

        $result->invalidate( $tag, wpcf7_get_message( 'spam' ) );
    }

    return $result;
}
