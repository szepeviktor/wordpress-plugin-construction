<?php
/**
 *
 * @param variable $value
 * @param Voce_Setting $setting
 * @param array $args
 * @return variable
 */
function vs_sanitize_checkbox($value, $setting, $args) {
	return !is_null( $value );
}

/**
 *
 * @param variable $value
 * @param Voce_Setting $setting
 * @param array $args
 * @return variable
 */
function vs_sanitize_text($value, $setting, $args) {
	$eol = '';
	$is_textarea = ( $args['display_callback'] == 'vs_display_textarea' );

	// don't trim last newline
	if ( $is_textarea && substr( $value, -1 ) === PHP_EOL ) {
		$eol = PHP_EOL;
	}

	return $is_textarea ? strip_tags( trim( $value ) . $eol ) : sanitize_text_field( $value );
}

/**
 *
 * @param variable $value
 * @param Voce_Setting $setting
 * @param array $args
 * @return variable
 */
function vs_sanitize_url($value, $setting, $args) {
	return esc_url_raw( sanitize_text_field( $value ) );
}

/**
 *
 * @param variable $value
 * @param Voce_Setting $setting
 * @param array $args
 * @return variable
 */
function vs_sanitize_email($value, $setting, $args) {
	$value = sanitize_email( sanitize_text_field( $value ) );

	if( !is_email( $value ) ) {
		$setting->add_error( sprintf( 'The %s is not a valid email address', $setting->title ) );
		return null;
	}

	return $value;
}

/**
 *
 * @param variable $value
 * @param Voce_Setting $setting
 * @param array $args
 * @return variable
 */
function vs_sanitize_dropdown( $value, $setting, $args ) {
	$value = strip_tags( trim( $value ) );

	if ( ! in_array( $value, array_keys( $args['options'] ) ) )
		return false;

	return $value;
}

/**
 *
 * @param variable $value
 * @return variable
 */
function vs_sanitize_int( $value ) {
	return intval( $value );
}

/* Deprecated Functions */
function vs_santize_checkbox($value, $setting, $args) {
	_deprecated_function( __FUNCTION__, '0.2', 'vs_sanitize_checkbox()' );
	return vs_sanitize_checkbox($value, $setting, $args);
}

function vs_santize_text($value, $setting, $args) {
	_deprecated_function( __FUNCTION__, '0.2', 'vs_sanitize_text()' );
	return vs_sanitize_text($value, $setting, $args);
}

