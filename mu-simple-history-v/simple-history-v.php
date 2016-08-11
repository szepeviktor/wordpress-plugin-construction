<?php
/*
Plugin Name: Simple History v (MU)
Version: 0.0.2
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

// Show the history page, the history Dashboard widget, and the history settings page
// to only the users specified in $allowed_users array
add_filter( 'simple_history/show_dashboard_page', 'o1_show_history_dashboard_or_page' );
add_filter( 'simple_history/show_dashboard_widget', 'o1_show_history_dashboard_or_page' );
add_filter( 'simple_history/show_settings_page', 'o1_show_history_dashboard_or_page' );

function o1_show_history_dashboard_or_page( $show ) {

    $allowed_user_emails = array(
        'viktor@szepe.net',
    );

    $user = wp_get_current_user();
    if ( ! in_array( $user->user_email, $allowed_emails ) ) {
        $show = false;
    }

    return $show;
}
