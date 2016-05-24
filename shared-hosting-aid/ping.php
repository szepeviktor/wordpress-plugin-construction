<?php
/*
Snippet Name: Check PHP and MySQL server version and server time
Version: 1.1.1
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
*/

$management_server_ip = '@@MANAGEMENT_SERVER_IP@@';

// IP access control
if ( $management_server_ip !== $_SERVER['REMOTE_ADDR'] ) {
    if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
        $referer = sprintf( ', referer:%s', addslashes( $_SERVER['HTTP_REFERER'] ) );
    } else {
        $referer = '';
    }
    error_log( sprintf( '[error] [client %s:%s] %s%s%s',
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['REMOTE_PORT'],
        'Malicious traffic detected: ping_extraneous_access ',
        addslashes( $_SERVER['REQUEST_URI'] ),
        $referer
    ) );
    ob_get_level() && ob_end_clean();
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden', true, 403 );
    header( 'Connection: Close' );
    header( 'Cache-Control: max-age=0, private, no-store, no-cache, must-revalidate' );
    header( 'X-Robots-Tag: noindex, nofollow' );
    header( 'Content-Type: text/html' );
    header( 'Content-Length: 0' );
    exit;
}

// Server time check
if ( isset( $_GET['time'] ) ) {
    $offset = abs( (int) $_GET['time'] - time() );
    if ( $offset > 1 ) {
        exit( 'time!' );
    }
}

// Load WordPress
define( 'WP_USE_THEMES', false );
$wpload_path = dirname( __FILE__ ) . '/wp-load.php';
require_once $wpload_path;

global $wpdb;
$mysql_version_query = "SHOW VARIABLES LIKE 'version'";
$pong = sprintf( '%s|%s',
    phpversion(),
    $wpdb->get_var( $mysql_version_query, 1 )
);

// DGB exit( $pong );
exit( md5( $pong ) );
