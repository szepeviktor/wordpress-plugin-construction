<?php
/*
Snippet Name: Code blocks for debugging
Version: 0.1.1
Description: Use these blocks to find errors in PHP code.
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

/*
Trace of all PHP-FPM processes
    strace -s 200 -p $(pgrep -u root php5-fpm) -f 2>&1 | tee php-fpm.trc
*/


// Is it executed?
echo "<!-- MARK -|-|- {$var} -->" . PHP_EOL; // FIXME


// Is it executed?
error_log( ' -- MARK -- ' . serialize( $var ) ); // FIXME


// What files were executed?
register_shutdown_function( function () {
    echo "<!-- \n";
    foreach ( get_included_files() as $i => $path ) {
        printf( '%04d: %s%s', $i, $path, "\n" );
    }
    echo " -->\n";
    ob_flush();
}); // FIXME


// Where does this get called from?
echo "\nTrace: "; var_dump( debug_backtrace() ); exit; // FIXME
