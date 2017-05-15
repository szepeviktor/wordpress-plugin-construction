<?php
/*
Plugin Name: Fluch cache on update (MU)
Version: 0.1.0
Description: Flush object cache on core, theme and plugin maintenance.
Author: Viktor Szépe
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

if ( function_exists( 'wp_cache_flush' ) ) {
    o1_flush_cache();
}

function o1_flush_cache() {

    $hooks = array(
        'upgrader_process_complete',
        'switch_theme',
        'activate_plugin',
        'deactivate_plugin',
    );

    foreach ( $hooks as $hook ) {
        add_action( $hook, 'wp_cache_flush', 20 );
    }
}
