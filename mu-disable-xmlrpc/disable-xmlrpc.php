<?php
/**
 * Disable XML-RPC.
 *
 * XML-RPC in WordPress core includes
 * - <link rel="pingback"> in header.php of the current theme
 * - RSD link (EditURI)
 * - pingbacks
 * - methods requiring authentication
 * - methods requiring no authentication (e.g. pingback)
 * - /trackback/ URLs
 * - /wp-trackback.php
 *
 * @package          Disablexmlrpc
 * @author           Viktor Szépe <viktor@szepe.net>
 * @link             https://github.com/szepeviktor/wordpress-plugin-construction
 *
 * @wordpress-plugin
 * Plugin Name: Disable XML-RPC (MU)
 * Version:     0.1.0
 * Description: Completely disable XML-RPC in core: both links and processing.
 * Plugin URI:  https://github.com/szepeviktor/wordpress-plugin-construction
 * License:     The MIT License (MIT)
 * Author:      Viktor Szépe
 */

add_action('init', function () {
    add_filter('bloginfo_url', function ($output, $property) {
        // May cause pingbacks to be sent to the home URL.
        return ($property === 'pingback_url') ? '' : $output;
    }, PHP_INT_MAX, 2);
    remove_action('wp_head', 'rsd_link');
    add_filter('pings_open', '__return_false', PHP_INT_MAX, 0);

    // Prevent processing of trackbacks.
    add_action('wp', function () {
        if (! is_trackback()) {
            return;
        }
        // Forbidden.
        status_header(403);
        header('Content-Type: text/xml; charset=UTF-8');
        nocache_headers();
        // Respond with closed message.
        $response = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><response />');
        $response->addChild('error', '1');
        $response->addChild('message', 'Sorry, trackbacks are closed.');
        print $response->asXML();
        exit;
    }, -1, 0);

    // Catch define('XMLRPC_REQUEST', true) in xmlrpc.php.
    if (! defined('XMLRPC_REQUEST')) {
        return;
    }
    // Forbidden.
    status_header(403);
    header('Content-Type: text/xml; charset=UTF-8');
    nocache_headers();
    // Respond with parse error.
    require_once ABSPATH . WPINC . '/IXR/class-IXR-error.php';
    print (new \IXR_Error(-32700, 'parse error. not well formed'))->getXml();
    exit;
}, -1, 0);
