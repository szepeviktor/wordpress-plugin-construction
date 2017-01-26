<?php
/*
Plugin Name: Not me! for Simple History (MU)
Version: 2.0.0
Description: Change "You" to your username and set log level to critical for aliens.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

add_filter( 'simple_history/log_insert_data', function ( $data ) {

    // EDIT
    $my_ip = '1.2.3.4';

    if ( ! empty( $_SERVER['REMOTE_ADDR'] ) && $my_ip !== $_SERVER['REMOTE_ADDR'] ) {
        $data['level'] = 'critical';
    }

    return $data;
} );

add_filter( 'simple_history/header_initiator_html_existing_user', function ( $tmpl ) {

    $tmpl = '
        <strong class="SimpleHistoryLogitem__inlineDivided">%3$s</strong>
        <span class="SimpleHistoryLogitem__inlineDivided SimpleHistoryLogitem__headerEmail">%2$s</span>
    ';

    return $tmpl;
} );
