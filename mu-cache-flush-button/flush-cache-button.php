<?php
/*
Plugin Name: Fluch cache button (MU)
Version: 0.1.1
Description: Add an admin bar button to flush the object cache.
Author: Viktor Szépe
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

if ( function_exists( 'wp_cache_flush' ) ) {
    add_action( 'admin_bar_menu', 'o1_flush_cache_button', 100 );
}

function o1_flush_cache_button( $wp_admin_bar ) {

    // Only for administrators
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Button pressed
    if ( isset( $_GET['flush-cache-button'] )
        && 'flush' === $_GET['flush-cache-button']
        && wp_verify_nonce( $_GET['_wpnonce'], 'flush-cache-button' )
    ) {
        wp_cache_flush();
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-success is-dismissible"><p>Object Cache flushed.</p></div>';
        } );
    }

    // Display the button
    $dashboard_url = admin_url( add_query_arg( 'flush-cache-button', 'flush', 'index.php' ) );
    $args = array(
        'id'    => 'flush_cache_button',
        'title' => 'Flush Object Cache',
        'href'  => wp_nonce_url( $dashboard_url, 'flush-cache-button' ),
        'meta'  => array( 'class' => 'flush-cache-button' ),
    );
    $wp_admin_bar->add_node( $args );
}
