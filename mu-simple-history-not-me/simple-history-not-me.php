<?php
/*
Plugin Name: Not me! for Simple History (MU)
Version: 1.0.1
Description: Change "You" to your username and email in "Logged in" and "Logged out" log items.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

add_filter( 'simple_history/header_initiator_html_existing_user', 'o1_simple_history_not_me' );

function o1_simple_history_not_me( $tmpl ) {

    $tmpl = '
        <strong class="SimpleHistoryLogitem__inlineDivided">%3$s</strong>
        <span class="SimpleHistoryLogitem__inlineDivided SimpleHistoryLogitem__headerEmail">%2$s</span>
    ';

    return $tmpl;
}
