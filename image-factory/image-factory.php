<?php
/*
Plugin Name: Image Factory for servers

Advanced image optimization with mozjpeg on your own server.

Compile video on Vultr VPS from spinup till wp runs -wp.install

apt-get install jpeginfo jpeg-archive optipng socat
(jpeg-archive depend on mozjpeg)

init script with: script location, user, socket file path
/etc/default/socat.conf
socat logging to syslog
https://github.com/asaif/socat-init

/usr/bin/socat UNIX-LISTEN:./factory.sock,mode=600,fork EXEC:./wp-media-optimize.sh,pipes,su=phpuser

script: [ -r file ]

wp-cli option delete: add_post_meta( $attachment_id, '_optimize', $metasize['file'] );

*/

class O1_Image_Factory {

        /**
         * Dimensions of optimized images
         */
        private $sizes = array();

        public function __construct() {

            // TODO Options / Media input: '/var/www/subtwo/factory.sock' or define

            // Work from the highest quality
            add_filter( 'jpeg_quality', array( $this, 'jpeg_quality'), 4294967295 );

            // In wp_generate_attachment_metadata()
            add_filter( 'image_make_intermediate_size', array( $this, 'image_factory' ) );
            add_filter( 'wp_generate_attachment_metadata', array( $this, 'metadata' ), 10, 2 );
        }

        public function jpeg_quality( $quality ) {

            return 100;
        }

        public function image_factory( $filename ) {

            $socket_file = get_option( 'image_factory_socket' );

            if ( ! file_exists( $socket_file ) ) {
                    error_log( '[image-factory] Socket does not exist:'
                        . $socket_file );
                    return $filename;
            }
            $factory = stream_socket_client( 'unix://' . $socket_file, $errno, $errstr );

            if ( 0 === $errno ) {
                // Maximum processing time per size
                stream_set_timeout( $factory, 3 );
                $write = fwrite( $factory, $filename . "\n" );
                if ( false === $write ) {
                    error_log( '[image-factory] Socket write error:'
                        . $filename );
                } else {
                    $result = fgets( $factory, 100 );
                    $result_string = trim( $result );
                    if ( 'OK' === $result_string ) {
                        if ( 1 === preg_match( '/-([0-9]+)x([0-9]+)\.[a-zA-Z]+$/',
                            $filename, $width_height )
                        ) {
                            // This image has been optimized.
                            $this->sizes[] = array( $width_height[1], $width_height[2] );
                        } else {
                            error_log( '[image-factory] Image invalid file name:'
                                . serialize( $filename ) );
                        }
                    } else {
                        error_log( '[image-factory] Image processing error/timeout:'
                            . serialize( $result_string ) );
                    }
                }
                fclose( $factory );
            } else {
                error_log( '[image-factory] Socket open error:' . $errstr );
            }

            return $filename;
        }

        public function metadata( $metadata, $attachment_id ) {

            foreach ( $metadata['sizes'] as $metasize ) {

                $processed = false;
                foreach ( $this->sizes as $processed_size ) {
                    // Only two equals sings ( integer == string )
                    if ( $metasize['width'] == $processed_size[0]
                        && $metasize['height'] == $processed_size[1]
                    ) {
                        // This size is done.
                        $processed = true;
                        break;
                    }
                }

                if ( ! $processed ) {
                    // Record data for image optimization cron job
                    add_post_meta( $attachment_id, '_optimize', $metasize['file'] );
                    error_log( sprintf( '[image-factory] Image missed, ID:%s name:%s',
                        $attachment_id,
                        $metasize['file']
                    ) );
                }
            }

            return $metadata;
        }
}

new O1_Image_Factory();
