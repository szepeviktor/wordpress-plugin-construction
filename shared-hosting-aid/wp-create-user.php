<?php
/*
Snippet Name: Create a user from hardcoded data and log in to WordPress.
Version: 0.2.3
Description: Unzip WordPress instead of uploading it file-by-file
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
*/

/**
 * Usage
 *
 * 1. Fill in your user details below
 * 2. Set a strong password! (user_pass)
 * 3. Upload this file to your WordPress directory
 * 4. Load it in your browser: https://www.example.com/wordpress/wp-create-user.php
 */

$wcu_userdata = (object) array(
    'user_login'        => 'viktor',
    'nickname'          => 'v',
    'role'              => 'administrator',
    'user_email'        => 'viktor@szepe.net',
    'user_url'          => 'https://github.com/szepeviktor',
    'first_name'        => 'Viktor',
    'last_name'         => 'Szépe',
    'comment_shortcuts' => '',
    'use_ssl'           => 0,
    'user_pass'         => '12345',
);

@ini_set( 'display_errors', 1 );
@error_reporting( E_ALL );

$wcu_html = '<!DOCTYPE html>
<html>
<meta charset="UTF-8">
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

$wcu_wpload_path = dirname( __FILE__ ) . '/wp-load.php';

if ( ! file_exists( $wcu_wpload_path ) ) {
    exit( 'wp-load not found: ' . $wcu_wpload_path );
}

define( 'WP_USE_THEMES', false );
require_once $wcu_wpload_path;

$wcu_userid = wp_insert_user( $wcu_userdata );

if ( is_wp_error( $wcu_userid ) ) {
    exit( sprintf( $wcu_html,  'Insert user failed: ' . $wcu_userid->get_error_message() ) );
}

// Delete self
unlink( __FILE__ );
// Log in
wp_set_auth_cookie( $wcu_userid );
// Redirect to Dashboard
exit( sprintf( sprintf( $wcu_html, $wcu_html_ok ),
    $wcu_userid,
    admin_url( 'profile.php#pass1' )
) );
