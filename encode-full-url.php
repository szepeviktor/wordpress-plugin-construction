<?php

function encode_full_url($url) {
    // a) detect whether this is a relative or absolute url
    // b) parse it
    // c) url_encode the parts which need encoding
    // d) re-build the url
    // e) only then -> pass it on to wp_redirect()

    // scheme http://tools.ietf.org/html/rfc3986#section-3.1
    to lower
    relative protocol (scheme) support
    // user, host, port http://tools.ietf.org/html/rfc3986#section-3.2
    no user:password!
    IDN
    ipv4, ipv6
    // path
    rawurlencode()
}

