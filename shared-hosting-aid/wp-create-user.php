<?php
/*
Snippet Name: Create a user from hardcoded data and log in to WordPress.
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Unzip WordPress instead of uploading it file-by-file
Version: 0.1
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
*/

/**
 * 1. Fill in your user details below.
 * 2. Set a strong password! (user_pass)
 * 3. Upload this file to your WordPress directory.
 * 4. Load it in your browser:  <YOURSITE.NET/WP-DIR>/wp-create-user.php
 */

$userdata = (object)array(
    'user_login'        => "viktor",
    'nickname'          => "v",
    'role'              => "administrator",
    'user_email'        => "viktor@szepe.net",
    'user_url'          => "http://www.online1.hu/",
    'first_name'        => "Viktor",
    'last_name'         => "Szépe",
    'comment_shortcuts' => "",
    'use_ssl'           => 0,
    'user_pass'         => "12345"
);

$html = '
<!DOCTYPE html>
<html>
<title>Create new WordPress user and log in</title>
<body>
<pre style="background:white; color:blue;">%s</pre>
</body>
</html>
';

@ini_set( 'display_errors', 1 );
@error_reporting( E_ALL );

function o1_autouser( $userdata ) {

    $wpload_path = dirname( __FILE__ ) . '/wp-load.php';

    if ( ! file_exists( $wpload_path ) )
        return 'wp-load not found: ' . $wpload_path;

    define( 'WP_USE_THEMES', false );
    require_once( dirname( __FILE__ ) . '/wp-load.php' );

    $userid = wp_insert_user( $userdata );

    if ( is_wp_error( $userid ) ) {
        return 'Insert user failed: ' . $userid->get_error_message();
    } else {
        // delete self
        unlink( __FILE__ );
        // log in
        wp_set_auth_cookie( $userid );
        // redirect to Dashboard
        return 'New user ID = ' . $userid
        . '<script type="text/javascript">setTimeout(function () {window.location.href="'
        . admin_url() . '";}, 3000);</script>';
    }
}

printf( $html, o1_autouser( $userdata ) );
exit;
