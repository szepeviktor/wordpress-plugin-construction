<?php
/*
Snippet Name: WordPress Live Debugger
Version: 0.3.0
Description: Enhance information available for debugging.
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
Source: https://gist.github.com/jrfnl/5925642
*/

/**
 * Require this file from wp-config.php after defining DB_NAME.
 *
 * Copy the folowing block into your wp-config.php.
 * == DO == test whether it's all working by using the code at the very end.
 */

/**
 * Enable debugging mode based on IP address and a cookie.
 *
 * 1. !wget -qO- http://www.szepe.net/ip/
 * 2. Set WP_DEBUG cookie with path of WordPress root (in Javascript: `document.cookie="WP_DEBUG=1;path=/"`)
 * 3. Copy this code block to your wp-config.php
 */
/*
$debugger_ip_addresses = array( '<ADD-YOUR-IP-ADDRESS-HERE>' );
if ( isset( $_SERVER['REMOTE_ADDR'] )
    && in_array( $_SERVER['REMOTE_ADDR'], $debugger_ip_addresses )
    && isset( $_COOKIE['WP_DEBUG'] )
    ) {
    define( 'DISABLE_WP_CRON', true );
    define( 'WP_DEBUG', true );
    //define( 'SCRIPT_DEBUG', true ); define( 'CONCATENATE_SCRIPTS', false );
    //define( 'SAVEQUERIES', true );
    include_once( dirname(__FILE__) . '/wp-config-live-debug.php' );
} else {
    define( 'WP_DEBUG', false );
    define( 'WP_CACHE', true );
}
*/

/**
 * Pay attention to require it AFTER defining DB_NAME.
 */
if ( ! defined( 'DB_NAME' ) ) {
    error_log( 'Malicious traffic detected: live_debugger_direct_access '
        . addslashes( @$_SERVER['REQUEST_URI'] )
    );
    ob_get_level() && ob_end_clean();
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.0 403 Forbidden' );
    exit();
}

/**
 * Debug database queries.
 */
function debugger_dump_db_qeries() {
    global $wpdb;
    echo '<hr style="display:block; clear:both; width:100%" />
        <link href="//jmblog.github.io/color-themes-for-google-code-prettify/css/themes/vibrant-ink.css"
        type="text/css" rel="stylesheet" />
        <script type="text/javascript"
        src="//google-code-prettify.googlecode.com/svn/loader/run_prettify.js"></script>
        <pre class="prettyprint lang-php" data-vibrant-ink-no-class="linenums" style="white-space:pre-wrap;
        color:grey; font-family:monospace,serif; font-size:1em;"><code>';
    var_dump( $wpdb->queries );
}
if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
    register_shutdown_function( 'debugger_dump_db_qeries' );
}

/**
 * Change the path to one on your webserver, the directory does not have to be in the web root
 * Don't forget to CHMOD this dir+file and add an .htaccess file denying access to all
 * For an example .htaccess file, see https://gist.github.com/jrfnl/5953256
 */
$error_log = @ini_get( 'error_log' );
if ( empty( $error_log ) || 'error_log' === $error_log ) {
    @ini_set( 'error_log', $_SERVER['DOCUMENT_ROOT'] . '/php-error.log' );
}

/**
 * Turn on error logging and show errors on-screen if in debugging mode
 */
error_reporting( E_ALL );
/**
 * Forgiving error reporting for old plugins and themes on modern PHP versions.
 */
//error_reporting( E_ALL ^ E_STRICT ^ E_DEPRECATED );
@ini_set( 'log_errors', true );
@ini_set( 'log_errors_max_len', '0' );

/**
 * Show errors on screen as HTML.
 */
@ini_set( 'display_errors', true );
@ini_set( 'html_errors', true );
@ini_set( 'docref_root', 'http://php.net/manual/' );
@ini_set( 'docref_ext', '.php' );
@ini_set( 'error_prepend_string', '<span style="color: #ff0000; background-color: transparent;">' );
@ini_set( 'error_append_string', '</span>' );

/**
 * Adds a backtrace to PHP errors
 *
 * Copied from: https://gist.github.com/625769
 * Forked from: http://stackoverflow.com/questions/1159216/how-can-i-get-php-to-produce-a-backtrace-upon-errors/1159235#1159235
 * Adjusted by jrfnl
 */
function process_error_backtrace( $errno, $errstr, $errfile, $errline ) {
    if ( ! ( error_reporting() & $errno ) ) {
        return;
    }
    switch ( $errno ) {
        case E_WARNING      :
        case E_USER_WARNING :
        case E_STRICT       :
        case E_NOTICE       :
        case ( defined( 'E_DEPRECATED' ) ? E_DEPRECATED : 8192 ) :
        case E_USER_NOTICE  :
            $type = 'warning';
            $fatal = false;
            break;
        default             :
            $type = 'fatal error';
            $fatal = true;
            break;
    }
    $trace = debug_backtrace();
    array_shift( $trace );
    if ( 'cli' === php_sapi_name() && ini_get( 'display_errors' ) ) {
        echo 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
        foreach( $trace as $item )
            echo '  ' . ( isset( $item['file'] ) ? $item['file'] : '<unknown file>' ) . ' '
            . ( isset( $item['line'] ) ? $item['line'] : '<unknown line>' )
            . ' calling ' . $item['function'] . '()' . "\n";

        flush();
    } else if ( ini_get( 'display_errors' ) ) {
        echo '<p class="error_backtrace">' . "\n";
        echo '  Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
        echo '  <ol>' . "\n";
        foreach($trace as $item)
            echo '    <li>' . ( isset( $item['file'] ) ? $item['file'] : '<unknown file>' ) . ' '
                . ( isset( $item['line'] ) ? $item['line'] : '<unknown line>' )
                . ' calling ' . $item['function'] . '()</li>' . "\n";
        echo '  </ol>' . "\n";
        echo '</p>' . "\n";

        flush();
    }

    if ( ini_get( 'log_errors' ) ) {
        $items = array();
        foreach ( $trace as $item )
            $items[] = (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' .
             ( isset( $item['line'] ) ? $item['line'] : '<unknown line>' )
                . ' calling ' . $item['function'] . '()';
        $message = 'Backtrace from ' . $type . ' \'' . $errstr
            . '\' at ' . $errfile . ' ' . $errline . ': ' . join( ' | ', $items );
        error_log( $message );
    }

    if ( $fatal ) {
        exit( 1 );
    }
}

set_error_handler( 'process_error_backtrace' );

/**
 * Now test whether it all works by uncommenting the below line
 *
 * If all is well:
 * - With WP_DEBUG set to true: You should see a red error notice on your screen
 * - Independently of the WP_DEBUG setting, the below 'error'-message should have been written to your log file.
 *   === DO === check whether it has been....
 */
//trigger_error( 'Testing 1..2..3.. Debugging code is working!', E_USER_NOTICE );
