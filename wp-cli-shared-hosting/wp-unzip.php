<pre><?php
/*
Snippet Name: Unzip WordPress core
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Unzip WordPress instead of uploading it file-by-file
Version: 0.1
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
*/


// name of the WordPress core zip
define( 'ZIP', 'wordpress.zip' );


// report every error
error_reporting( E_ALL );
ini_set( 'display_errors', '1' );

// check for the ZIP extension
if ( ! class_exists( 'ZipArchive' ) )
    exit( 'No ZipArchive class' );

$zip = new ZipArchive;

if ( ! $zip->open( ZIP ) )
    exit( 'ZIP open error (' . ZIP . ')' );

// extract the zip file in place
if ( ! $zip->extractTo( '.' ) )
    exit( 'Extraction failed, maybe write permission problem' );

$zip->close();

print 'Done. <strong>Please delete ' . ZIP;

