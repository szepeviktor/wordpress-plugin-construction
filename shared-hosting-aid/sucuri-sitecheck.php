<?php

$site = 'domain.tld';

define( 'SUCURI_SITECHECK_URL', "http://sitecheck.sucuri.net/?fromwp=2&clear=1&json=1&scan=" );

$json = file_get_contents( SUCURI_SITECHECK_URL . $site );
$check_data = json_decode( $json, true );
$importants = array( "ERROR", "WARN" );

foreach ( $check_data as $section_title => $section ) {
    foreach ( $section as $level => $item ) {
        if ( in_array( $level, $importants, true ) ) {
            foreach ( $item as $message ) {
                echo strip_tags( html_entity_decode( implode( "\n", (array)$message ) ) ) . "\n";
            }
        }
    }
}
