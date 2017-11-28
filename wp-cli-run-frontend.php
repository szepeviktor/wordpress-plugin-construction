<?php
/**
 * Run WordPress front-end.
 *
 * wp --url="http://example.com/" eval-file wp-cli-run-frontend.php
 */

WP_CLI::get_runner()->load_wordpress();

// Hide admin bar
add_filter( 'get_user_metadata', function ( $value, $object_id, $meta_key ) {
    if ( 'show_admin_bar_front' === $meta_key ) {
        return 'false';
    }
    return $value;
}, 99, 3 );

wp();
define( 'WP_USE_THEMES', true );
require_once( ABSPATH . WPINC . '/template-loader.php' );
