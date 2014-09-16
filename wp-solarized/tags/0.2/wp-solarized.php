<?php
/**
 * Plugin Name: WordPress Solarized
 * Plugin URI: http://wordpress.org/plugins/wp-solarized/
 * Description: Make the Dashboard Solarized
 * Version: 0.2
 * Author: Viktor Szépe
 * Author URI: http://www.online1.hu/webdesign/
 * License: GNU General Public License (GPL) version 2
 */

add_action( 'admin_init', 'solarized_colors' );

function solarized_tiny_mce_css( $mce_css ) {
    $mce_css .= ',' . plugins_url( 'css/tiny-mce.css', __FILE__ );

    return $mce_css;
}

function solarized_colors() {
    add_filter( 'mce_css', 'solarized_tiny_mce_css' );

    $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.css' : '.min.css';

    wp_admin_css_color(
        'solarized',
        __( 'Solarized' ),
        plugins_url( 'css/colors' . $suffix, __FILE__ ),
        // $base-color, $menu-submenu-background, $menu-submenu-focus-text, $notification-color
        array( '#657b83', '#586e75', '#fdf6e3', '#859900' ),
        // $icon-color, icon/focus, icon/current
        array( 'base' => '#93a1a1', 'focus' => '#586e75', 'current' => '#657b83' )
    );
}
