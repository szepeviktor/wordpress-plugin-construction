<?php
/*
Plugin Name: GitHub Link
Version: 0.1.0
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Displays GitHub link on the Plugins page given there is a <code>GitHub Plugin URI</code> plugin header.
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/github-link
*/

add_filter( 'plugin_action_links', 'GHL_plugin_link', 10, 4 );

function GHL_plugin_link( $actions, $plugin_file, $plugin_data, $context ) {

    if ( 'search' !== $context ) {
        $plugin_data = get_file_data( trailingslashit( WP_PLUGIN_DIR ) . $plugin_file, array(
            "GitHubURI" => "GitHub Plugin URI",
            "GitHubBranch" => "GitHub Branch",
            "GitHubToken" => "GitHub Access Token",
            "BitbucketURI" => "Bitbucket Plugin URI",
            "BitbucketBranch" => "Bitbucket Branch"
        ) );
        $link_template = '<a href="%s" target="_blank"><img src="%s" style="vertical-align:-3px" height="16" width="16" alt="%s" />%s</a>';

        if ( ! empty( $plugin_data["GitHubURI"] ) ) {
            $icon = "icon/GitHub-Mark-32px.png";
            $branch = '';

            if ( ! empty( $plugin_data["GitHubToken"] ) )
                $icon = 'icon/GitHub-Mark-Light-32px.png" style="vertical-align:-3px;background-color:black;border-radius:50%';
            if ( ! empty( $plugin_data["GitHubBranch"] ) )
                $branch = '/' . $plugin_data["GitHubBranch"];

            $actions['github'] = sprintf(
                $link_template,
                $plugin_data["GitHubURI"],
                plugins_url( $icon, __FILE__ ),
                "GitHub",
                $branch
            );
        }

        if ( ! empty( $plugin_data["BitbucketURI"] ) ) {
            $icon = "icon/bitbucket_32_darkblue_atlassian.png";
            $branch = '';

            if ( ! empty( $plugin_data["BitbucketBranch"] ) )
                $branch = '/' . $plugin_data["BitbucketBranch"];

            $actions['bitbucket'] = sprintf(
                $link_template,
                $plugin_data["BitbucketURI"],
                plugins_url( $icon, __FILE__ ),
                "Bitbucket",
                $branch
            );
        }
    }

    return $actions;
}
