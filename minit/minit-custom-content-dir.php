<?php
/*
Plugin Name: Minit custom content dir (MU)
Version: 0.1.0
Description: Enable custom wp-content directory for Minit.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

add_filter( 'minit-asset-local-path', 'minit_custom_content_dir', 10, 2 );
function minit_custom_content_dir( $local_path, $item_url ) {

    // Bail out if get_local_path_from_url() succeeded
    if ( false !== $local_path ) {
        return $local_path;
    }

    // Replace custom MU plugin URL
    $full_path = str_replace( WPMU_PLUGIN_URL, WPMU_PLUGIN_DIR, $item_url );
    // Replace custom plugin URL
    $full_path = str_replace( plugins_url(), WP_PLUGIN_DIR, $full_path );
    // Replace custom theme URL
    $full_path = str_replace( get_theme_root_uri(), get_theme_root(), $full_path );
    // Replace remaining custom wp-content URL
    $full_path = str_replace( content_url(), WP_CONTENT_DIR, $full_path );

    if ( file_exists( $full_path ) ) {
        return $full_path;
    }

    return false;
}

// Purge W3TC Page Cache
add_action( 'minit-cache-purged', 'minit_flush_w3tc_on_minit_purge' );
function minit_flush_w3tc_on_minit_purge() {

    if ( function_exists( 'w3tc_pgcache_flush' ) ) {
        w3tc_pgcache_flush();
    }
}

// TO BE included: https://github.com/kasparsd/minit/issues/103
// Exclude handles that are known to cause problems
add_filter( 'minit-exclude-js', 'minit_exclude_defaults' );
function minit_exclude_defaults( $handles ) {

    $exclude = array(
        'jquery',
    );

    return array_merge( $exclude, $handles );
}
