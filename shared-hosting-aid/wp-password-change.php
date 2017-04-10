<pre><?php
/*
Snippet name: Reset your own WordPress password
Version: 1.0.1
Description: Copy this file to the WordPress root and load it in your browser
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
Author: Viktor Szépe
*/

/**
 * 1. Load it in your browser to see user names
 * 2. Change login name below to the selected user ($user)
 *        + set password ($plain_pass)
 *        + comment out `die;`
 * 3. Reload it to set password
 */


/***------EDIT HERE----------***/

$user = 'viktor';
$plain_pass = '12345';

/***------EDIT HERE----------***/


ini_set( 'display_errors', 1 );
error_reporting( E_ALL );
// if it's annoying uncomment the next line
//error_reporting( E_ALL ^ E_STRICT );

define( 'WP_USE_THEMES', false );
require_once( dirname( __FILE__ ) . '/wp-load.php' );

var_dump( $wpdb->get_col( "SELECT user_login FROM $wpdb->users", 0 ) );
var_dump( $wpdb->get_col( "SELECT user_pass FROM $wpdb->users", 0 ) );


/***------EDIT HERE----------***/

// Comment out in the second run
die;

/***------EDIT HERE----------***/


require_once( dirname( __FILE__ ) . '/wp-includes/class-phpass.php' );

$wp_hasher = new PasswordHash( 8, true );
$pass = $wp_hasher->HashPassword( $plain_pass );

var_dump( $wpdb->update( $wpdb->users, array( 'user_pass' => $pass ), array( 'user_login' => $user ) ) );
