#!/usr/bin/env php
<?php
/*
Snippet Name: export-one-db decryption.
Version: 0.2
Description: Print the command line that decrypts and expands the dump.
Author: Viktor Szépe <viktor@szepe.net>
Snippet URI: https://github.com/szepeviktor/debian-server-tools
Idea: http://php.net/manual/en/function.openssl-encrypt.php#104438
*/

/**
 * Convert a string to hexadecimal representation.
 *
 * For openssl enc.
 */
function strtohex( $str ) {
    $hex = '';
    foreach ( str_split( $str ) as $char ) {
        $hex .= sprintf( '%02X', ord( $char ) );
    }

    return $hex;
}

function exit_message( $code, $msg ) {
    error_log( $msg );
    exit( $code );
}

if ( 'cli' !== php_sapi_name() || 4 !== count( $argv ) ) {
    exit_message( 1, './exp-o-decrypt.php <PASSWORD> <IV> <PRIVATE-KEY-FILE>' . PHP_EOL );
}
if ( empty( $argv[1] ) || empty( $argv[2] ) || ! file_exists( $argv[3] ) ) {
    exit_message( 2, 'Invalid parameters.' . PHP_EOL ) ;
}

# Base64 encoded password from the "X-Password" header
$password64 = $argv[1];
# Initialization Vector
$iv = $argv[2];
# Private key file
$private_key = file_get_contents( $argv[3] );

$enc_password = base64_decode( $password64 );
if ( false === $enc_password ) {
    exit_message( 3, 'Invalid password. It has to be base64 encoded.' . PHP_EOL );
}

$decryption = openssl_private_decrypt ( $enc_password , $decrypted , $private_key );
if ( false === $decryption ) {
    exit_message( 4, 'Decryption failed.' . PHP_EOL );
}

# Removed "-nopad"
printf( "openssl enc -aes-128-cbc -d -nosalt -K %s -iv %s -in " . PHP_EOL,
    strtohex ( $decrypted ),
    strtohex( $iv )
);
