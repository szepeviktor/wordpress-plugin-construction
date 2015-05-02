<?php
/*
Plugin Name: Not me! for Simple History MU
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Change "You" to your username and email in Logged in and Logged out log items.
Version: 1.0
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-simple-history-not-me
*/

function simple_history_not_me( $tmpl ) {
    $tmpl = '
        <strong class="SimpleHistoryLogitem__inlineDivided">%3$s</strong>
        <span class="SimpleHistoryLogitem__inlineDivided SimpleHistoryLogitem__headerEmail">%2$s</span>
        ';

    return $tmpl;
}

add_filter( 'simple_history/header_initiator_html_existing_user', 'simple_history_not_me' );
