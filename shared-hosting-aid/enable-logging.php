<?php
/*
Snippet Name: Enable PHP error logging
Version: 0.3.3
Description: Sets up PHP error logging in a protected directory.
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
*/

/**
 * Usage
 *
 * 1. Upload this file to the document root.
 * 2. Load it in your browser.
 *     Add `?above` to place error log above your public folder.
 *     Add `?above=<DIR-NAME>` to name the directory other than "log".
 * 3. Copy the code to your PHP application's config file.
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

$errorlog_file = 'error.log';
$htaccess_content = '
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
';

if ( isset( $_GET['above'] ) ) {
    // Place log directory above document root
    $dir_name = empty( $_GET['above'] ) ? 'log' : $_GET['above'];
    $errorlog_dir = dirname( __DIR__ ) . '/' . $dir_name;
} else {
    // Place log directory under document root
    // .htaccess will block access to it
    $secret_dir = md5( time() );
    $errorlog_dir = __DIR__ . '/' . $secret_dir;
}

$errorlog_path = $errorlog_dir . '/' . $errorlog_file;

// Check error_log
$old_errorlog = ini_set( 'error_log', $errorlog_path );
$new_errorlog = ini_get( 'error_log' );
if ( false !== $old_errorlog && $new_errorlog === $errorlog_path ) {
    if ( ! mkdir( $errorlog_dir, 0700 ) ) {
        die( "Couldn't create log dir!" );
    }

    if ( ! isset( $_GET['above'] ) {
        if ( ! file_put_contents( $errorlog_dir . '/.htaccess', $htaccess_content ) {
            die( "Couldn't create .htaccess!" );
        }
    }
}

if ( ! touch( $new_errorlog ) ) {
    die( "Couldn't touch error log!" );
}

printf( "
/*
 This script has deleted itself.

 Current error.log path: '%s'
 Copy this into your wp-config or settings file:
*/
<p id='iniset'>ini_set( 'error_log', '%s' );
ini_set( 'log_errors', 1 );</p>
", $new_errorlog, $new_errorlog );

// Delete self
unlink( __FILE__ );

?>
</pre>
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
</script></body>
</html>
