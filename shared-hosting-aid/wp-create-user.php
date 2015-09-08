<?php
/*
Snippet Name: Create a user from hardcoded data and log in to WordPress.
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Unzip WordPress instead of uploading it file-by-file
Version: 0.2.1
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
*/

/**
 * Usage
 *
 * 1. Fill in your user details below.
 * 2. Set a strong password! (user_pass)
 * 3. Upload this file to your WordPress directory.
 * 4. Load it in your browser:  <YOURSITE.NET/WP-DIR>/wp-create-user.php
 */

$wcu_userdata = (object)array(
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

$wcu_html = '
<!DOCTYPE html>
<html>
<title>Create new WordPress user and log in</title>
<body>
<pre style="background:white; color:blue;">%s</pre>
</body>
</html>
';
$wcu_html_ok = '
New user ID = %s
<script type="text/javascript">setTimeout(function () { window.location.href="%s"; }, 3000);</script>
';

@ini_set( 'display_errors', 1 );
@error_reporting( E_ALL );

$wcu_wpload_path = dirname( __FILE__ ) . '/wp-load.php';

if ( ! file_exists( $wcu_wpload_path ) )
    return 'wp-load not found: ' . $wcu_wpload_path;

define( 'WP_USE_THEMES', false );
require_once( $wcu_wpload_path );

$wcu_userid = wp_insert_user( $wcu_userdata );

if ( is_wp_error( $wcu_userid ) ) {
    printf( $wcu_html,  'Insert user failed: ' . $wcu_userid->get_error_message() );
} else {
    // Delete self
    unlink( __FILE__ );
    // Log in
    wp_set_auth_cookie( $wcu_userid );
    // Redirect to Dashboard
    printf( sprintf( $wcu_html, $wcu_html_ok ),
        $wcu_userid,
        admin_url( 'profile.php#pass1' )
    );
}

exit;
