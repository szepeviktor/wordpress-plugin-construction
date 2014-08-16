<?php
/*
Snippet Name: WordPress Bad Request
Description: Copy it in the top of your wp-config.php
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Author: Viktor Szépe
Author URI: http://www.online1.hu/
Version: 1.5
*/

class O1_Bad_Request {

    const COUNT = 6;

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

    private function check() {
        // exit on local access
        if ( php_sapi_name() === 'cli'
            || $_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR'] )
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

            // attackers use usernames with "TwoCapitals"
            if ( 1 === preg_match( '/^[A-Z][a-z]+[A-Z][a-z]+$/', $username ) )
                return 'bad_request_username_pattern';
        }

        // accept header - IE9 sends only "*/*"
        //|| false === strpos( $_SERVER['HTTP_ACCEPT'], 'text/html' )
        if ( ! isset( $_SERVER['HTTP_ACCEPT'] )
            || false === strpos( $_SERVER['HTTP_ACCEPT'], '/' ) )
            return 'bad_request_http_post_accept';

        // accept-language header
        if ( ! isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] )
            || strlen( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) < 2 )
            return 'bad_request_http_post_accept_language';

        // content-type header
        if ( ! isset( $_SERVER['CONTENT_TYPE'] )
            || false === strpos( $_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded' ) )
            return 'bad_request_http_post_content_type';

        // content-length header
        if ( ! isset( $_SERVER['CONTENT_LENGTH'] )
            || ! is_numeric( $_SERVER['CONTENT_LENGTH'] ) )
            return 'bad_request_http_post_content_length';

        // referer header
        // COMMENT OUT on 'Allow anyone to register'
        if ( ! isset ( $_SERVER['HTTP_REFERER'] )
            || $server_name !== parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_HOST )
            || false === strpos( parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_PATH ), '/wp-login.php' ) )
            return 'bad_request_http_post_referer';

        // don't ban password protected posts by the rules AFTER this one
        if ( isset( $_SERVER['QUERY_STRING'] ) ) {
            $queries = $this->parse_query( $_SERVER['QUERY_STRING'] );

            if ( isset( $queries['action'] )
                && 'postpass' === $queries['action'] )
                return false;
        }

        // --------------------------- >8 ---------------------------

        // protocol version
        // COMMENT OUT to allow old proxy servers (HTTP/1.0)
        if ( ! isset( $_SERVER['SERVER_PROTOCOL'] )
            || false === strpos( $_SERVER['SERVER_PROTOCOL'], 'HTTP/1.1' ) )
            return 'bad_request_http_post_1_1';

        // connection header
        if ( ! isset( $_SERVER['HTTP_CONNECTION'] )
            || false === stripos( $_SERVER['HTTP_CONNECTION'], 'keep-alive' ) )
            return 'bad_request_http_post_keep_alive';

        // accept-encoding header
        if ( ! isset ( $_SERVER['HTTP_ACCEPT_ENCODING'] )
            || false === strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) )
            return 'bad_request_http_post_accept_encoding';

        // cookie
        // COMMENT OUT on 'Allow anyone to register'
        if ( ! isset( $_SERVER['HTTP_COOKIE'] )
            || false === strpos( $_SERVER['HTTP_COOKIE'], 'wordpress_test_cookie' ) )
            return 'bad_request_http_post_test_cookie';

        // empty user agent
        if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
        } else {
            return 'bad_request_http_post_user_agent';
        }

        // COMMENTS out on 'Allow anyone to register'
        /*
        // allow IE8 logins
        if ( 1 === preg_match( '/^Mozilla\/4\.0\ \(compatible; MSIE 8\.0;/', $user_agent ) )
            return false;
        */

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

        $result = $this->check();

        // check result
        if ( false === $result )
            return;

        //DEBUG echo '<pre>blocked by O1_Bad_Request, reason: <strong>' . $result; return;

        // trigger fail2ban
        for ( $i = 0; $i < self::COUNT; $i++ )
            error_log( $this->prefix  . $result );

        ob_end_clean();
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.0 403 Forbidden' );
        exit();
    }

}

new O1_Bad_Request();

