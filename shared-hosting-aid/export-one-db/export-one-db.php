<?php
/*
Snippet Name: Export a database to stdout.
Version: 0.4
Description: Sends the compressed (gzip) and encrypted (aes-128-cbc) database dump to stdout.
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
Usage: wget -q -S --content-disposition --user-agent="<UA>" --header="X-Secret-Key: <SECRET-KEY>" "https://<DOMAIN-AND-PATH>/export-one-db.php"
*/

// from wp-cli
function exp_o_replace_path_consts( $source, $path ) {
    $replacements = array(
        '__FILE__' => "'$path'",
        '__DIR__'  => "'" . dirname( $path ) . "'"
    );

    $old = array_keys( $replacements );
    $new = array_values( $replacements );

    return str_replace( $old, $new, $source );
}

// from wp-cli
function exp_o_get_wp_config_code() {
    $wp_config_path = dirname( dirname( __FILE__ ) ) . EXP_O_REL_WPCONFIG;

    $wp_config_code = explode( "\n", file_get_contents( $wp_config_path ) );

    $found_wp_settings = false;

    $lines_to_run = array();

    foreach ( $wp_config_code as $line ) {
        if ( preg_match( '/^\s*require.+wp-settings\.php/', $line ) ) {
            $found_wp_settings = true;
            continue;
        }

        $lines_to_run[] = $line;
    }

    if ( !$found_wp_settings ) {
        die( 'Strange wp-config.php file: wp-settings.php is not loaded directly.' );
    }

    $source = implode( "\n", $lines_to_run );
    $source = exp_o_replace_path_consts( $source, $wp_config_path );
    return preg_replace( '|^\s*\<\?php\s*|', '', $source );
}

error_reporting( E_ALL );

require_once( dirname( __FILE__ ) . '/exp-o-config.php' );

if ( empty( $_SERVER['HTTP_X_SECRET_KEY'] ) || EXP_O_SECRET !== $_SERVER['HTTP_X_SECRET_KEY'] ) {
    header( "HTTP/1.0 403 Forbidden" );
    exit( 1 );
}

if ( ! file_exists( EXP_O_PUBLIC_KEY_FILE ) ) {
    header( "HTTP/1.0 503 Service Unavailable" );
    exit( 2 );
}

eval( exp_o_get_wp_config_code() );

if ( !defined( 'DB_NAME' ) ) {
    header( "HTTP/1.0 406 Not Acceptable" );
    exit( 3 );
}

// Workaround to simulate pma's UI
$_REQUEST = array (
  'db_select' => array(
    0 => DB_NAME,
  ),
  'token' => '11111111111111111111111111111111',
  'export_type' => 'server',
  'export_method' => 'quick',
  'quick_or_custom' => 'custom',
  'output_format' => 'sendit',
  'filename_template' => '@SERVER@',
  'remember_template' => 'on',
  'charset_of_file' => 'utf-8',
  'compression' => 'none',
  'maxsize' => '',
  'what' => 'sql',
  'sql_include_comments' => 'something',
  'sql_header_comment' => '',
  'sql_use_transaction' => 'something',
  'sql_compatibility' => 'NONE',
  'sql_structure_or_data' => 'structure_and_data',
  'sql_create_database' => 'something',
  'sql_create_table' => 'something',
  'sql_create_view' => 'something',
  'sql_procedure_function' => 'something',
  'sql_create_trigger' => 'something',
  'sql_create_table_statements' => 'something',
  'sql_if_not_exists' => 'something',
  'sql_auto_increment' => 'something',
  'sql_backquotes' => 'something',
  'sql_truncate' => 'something',
  'sql_type' => 'INSERT',
  'sql_insert_syntax' => 'both',
  'sql_max_query_size' => '50000',
  'sql_hex_for_binary' => 'something',
  'sql_utc_time' => 'something'
);

$_POST = $_REQUEST;

$_COOKIE = array (
  'pma_lang' => 'en',
  'pma_collation_connection' => 'utf8_unicode_ci'
);

// Trick pma's session handler
$session_name = 'phpMyAdmin';
@session_name($session_name);
session_start();
$_SESSION[' PMA_token '] = $_REQUEST['token'];
session_write_close();

// Capture pma's output
ob_start();
require_once( dirname( __FILE__ ) . '/export.php' );
$dump = ob_get_clean();

// Generate password from random data
$bytes = bin2hex( openssl_random_pseudo_bytes( 10 ) );
$numbers = round( microtime( true ) / mt_rand() * 1E10 );
$password = sha1( $bytes . $numbers );

// Encrypt the password
$public_key = file_get_contents( EXP_O_PUBLIC_KEY_FILE );
openssl_public_encrypt( $password, $encrypted, $public_key );
header( 'X-Password: ' . base64_encode( $encrypted ) );

// Compress and encrypt the dump
$cdump = gzencode( $dump, 9 );
unset( $dump );
header( 'X-Memory-Peak: ' . memory_get_peak_usage( true ) );
//                                                       true = OPENSSL_RAW_DATA in PHP 5.4
print openssl_encrypt( $cdump, 'aes-128-cbc', $password, true, EXP_O_IV );
unset( $password );
