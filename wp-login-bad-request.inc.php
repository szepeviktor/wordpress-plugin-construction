<?php
/*
Snippet Name: WordPress Bad Request
Description: Copy it in the top of your wp-config.php
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Author: Viktor Szépe
Author URI: http://www.online1.hu/
Version: 1.4
*/

$bad_request_count = 6;

function bad_request_parse_query( $query ) {
    $query_parts = explode( '&', $query );
    $params = array();

    foreach ( $query_parts as $param ) {
        $item = explode( '=', $param );
        if ( ! empty( $item[0] ) ) {
            $params[$item[0]] = isset( $item[1] ) ? $item[1] : '';
        }
    }
    return $params;
}

function bad_request() {
    $m = array();
    $names2ban = array(
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

    // local access
    if ( php_sapi_name() === 'cli'
        || $_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR'] ) {
        return false;
    }

    // don't ban on post listing by author
    if ( strpos( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/wp-admin/' ) === false
        && intval( @$_GET['author'] ) ) {
        return 'author-sniffing';
    }

    if ( stripos( $_SERVER['REQUEST_METHOD'], 'POST' ) !== false
        //FIXME path only
        && stripos( $_SERVER['REQUEST_URI'], '/wp-login.php' ) !== false ) {
        // POST data
        if (!empty($_POST['log'])) {
            $username = $_POST['log'];
            if ( in_array( strtolower( $username ), $names2ban, true ) ) {
                return 'login_banned-username';
            }
            if ( preg_match( '/^[A-Z][a-z]+[A-Z][a-z]+$/', $username, $m ) === 1 ) {
                return 'login_UserName-pattern';
            }
        }
        // accept header - IE9 send only "*/*"
        //if ( strpos( @$_SERVER['HTTP_ACCEPT'], 'text/html' ) === false ) {
        if ( strpos( @$_SERVER['HTTP_ACCEPT'], '/' ) === false ) {
            return 'login_!http/accept';
        }
        // accept language header - a minimum is like "en"
        if (strlen(@$_SERVER['HTTP_ACCEPT_LANGUAGE']) < 2) {
            return 'login_!http/accept-language';
        }
        // http content type
        if (strpos(@$_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') === false) {
            return 'login_!content-type';
        }
        // http content length
        if (!is_numeric(@$_SERVER['CONTENT_LENGTH'])) {
            return 'login_!content-length';
        }


        // allow password protected posts without rules below
        // TODO allow 'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register', NOT 'login'
        $queries = bad_request_parse_query(@$_SERVER['QUERY_STRING']);
        if (isset($queries['action']) &&
            $queries['action'] === 'postpass' &&
            parse_url(@$_SERVER['HTTP_REFERER'], PHP_URL_HOST) === $_SERVER['HTTP_HOST']) {
            return false;
        }
        // protocol version -   old proxy servers use 1.0 !!!
        if (strpos(@$_SERVER['SERVER_PROTOCOL'], 'HTTP/1.1') === false) {
            return 'login_!http/1.1';
        }
        // http connection
        if (stripos(@$_SERVER['HTTP_CONNECTION'], 'keep-alive') === false) {
            return 'login_!keep-alive';
        }
        // accept encoding header
        if (strpos(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
            return 'login_!http/accept-encoding';
        }
        // the referer
        if (parse_url(@$_SERVER['HTTP_REFERER'], PHP_URL_HOST) !== $_SERVER['HTTP_HOST'] ||
            strpos(parse_url(@$_SERVER['HTTP_REFERER'], PHP_URL_PATH), '/wp-login.php') === false
           ) {
            return 'login_referer!=wp-login';
        }
        // cookies
        if (strpos(@$_SERVER['HTTP_COOKIE'], 'wordpress_test_cookie')  === false) {
            return 'login_!wp-test-cookie';
        }

        /* ---------------- USER AGENT testing ---------- */
        // allow IE8 logins
        //if (preg_match('/^Mozilla\/4\.0\ \(compatible; MSIE 8\.0;/', $_SERVER['HTTP_USER_AGENT'], $m) === 1) {
        //    return false;
        //}
        // botnet UA
        // FIXME join these two regex-s
        if (preg_match('/Firefox\/1|bot|spider|crawl|user-agent/i', @$_SERVER['HTTP_USER_AGENT'], $m) === 1) {
            return 'login_fake/old-firefox';
        }
        // only modern browsers - Mozilla/5.0
        if (preg_match('/^Mozilla\/5\.0/', @$_SERVER['HTTP_USER_AGENT'], $m) !== 1) {
            return 'login_!mozilla5.0';
        }
    }

    // OK
    return false;
}

$bad_request_result = bad_request();
if ( false !== $bad_request_result ) {
    for ( $i = 1; $i <= $bad_request_count; $i++ ) {
        error_log( 'File does not exist: ' . $bad_request_result );
    }
    die;
}


