<?php
/*
Plugin Name: Disallow crawling (MU)
Version: 0.2.0
Description: Prevent blog_public from being "1" and add X-Robots header.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Author: Viktor Szépe
License: GNU General Public License (GPL) version 2
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

if ( ! is_admin() ) {
    add_filter( 'option_blog_public', '__return_zero', 4294967295 );

    add_action( 'send_headers', function () {
        header( 'X-Robots-Tag: noindex, nofollow', true );
    }, 4294967295 );
}

// Also create robots.txt: echo -e "User-agent: *\nDisallow: /" > robots.txt
