<?php
/*
Plugin Name: Leho's speedbike (MU)
Version: 0.1.2
Description: Log WordPress runtime.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

add_action( 'shutdown', 'leho_lap_time' );

function leho_lap_time() {

    $duration = timer_stop();

    // Don't run on WP-CLI
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        return;
    }

    // Error log
    if ( isset( $_SERVER['REQUEST_URI'] ) ) {
        error_log( sprintf( 'Lap time: %ss Request: %s %s',
            $duration,
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_URI']
        ) );
    }

    // DOING_AJAX is defined late on file uploads (async-upload.php)
    if ( ( defined( 'DOING_CRON' ) && DOING_CRON )
        || ( defined( 'DOING_AJAX' ) && DOING_AJAX )
        || ( ABSPATH . 'wp-admin/async-upload.php' === $_SERVER['SCRIPT_FILENAME'] )
        || ( defined( 'REST_REQUEST' ) && REST_REQUEST )
    ) {
        return;
    }

    // HTML comment
    printf( '<!-- Lap time: %ss -->', esc_html( $duration ) );
}
