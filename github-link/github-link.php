<?php
/*
Plugin Name: GitHub Link
Version: 0.3.0
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Displays GitHub link on the Plugins page given there is a <code>GitHub Plugin URI</code> plugin header.
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/github-link
*/

if ( ! function_exists( 'add_filter' ) ) {
    error_log( 'Malicious sign detected: wpf2b_direct_access '
        . addslashes( $_SERVER['REQUEST_URI'] )
    );
    ob_get_level() && ob_end_clean();
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.0 403 Forbidden' );
    exit();
}

add_filter( "extra_plugin_headers", "GHL_extra_headers" );
add_filter( "plugin_action_links", "GHL_plugin_link", 10, 4 );

function GHL_extra_headers( $extra_headers ) {

    // keys will get lost
    return array_merge( $extra_headers, array(
        "GitHubURI" => "GitHub Plugin URI",
        "GitHubBranch" => "GitHub Branch",
        "GitHubToken" => "GitHub Access Token",
        "BitbucketURI" => "Bitbucket Plugin URI",
        "BitbucketBranch" => "Bitbucket Branch"
    ) );
}

function GHL_plugin_link( $actions, $plugin_file, $plugin_data, $context ) {

    // no GitHub data on search
    if ( 'search' === $context )
        return $actions;

    $link_template = '<a href="%s" title="%s" target="_blank"><img src="%s" style="vertical-align:-3px" height="16" width="16" alt="%s" />%s</a>';

    $on_wporg = false;
    _maybe_update_plugins();
    $plugin_state = get_site_transient( 'update_plugins' );
    if ( isset( $plugin_state->response[$plugin_file] )
        || isset( $plugin_state->no_update[$plugin_file] )
    )
        $on_wporg = true;

    if ( ! empty( $plugin_data["GitHub Plugin URI"] ) ) {
        $icon = "icon/GitHub-Mark-32px.png";
        $branch = '';

        if ( ! empty( $plugin_data["GitHub Access Token"] ) )
            $icon = 'icon/GitHub-Mark-Light-32px.png" style="vertical-align:-3px;background-color:black;border-radius:50%';
        if ( ! empty( $plugin_data["GitHub Branch"] ) )
            $branch = '/' . $plugin_data["GitHub Branch"];

        $new_action = array ('github' => sprintf(
            $link_template,
            $plugin_data["GitHub Plugin URI"],
            "Visit GitHub repository",
            plugins_url( $icon, __FILE__ ),
            "GitHub",
            $branch
        ) );
        // if on WP.org + master -> put the icon after other actions
        if ( $on_wporg && ( empty( $branch ) || '/master' === $branch ) ) {
            $actions = array_merge( $actions, $new_action );
        } else {
            $actions = array_merge( $new_action, $actions );
        }
    }

    if ( ! empty( $plugin_data["Bitbucket Plugin URI"] ) ) {
        $icon = "icon/bitbucket_32_darkblue_atlassian.png";
        $branch = '';

        if ( ! empty( $plugin_data["Bitbucket Branch"] ) )
            $branch = '/' . $plugin_data["Bitbucket Branch"];

        $new_action = array('bitbucket' => sprintf(
            $link_template,
            $plugin_data["Bitbucket URI"],
            "Visit Bitbucket repository",
            plugins_url( $icon, __FILE__ ),
            "Bitbucket",
            $branch
        ) );
        // if on WP.org + master -> put the icon after other actions
        if ( $on_wporg && ( empty( $branch ) || '/master' === $branch ) ) {
            $actions = array_merge( $actions, $new_action );
        } else {
            $actions = array_merge( $new_action, $actions );
        }
    }

    return $actions;
}
