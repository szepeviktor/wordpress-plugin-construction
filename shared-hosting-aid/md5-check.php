<pre><?php
/*
Snippet Name: Verify MD5 checksums
Version: 1.0.0
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
*/

/* Shell script

WP_VER="4.6.1"
wget https://downloads.wordpress.org/release/hu_HU/wordpress-${WP_VER}.tar.gz | tar -xz
cd wordpress/
find -type f -exec md5sum "{}" ";" > ../wordpress-${WP_VER}-hu_HU.md5

*/

// @TODO https://api.wordpress.org/core/checksums/1.0/?version=4.6.1&locale=hu_HU
md5_check( '../wordpress-4.6.1-hu_HU.md5' );

function md5_check( $list_file ) {

    $list = file_get_contents( $list_file );
    $list_lines = explode( "\n", trim( $list ) );

    foreach ( $list_lines as $line ) {
        $parts = explode( ' ', $line );
        $md5 = $parts[0];
        $file = $parts[2];

        $current_md5 = md5_file( $file );

        if ( $current_md5 !== $md5 ) {
            printf( "File doesn't verify against checksum: %s\n", $file );
        }
    }
}
