<?php // phpcs:disable WordPress.Files.FileName.InvalidClassFileName
/**
 * Stop Hungarian translation update of WooCommerce.
 *
 * @package          Wcstoptranslationupdateshu
 * @author           Viktor Szépe <viktor@szepe.net>
 * @link             https://github.com/szepeviktor/wordpress-plugin-construction
 * @wordpress-plugin
 * Plugin Name: Stop Hungarian translation update of WooCommerce (MU)
 * Version: 0.1.0
 * Description: Hooks plugin update transient and removes Hungarian updates.
 * Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
 * License: The MIT License (MIT)
 * Author: Viktor Szépe
 */

// Hook plugin updates.
add_filter( 'site_transient_update_plugins', 'o1_wc_stop_translation_updates' );

/**
 * Remove Hungarian WooCommerce translation from plugin updates transient.
 *
 * @param string $transient The original transient value.
 * @return string
 */
function o1_wc_stop_translation_updates( $transient ) {
	// Loop through all available updates.
	if ( is_object( $transient ) && property_exists( $transient, 'translations' ) ) {
		foreach ( $transient->translations as $index => $update ) {
			// Remove Hungarian WooCommerce translation.
			if ( 'plugin' === $update['type'] && 'woocommerce' === $update['slug'] && 'hu_HU' === $update['language'] ) {
				unset( $transient->translations[ $index ] );
				break;
			}
		}
	}
	return $transient;
}
