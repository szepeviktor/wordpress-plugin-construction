<?php
/*
Snippet Name: wpml-config.xml admin-texts generator
Version: 0.1.0
Description: Convert an option to wpml-config.xml keys
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
Author: Viktor Szépe <viktor@szepe.net>
License: The MIT License (MIT)
Depends: WP-CLI
Usage: WPOPTION="OPTION-NAME" wp eval-file option-array-to-wpml-config.php > wpml-config.xml
*/

if ( empty( $_SERVER['WPOPTION'] ) || ! function_exists( 'get_option' ) ) {
    exit( 1 );
}

$option_name = $_SERVER['WPOPTION'];
$option_value = get_option( $option_name );

print "<wpml-config>\n    <admin-texts>\n";
recursive_process_wpml( array( $option_name => $option_value ), 2 );
print "    </admin-texts>\n</wpml-config>\n";

function recursive_process_wpml( $array, $level = 0 ) {

    foreach ( $array as $key => $value ) {
        if ( is_array( $value ) ) {
            output_wpml_key( $key, $level, false );
            recursive_process_wpml( $value, $level + 1 );
            output_wpml_key_close( $key, $level );
        } else {
            output_wpml_key( $key, $level );
        }
    }
}

function output_wpml_key( $name, $level, $self_closing = true ) {

    $padding = $level * 4;
    $close = $self_closing ? ' /' : '';

    printf( "%{$padding}s<key name=\"%s\"%s>\n", '', $name, $close );
}

function output_wpml_key_close( $name, $level ) {

    $padding = $level * 4;

    printf( "%{$padding}s</key>\n", ' ', $name );
}
