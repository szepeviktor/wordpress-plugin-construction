<?php
/*
Snippet Name: Enable PHP error logging.
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Sets up PHP error logging in a protected directory.
Version: 0.3
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
*/

/**
 * USAGE
 *
 * 1. Upload this file to the document root.
 * 3. Load it in your browser.
 *     Add `?above` to place error log above your public folder.
 *     Add `?above=<DIR-NAME>` to name the directory other than "log".
 * 4. Copy the code to your application's config file.
 */

?>
<!DOCTYPE html>
<html>
<title>Enable PHP error logging</title>
<style rel="stylesheet">
    /* http://flatuicolors.com/ */
    pre {
        color: #34495e;
        background: white;
    }
    #iniset {
        color: black;
    }
    ::selection {
        background: #2ecc71;
    }
    ::-moz-selection {
        background: #2ecc71;
    }
</style>
<body>
<pre><?php

ini_set( 'display_errors', '1' );
ini_set( 'display_startup_errors', '1' );

$this_dir = dirname( __FILE__ );
$errorlog_file = "error.log";
$htaccess_content = "
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

if ( isset( $_GET['above'] ) ) {
    // Place log directory above document root.
    $dir_name = empty( $_GET['above'] ) ? "log" : $_GET['above'];
    $errorlog_dir = dirname( $this_dir ) . "/" . $dir_name;
} else {
    // Place log directory under document root.
    // .htaccess will block access to it.
    $secret_dir = md5( time() );
    $errorlog_dir = $this_dir . "/" . $secret_dir;
}

$errorlog_path = $errorlog_dir . "/" . $errorlog_file;

// Check error_log
$old_errorlog = ini_set( 'error_log', $errorlog_path );
$new_errorlog = ini_get( 'error_log' );
if ( false !== $old_errorlog && $new_errorlog === $errorlog_path ) {
    if ( ! mkdir( $errorlog_dir, 0700 ) )
        die( "Couldn't create log dir!" );

    if ( isset( $_GET['above'] )
        && ! file_put_contents( $errorlog_dir . "/.htaccess", $htaccess_content ) )
        die( "Couldn't create .htaccess!" );
}

if ( ! touch( $new_errorlog ) )
    die( "Couldn't touch error log!" );

print "
/*
 This script has deleted itself.

 Current error.log path: '{$new_errorlog}'
 Copy this into your wp-config or settings file:
*/
<p id='iniset'>ini_set( 'error_log', '{$new_errorlog}' );
ini_set( 'log_errors', 1 );</p>
";

// delete self
unlink( __FILE__ );

?>
</pre></body>
<script>
(function () {
    var range, selection,
        doc = document,
        text = doc.getElementById('iniset');

    // MSIE
    if (doc.body.createTextRange) {
        range = doc.body.createTextRange();
        range.moveToElementText(text);
        range.select();
    } else if (window.getSelection) {
        selection = window.getSelection();
        range = doc.createRange();
        range.selectNodeContents(text);
        selection.removeAllRanges();
        selection.addRange(range);
    }
}())
</script>
</html>