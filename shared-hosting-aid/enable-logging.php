<?php
/*
Snippet Name: Enable PHP error logging.
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: 
Version: 0.2
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
*/

/**
 * 1. Upload this file to the document root.
 * 2. Uncomment "above document root" to place error log above your public folder.
 * 3. Load it in your browser.
 * 4. Copy the code to your application's config file.
 */

?>
<!DOCTYPE html>
<html>
<title>Enable PHP error logging</title>
<body>
<pre style="background:white; color:blue;"><?php

ini_set( 'display_errors', '1' );

$this_dir = dirname( __FILE__ );
$errorlog_file = "error.log";
$htaccess = "
# Apache < 2.3
<IfModule !mod_authz_core.c>
  Order allow,deny
  Deny from all
  Satisfy All
</IfModule>
# Apache ≥ 2.3
<IfModule mod_authz_core.c>
  Require all denied
</IfModule>
";

// under document root, htaccess will block access to it
$secret_dir = md5( time() );
$errorlog_dir = $this_dir . "/" . $secret_dir;

// above document root
//$errorlog_dir = dirname( $this_dir ) . "/log";

$errorlog_path = $errorlog_dir . "/" . $errorlog_file;

if (! mkdir( $errorlog_dir, 0700 ) )
    die( "Couldn't create log dir!" );

if ( ! touch( $errorlog_path ) )
    die( "Couldn't touch error.log!" );

if ( ! file_put_contents( $errorlog_dir . "/.htaccess", $htaccess ) )
    die( "Couldn't create .htaccess!" );

// OK
$current_errorlog = ini_get( 'error_log' );
print "
/*
 Current error.log path = ({$current_errorlog})
 Copy this into your wp-config or settings file:
*/
ini_set( 'error_log', '{$errorlog_path}' );
ini_set( 'log_errors', 1 );
";

// delete self
unlink( __FILE__ );

?></body>
</html>