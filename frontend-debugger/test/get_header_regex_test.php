#!/usr/bin/php
<?php

$lines = 'get_header();
that_get_header();
reget_header();
get_header_nope();
 get_header();
	get_header();
get_header( );
get_header( \'\' );
get_header( "catchme" );
get_header( \'catch me\' );
get_header() ;
';

$lines = explode( "\n", $lines );

foreach( $lines as $line ) {
    echo $line . PHP_EOL;
    $pm = preg_match( '/\bget_header\s*\(.*\)\s*;/', $line, $matches );
    var_export( $pm . "->" ); var_export( $matches );
    echo PHP_EOL;
    echo PHP_EOL;
}
