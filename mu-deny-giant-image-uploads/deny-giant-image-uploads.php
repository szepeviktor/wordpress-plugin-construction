<?php
/*
Plugin Name: Deny Giant Image Uploads MU
Version: 1.2
Description: Prevents Uploads of images greater than 3.2MP
Author: TJNowell
Author URI: http://tomjn.com/
Plugin URI: http://tomjn.com/164/clients-who-upload-huge-camera-photos-decompression-bombs/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-deny-giant-image-uploads
*/

function tomjn_deny_giant_images( $file ) {
    $type = explode( '/', $file['type'] );

    if ( 'image' === $type[0] ) {
        list( $width, $height, $imagetype, $hwstring, $mime, $rgb_r_cmyk, $bit ) = getimagesize( $file['tmp_name'] );

        // I added 100 000 as sometimes there are more rows/columns than visible pixels depending on the format
        if ( $width * $height > 3200728 )
            $file['error'] = 'This image is too large, resize it prior to uploading, ideally below 3.2MP or 2048x1536';
    }

    return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'tomjn_deny_giant_images' );

/*
TODO
- set $limit_max _min
- avoid list()
- ban camera file names (pattern)
- ".php" in name
- too small size
- i18n
*/
