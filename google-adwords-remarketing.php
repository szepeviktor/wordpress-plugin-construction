<?php
/*
Plugin Name: Google Adwords Remarketing
Description: Tag your site for remarketing.
Version: 0.1.0
Plugin URI: https://support.google.com/adwords/answer/3103357
*/

add_action( 'wp_enqueue_scripts', function () {

    global $_google_conversion_id;

    // EDIT
    $_google_conversion_id = 000000000;

    $_google_custom_params = 'window.google_tag_params';
    $_google_remarketing_only = 'true';

    $variables_template = '
/* <![CDATA[ */
// Google Adwords Remarketing
var google_conversion_id = %d;
var google_custom_params = %s;
var google_remarketing_only = %s;
/* ]]> */';
    $variables = sprintf( $variables_template, $_google_conversion_id, $_google_custom_params, $_google_remarketing_only );

    // Enqueue in the footer
    wp_enqueue_script( 'googole_remarketing', '//www.googleadservices.com/pagead/conversion.js', array(), null, true );
    wp_add_inline_script( 'googole_remarketing', $variables, 'before' );
}, 51 );

add_action( 'wp_footer', function () {

    global $_google_conversion_id;

    $noscript_template = '
<!-- Google Remarketing --><noscript><div style="display:inline;"><img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/%d/?guid=ON&amp;script=0"/></div></noscript>';

    printf( $noscript_template, $_google_conversion_id );
}, 51 );
