<?php
/*
Plugin Name: Remove Sucuri Firewall menu
Version: 1.0.0
*/

add_action( 'admin_menu', 'remove_sucuri_firewall', 1 );

function remove_sucuri_firewall() {
    global $sucuriscan_pages;

    unset( $sucuriscan_pages['sucuriscan_monitoring'] );
}
