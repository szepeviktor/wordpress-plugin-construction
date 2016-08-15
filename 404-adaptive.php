<?php
/**
 * Adaptive 404 response
 *
 * PHP version 5.4+
 *
 * @package    Adaptive 404
 * @version    0.1.1
 * @license    MIT
 * @author     Viktor Szépe <viktor@szepe.net>
 * @link       https://github.com/mathiasbynens/small
 */

/**
 * Send adaptive response when content is not found.
 */
class Adaptive_404 {

    /**
     * HTML response body
     */
    private $html = '';

    /**
     * Determine response type and send it.
     *
     * param string $custom_html Optional HTML reponse body
     *
     * @return void
     */
    public function __construct( $custom_html = '' ) {

        $this->set_html( $custom_html );

        if ( '/robots.txt' === $_SERVER['REQUEST_URI'] ) {
            $this->respond( "User-agent: *\nDisallow: /\n", 'text/plain' );
        }

        // Detect AJAX requests
        if ( $this->is_ajax() ) {
            $ext = 'AJAX';
        } else {
            // Find file extension in the request
            $path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
            $ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
        }

        // respond() exists so no break-s
        switch ( $ext ) {
            // AJAX & JSON
            case 'AJAX':
            case 'json':
                $this->respond( '0', 'application/json' );
            case 'jsonp':
                // @TODO JSONP url?callback=function
                $this->respond( '_([])', 'application/json' );

            // XML & Feeds
            case 'atom':
            case 'rdf':
            case 'rss':
            case 'xml':
                $this->respond( '<?xml version="1.1"?><!DOCTYPE _[<!ELEMENT _ EMPTY>]><_/>', 'application/xml' );

            // Stylesheet
            case 'css':
                $this->respond( '', 'text/css' );

            // Javascript
            case 'js':
                $this->respond( '', 'application/javascript' );

            // Images
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'png':
            case 'ico':
            case 'cur':
            case 'webp':
            case 'svg':
                $this->respond( base64_decode( 'R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==' ), 'image/gif' );

            // Fonts
            case 'ttf':
            case 'ttc':
            case 'otf':
            case 'eot':
            case 'woff':
            case 'woff2':
                $this->respond( gzdecode( base64_decode( 'H4sIACaMS1cCA4VSzytEURg9971nMqOwkFLSJAsLvxekJklKihRlocQ1nhmaN++ZGWWymYWytRULC7bKXvkT7KwsRP4EWek5991rMgy+1333nHO/e77vvnchADShAhtDC0uDI43dsXsqNxzraU8GGME2IDrJpzOyGHBuJV/n3JjJlbds+/KaPEueyrpy8/20ckB+QT6apSCexAv5A3lP1ivtIwpLvVpzflqiF23k9ETCk/sBYmggV/WSeem5KyerHeQpeqwGfrHEPhn2mVqHbUEcq3wxJ7aozJj5FR04RG0IfI9J5SEeQz7ikW61Oclqo6piG98imh0MqbPzsaIdyfA9DPWOCInw2eR+1hVUW74o3Dc2Lmp7+l/jKLzFd9ZaUq9oir4f7ibOb7/OJnRnZp86H6rCj6/goF4keM4uk+1UT2PVyWzGlVkVaK/6W9TTBtvU+wx2iHcNbiBuNzjGGzhlcII34kjVdOLGU2PtqbH21Fh7aqw9NY4RzRusPYsIIOnhop/DIyuhjGXiAte24SPPvz6MAf7hurnFQKbdftcLSuVfzBaJM9hDjmuFX3JmiPNRUUnuYpNFN6gnMctcn037USvm5v0RH8qTayO8AwAA' ) ), 'application/x-font-ttf' );

            // Audio & Video
            case 'mp3':
            case 'mp4':
            case 'ogg':
            case 'wav':
            case 'webm':
                $this->respond( base64_decode( 'GkXfo0AgQoaBAUL3gQFC8oEEQvOBCEKCQAR3ZWJtQoeBAkKFgQIYU4BnQI0VSalmQCgq17FAAw9CQE2AQAZ3aGFtbXlXQUAGd2hhbW15RIlACECPQAAAAAAAFlSua0AxrkAu14EBY8WBAZyBACK1nEADdW5khkAFVl9WUDglhohAA1ZQOIOBAeBABrCBCLqBCB9DtnVAIueBAKNAHIEAAIAwAQCdASoIAAgAAUAmJaQAA3AA/vz0AAA=' ), 'video/webm' );

            // HTML
            case 'html':
            case 'htm':
                $this->respond( $this->html );

            // Could be a SEF URL
            default:
                if ( $this->accept_html() ) {
                    $this->respond( $this->html );
                } else {
                    $this->respond( '404', 'text/plain' );
                }
        }
    }

    /**
     * Is it an AJAX resuest?
     *
     * @return boolean
     */
    private function is_ajax() {

        return ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] )
            && 'xmlhttprequest' === strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] );
    }

    /**
     * Does the client accept an HTML response?
     *
     * @return boolean
     */
    private function accept_html() {

        return ! empty( $_SERVER['HTTP_ACCEPT'] )
            && false !== stripos( $_SERVER['HTTP_ACCEPT'], 'text/html' );
    }

    /**
     * Send the response to the client.
     *
     * param string $response_content Response body
     * param string $content_type     Response content type
     *
     * @return void
     */
    private function respond( $response_content = '', $content_type = 'text/html; charset=UTF-8' ) {

        // Flush output buffer
        ob_get_level() && ob_end_clean();

        if ( headers_sent() ) {
            exit( '404' );
        }

        http_response_code( 404 );

        $response_headers = array(
            // 'Status' for proxy servers
            'Status'        => '404 Not Found',
            // Stolen from WordPress core
            'Expires'       => 'Wed, 11 Jan 1984 05:00:00 GMT',
            'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'X-Robots-Tag'  => 'noindex, nofollow',
            'Content-Type'  => $content_type,
        );
        foreach ( $response_headers as $name => $value ) {
            header( sprintf( '%s: %s', $name, $value ), true );
        }

        exit( $response_content );
    }

    /**
     * Set custom of default HTML reponse body.
     *
     * @param string $custom_html HTML reponse body
     *
     * @return void
     */
    private function set_html( $custom_html ) {

        // Apache's default 404 page
        $default_html_tpl = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL %s was not found on this server.</p>
</body></html>';

        if ( empty( $custom_html ) ) {
            $this->html = sprintf( $default_html_tpl, htmlspecialchars( urldecode( $_SERVER['REQUEST_URI'] ) ) );
        } else {
            $this->html = $custom_html;
        }
    }
}
