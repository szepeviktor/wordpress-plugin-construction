<?php

function wp_safe_salt() {
    // Shell equivalent
    //    apg -a 1 -n 8 -m 64 -M SNCL -E "'\"\\"

    // ASCII characters 32-126 excluding:  '  "  \
    //for($c=32;$c<=126;$c+=1)switch($c){case 34:break;case 39:break;case 92:break;default:echo chr($c);}
    $chars = ' !#$%&()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{|}~';
    $chars_array = str_split( $chars );

    $crypto_strong = null;
    $salt_array = array();

    while ( true !== $crypto_strong || count( $salt_array ) < 64 ) {
        $rnd_string = openssl_random_pseudo_bytes( 256, $crypto_strong );
        $rnd_array = str_split( $rnd_string );
        $salt_array = array_intersect( $rnd_array, $chars_array );
    }

    $salt = implode( '', array_slice( $salt_array, 0, 64 ) );

    return $salt;
}

printf( "define( 'AUTH_KEY',         '%s' );\n", wp_safe_salt() );
printf( "define( 'SECURE_AUTH_KEY',  '%s' );\n", wp_safe_salt() );
printf( "define( 'LOGGED_IN_KEY',    '%s' );\n", wp_safe_salt() );
printf( "define( 'NONCE_KEY',        '%s' );\n", wp_safe_salt() );
printf( "define( 'AUTH_SALT',        '%s' );\n", wp_safe_salt() );
printf( "define( 'SECURE_AUTH_SALT', '%s' );\n", wp_safe_salt() );
printf( "define( 'LOGGED_IN_SALT',   '%s' );\n", wp_safe_salt() );
printf( "define( 'NONCE_SALT',       '%s' );\n", wp_safe_salt() );
