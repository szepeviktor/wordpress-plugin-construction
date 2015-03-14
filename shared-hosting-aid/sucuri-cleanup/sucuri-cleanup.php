<?php
/*
Plugin Name: Sucuri Scanner Firewall menu hider
Description: Hide Firewall menu and Plugin advertisements.
Version: 1.1.0
*/

add_action( 'admin_menu', 'o1_sucuri_remove_firewall', 0 );
add_filter( 'pre_option_' . 'sucuriscan_ads_visibility', 'o1_sucuri_ads_visibility', 9999 );
add_filter( 'pre_update_option_' . 'sucuriscan_ads_visibility', 'o1_sucuri_ads_visibility', 9999 );

function o1_sucuri_remove_firewall() {
    global $sucuriscan_pages;

    unset( $sucuriscan_pages['sucuriscan_monitoring'] );
}

function o1_sucuri_ads_visibility( $value ) {

    return 'disabled';
}
