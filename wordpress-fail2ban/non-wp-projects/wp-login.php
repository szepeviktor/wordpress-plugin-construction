<?php
/*
Snippet Name: Triggers fail2ban in non-WordPress projects
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Copy this file into a non-WordPress project's root
Version: 0.1
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
*/


for ( $i = 1; $i <= 6; $i++ ) {
    error_log( 'File does not exist: ' . 'errorlog_no_wp_here' );
}

exit(1);
