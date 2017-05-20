<?php
/*
Plugin Name: Flush cache on maintenance (MU)
Version: 0.1.1
Description: Flush object cache on core, theme and plugin maintenance.
Author: Viktor Szépe
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Constants: FLUSH_ON_UPGRADER_COMPLETE
*/

if ( function_exists( 'wp_cache_flush' ) ) {
    o1_hook_flush_cache();
}

function o1_hook_flush_cache() {

    $hooks = array(
        'switch_theme',
        'activate_plugin',
        'deactivate_plugin',
    );
    // Core flushes on core upgrade, a plugin upgrade may not need a flush
    if ( defined( 'FLUSH_ON_UPGRADER_COMPLETE' ) && O1_FLUSH_ON_UPGRADER_COMPLETE ) {
        array_push( $hooks, 'upgrader_process_complete' );
    }

    foreach ( $hooks as $hook ) {
        add_action( $hook, 'wp_cache_flush', 20 );
    }
}
