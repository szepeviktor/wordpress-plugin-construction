<?php
/*
Plugin Name: WordPress Block Bad Requests (wp-config snippet or MU plugin)
Description: Copy it in the top of your wp-config.php or make it an mu-plugin
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
Version: 1.6
Options: O1_BAD_REQUEST_COUNT, O1_BAD_REQUEST_ALLOW_REG, O1_BAD_REQUEST_ALLOW_IE8, O1_BAD_REQUEST_ALLOW_OLD_PROXIES, O1_BAD_REQUEST_ALLOW_CONNECTION_CLOSE, O1_BAD_REQUEST_ALLOW_TWO_CAPS
*/

class O1_Bad_Request {

    private $prefix = 'File does not exist: ';
    private $names2ban = array(
        'access',
        'admin',
        'administrator',
        'backup',
        'business',
        'contact',
        'data',
        'demo',
        'doctor',
        'guest',
        'info',
        'information',
        'internet',
        'master',
        'number',
        'office',
        'pass',
        'password',
        'postmaster',
        'public',
        'root',
        'sales',
        'server',
        'service',
        'test',
        'user',
        'username',
        'webmaster'
    );
    private $trigger_count = 6;
    private $allow_registration = false;
    private $allow_ie8_login = false;
    private $allow_old_proxies = false;
    private $allow_connection_close = false;
    private $allow_two_capitals = false;
    private $result = false;

    private function parse_query( $query_string ) {
        $field_strings = explode( '&', $query_string );
        $fields = array();

        foreach ( $field_strings as $field_string ) {
            $name_value = explode( '=', $field_string );

            // check field name
            if ( empty( $name_value[0] ) )
                continue;

            // set field value
            $fields[$name_value[0]] = isset( $name_value[1] ) ? $name_value[1] : '';
        }

        return $fields;
    }

    private function trigger() {
        // trigger fail2ban
        for ( $i = 0; $i < $this->trigger_count; $i++ ) {

            error_log( $this->prefix  . $this->result );
        }

        // help learning attack internals
        $server = array();
        foreach ( $_SERVER as $header => $value ) {
            if ( 'HTTP_' === substr( $header, 0, 5 ) )
                $server[$header] = $value;
        }
        error_log( 'HTTP headers: ' . serialize( $server ) );
        error_log( 'HTTP request: ' . serialize( $_REQUEST ) );

        ob_end_clean();
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.0 403 Forbidden' );
        exit();
    }

    private function check() {
        // exit on local access
        // don't run on install / upgrade
        if ( php_sapi_name() === 'cli'
            || $_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR']
            || defined( 'WP_INSTALLING' ) && WP_INSTALLING
        )
            return false;

        $request_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
        $server_name = isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];

        // author sniffing
        // don't ban on post listing by author
        if ( false === strpos( $request_path, '/wp-admin/' )
            && isset( $_GET['author'] )
            && is_numeric( $_GET['author'] ) )
            return 'bad_request_author_sniffing';

        // check only POST requests to wp-login
        if ( false === stripos( $_SERVER['REQUEST_METHOD'], 'POST' )
            || false === stripos( $request_path, '/wp-login.php' ) )
            return false;

        // --------------------------- >8 ---------------------------

        if ( ! empty($_POST['log'] ) ) {
            $username = $_POST['log'];

            // banned usernames
            if ( in_array( strtolower( $username ), $this->names2ban ) )
                return 'bad_request_banned_username';

            // attackers try usernames with "TwoCapitals"
            if ( ! $this->allow_two_capitals ) {

                if ( 1 === preg_match( '/^[A-Z][a-z]+[A-Z][a-z]+$/', $username ) )
                    return 'bad_request_username_pattern';
            }
        }

        // accept header - IE9 sends only "*/*"
        //|| false === strpos( $_SERVER['HTTP_ACCEPT'], 'text/html' )
        if ( ! isset( $_SERVER['HTTP_ACCEPT'] )
            || false === strpos( $_SERVER['HTTP_ACCEPT'], '/' )
        )
            return 'bad_request_http_post_accept';

        // accept-language header
        if ( ! isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] )
            || strlen( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) < 2
        )
            return 'bad_request_http_post_accept_language';

        // content-type header
        if ( ! isset( $_SERVER['CONTENT_TYPE'] )
            || false === strpos( $_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded' )
        )
            return 'bad_request_http_post_content_type';

        // content-length header
        if ( ! isset( $_SERVER['CONTENT_LENGTH'] )
            || ! is_numeric( $_SERVER['CONTENT_LENGTH'] )
        )
            return 'bad_request_http_post_content_length';

        // referer header (empty)
        if ( ! isset ( $_SERVER['HTTP_REFERER'] ) )
            return 'bad_request_http_post_referer_empty';

        $referer = $_SERVER['HTTP_REFERER'];

        // referer header (host only)
        if ( ! $this->allow_registration ) {

            if ( $server_name !== parse_url( $referer, PHP_URL_HOST ) )
                return 'bad_request_http_post_referer_host';
        }

        // don't ban password protected posts by the rules AFTER this one
        if ( isset( $_SERVER['QUERY_STRING'] ) ) {
            $queries = $this->parse_query( $_SERVER['QUERY_STRING'] );

            if ( isset( $queries['action'] )
                && 'postpass' === $queries['action']
            )
                return false;
        }

        // --------------------------- >8 ---------------------------

        // referer header (path)
        if ( ! $this->allow_registration ) {

            if ( false === strpos( parse_url( $referer, PHP_URL_PATH ), '/wp-login.php' ) )
                return 'bad_request_http_post_referer_path';
        }

        // protocol version
        if ( ! isset( $_SERVER['SERVER_PROTOCOL'] ) )
                return 'bad_request_http_post_protocol_empty';

        if ( ! $this->allow_old_proxies ) {

            if ( false === strpos( $_SERVER['SERVER_PROTOCOL'], 'HTTP/1.1' ) )
                return 'bad_request_http_post_1_1';
        }

        // connection header (keep alive)
        if ( ! isset( $_SERVER['HTTP_CONNECTION'] ) )
            return 'bad_request_http_post_connection_empty';

        if ( ! $this->allow_connection_close ) {

            if ( false === stripos( $_SERVER['HTTP_CONNECTION'], 'keep-alive' ) )
                return 'bad_request_http_post_connection';
        }

        // accept-encoding header
        if ( ! isset ( $_SERVER['HTTP_ACCEPT_ENCODING'] )
            || false === strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' )
        )
            return 'bad_request_http_post_accept_encoding';

        // cookie
        if ( ! $this->allow_registration ) {

            if ( ! isset( $_SERVER['HTTP_COOKIE'] )
                || false === strpos( $_SERVER['HTTP_COOKIE'], 'wordpress_test_cookie' )
            )
                return 'bad_request_http_post_test_cookie';
        }

        // empty user agent
        if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            return 'bad_request_http_post_user_agent';
        }

        // IE8 logins
        if ( $this->allow_ie8_login ) {

            if ( 1 === preg_match( '/^Mozilla\/4\.0\ \(compatible; MSIE 8\.0;/', $user_agent ) )
                return false;
        }

        // botnets
        if ( 1 === preg_match('/Firefox\/1|bot|spider|crawl|user-agent/i', $user_agent ) )
            return 'bad_request_http_post_user_agent_botnet';

        // modern browsers
        if ( 1 !== preg_match( '/^Mozilla\/5\.0/', $user_agent ) )
            return 'bad_request_http_post_user_agent_mozilla_5_0';

        // OK
        return false;
    }

    function __construct() {
        // options
        if ( defined( 'O1_BAD_REQUEST_COUNT' ) )
            $this->trigger_count = intval( O1_BAD_REQUEST_COUNT );

        if ( defined( 'O1_BAD_REQUEST_ALLOW_REG' ) && O1_BAD_REQUEST_ALLOW_REG )
            $this->allow_registration = true;

        if ( defined( 'O1_BAD_REQUEST_ALLOW_IE8' ) && O1_BAD_REQUEST_ALLOW_IE8 )
            $this->allow_ie8_login = true;

        if ( defined( 'O1_BAD_REQUEST_ALLOW_OLD_PROXIES' ) && O1_BAD_REQUEST_ALLOW_OLD_PROXIES )
            $this->allow_old_proxies = true;

        if ( defined( 'O1_BAD_REQUEST_ALLOW_CONNECTION_CLOSE' ) && O1_BAD_REQUEST_ALLOW_CONNECTION_CLOSE )
            $this->allow_connection_close = true;

        if ( defined( 'O1_BAD_REQUEST_ALLOW_TWO_CAPS' ) && O1_BAD_REQUEST_ALLOW_TWO_CAPS )
            $this->allow_two_capitals = true;

        $this->result = $this->check();

        //DEBUG echo '<pre>blocked by O1_Bad_Request, reason: <strong>'.$this->result;error_log('Bad_Request:'.$this->result);return;

        // false means NO bad requests
        if ( false !== $this->result )
            $this->trigger();
    }

}

new O1_Bad_Request();

/*TODO
readme: snippet copy, require_once( dirname( __FILE__ ) . '/wp-login-bad-request.inc.php' );, mu-plugin, plugin
php-doc

*/
