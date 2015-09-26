<?php
/*
Snippet name: Display only errors and warnings from Sucuri Sitecheck
*/

$site_url = "http://domain.tld/";

define( 'SUCURI_SITECHECK_URL', "http://sitecheck.sucuri.net/?fromwp=2&clear=1&json=1&scan=" );

$importants = array( "ERROR", "WARN" );

$json = file_get_contents( SUCURI_SITECHECK_URL . $site_url );
if ( false === $json ) {
    exit( 'Sitecheck response error.' );
}
$check_data = json_decode( $json, true );
if ( false === $check_data ) {
    exit( 'JSON decode failure.' );
}

foreach ( $check_data as $section_title => $section ) {
    foreach ( $section as $level => $item ) {
        if ( in_array( $level, $importants, true ) ) {
            foreach ( $item as $message ) {
                echo strip_tags( html_entity_decode( implode( "\n", (array)$message ) ) ) . "\n";
            }
        }
    }
}
