<?php
/*
Plugin Name: Enhance 404 pages (MU)
Version: 0.1.0
Description: Google 404 widget shortcode.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: GPLv2 or later
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

add_action( 'init', 'linkhelp_register_shortcodes' );

function linkhelp_register_shortcodes() {

    add_shortcode( 'linkhelp', 'linkhelp_shortcode' );
}

function linkhelp_shortcode( $attr, $content, $shortcode_tag ) {

    $attr = shortcode_atts( array(
        'lang' => get_bloginfo( 'language' ),
    ), $attr, $shortcode_tag );

    $linkhelp_tpl = <<<'EOT'
<script type="text/javascript">
    var GOOG_FIXURL_LANG = "%s";
    var GOOG_FIXURL_SITE = "%s";
</script>
<script type="text/javascript" src="https://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js"></script>
EOT;

    return sprintf( $linkhelp_tpl, $attr['lang'], esc_url( home_url() ) );
}
