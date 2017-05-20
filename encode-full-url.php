<?php

/**
 * Encode all parts of any URL - full version of esc_url()
 *
 * a) relative or absolute
 * b) parse it
 * c) url_encode the parts which need encoding (relative protocol, IDN, accents, query)
 * d) re-build the url
 * e) ??? only then -> pass it on to wp_redirect()
 * +1) IDN in home and siteurl?
 *
 * @param string any URL
 * @var string encoded URL
 */
function encode_full_url( $url ) {

    // TESTS:
    // scheme http://tools.ietf.org/html/rfc3986#section-3.1
    //to lower
    //relative protocol (scheme) support
    // user, host, port http://tools.ietf.org/html/rfc3986#section-3.2
    //no user:password!
    //IDN
    //ipv4, ipv6
    // path
    //rawurlencode()
}

function urlpath_encode( $raw ) {

    return join( '/', array_map( 'rawurlencode', explode( '/', $raw ) ) );
}
