<pre><?php


define( 'ZIP', 'wordpress.zip' );


error_reporting( E_ALL );
ini_set( 'display_errors', '1' );

if ( ! class_exists( 'ZipArchive' ) )
    die( 'No ZipArchive class' );

$zip = new ZipArchive;

$opened = $zip->open( ZIP );
if ( $opened !== true )
    die( 'Open error' );

$zip->extractTo( '.' );
$zip->close();

echo 'OK. Delete ' . ZIP;

