<?php
/*
Plugin Name: GitHub Link
Version: 0.1.0
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Displays GitHub link on the Plugins page given there is a <code>GitHub Plugin URI</code> plugin header.
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/github-link
*/

add_filter( 'plugin_action_links', 'GHL_plugin_link', 10, 4 );

function GHL_plugin_link( $actions, $plugin_file, $plugin_data, $context ) {

    if ( 'search' !== $context ) {
        $uri = get_file_data( trailingslashit( WP_PLUGIN_DIR ) . $plugin_file, array( "GitHubURI" => "GitHub Plugin URI" ) );

        if ( ! empty( $uri["GitHubURI"] ) ) {
            $actions['github'] = sprintf(
                '<a href="%s" target="_blank"><img src="%s" style="vertical-align:-3px" height="16" width="16" alt="GitHub" /></a>',
                $uri["GitHubURI"],
                plugins_url( "icon/GitHub-Mark-32px.png", __FILE__ )
            );
        }
    }

    return $actions;
}
