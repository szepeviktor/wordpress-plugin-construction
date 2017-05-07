<?php

/**
 * A wannabe WordPress plugin (MU)
 *
 * Average color stored in post meta.
 */


$file = __DIR__ . '/sample-image.jpg';
printf( '<img src="%s" style="background-color:#%s; max-width:100%%;" />', $file, average_color( $file ) );

// Add theme usage example

/**
 * Get average color from an image using GD
 *
 * @param string $file Full path to the JPEG image file.
 *
 * @return string Hexadecimal color code.
 */
function average_color( $file ) {

    // Load image
    $img_resource = imagecreatefromjpeg( $file );
    // Get dimensions
    list($width, $height) = getimagesize( $file );
    // New empty pixel
    $average_image_resource = imagecreatetruecolor( 1, 1 );
    // Resample to 1x1
    imagecopyresampled( $average_image_resource, $img_resource, 0, 0, 0, 0, 1, 1, $width, $height );
    // Get pixel color
    $average_color = imagecolorat( $average_image_resource, 0, 0 );

    return dechex( $average_color );
}
