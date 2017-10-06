<?php
/*
Plugin Name: Pods export register code (MU)
Version: 0.1.0
Description: Export register_post_type() and register_taxonomy() calls to theme directory.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

/**
 * Change in pods_debug() "var_dump( $debug );" to "var_export( $debug );"
 * wp-content/plugins/pods/includes/general.php:258
 */

if ( ! empty( $_GET['pods_debug_register'] ) && '1' === $_GET['pods_debug_register'] ) {
    ob_start();
    add_action( 'admin_init', function () {
        $output = htmlspecialchars_decode( ob_get_contents() );
        ob_end_clean();
        $file = pod_register_export( $output );
        printf( '<pre>See %s', $file );
    } );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pod_register_export_plugin_link' );

function pod_register_export_plugin_link() {

    $url = self_admin_url( 'admin.php?pods_debug_register=1' );
    $actions['pods-debug-register'] = sprintf( '<a href="%s" target="_blank">Dump Pods</a>', $url );

    return $actions;
}

function pods_php_code_check( $code ) {

    return @eval( 'return true;' . $code );
}

function pod_register_export( $output ) {

    $php_export = '';

    $arrays = array_filter( explode( '<e>', $output ) );

    foreach ( $arrays as $array_text ) {
        $code = sprintf( '$array = %s;', $array_text );
        if ( true !== pods_php_code_check( $code ) ) {

            return false;
        }
        $evaluate = eval( $code );

        switch ( count( $array ) ) {
            case 2:
                // https://codex.wordpress.org/Function_Reference/register_post_type
                $php_export .= sprintf( "register_post_type( %s, %s );\n\n",
                    var_export( $array[0], true ),
                    var_export( $array[1], true )
                );
                break;
            case 3:
                // https://codex.wordpress.org/Function_Reference/register_taxonomy
                $php_export .= sprintf( "register_taxonomy( %s, %s, %s );\n\n",
                    var_export( $array[0], true ),
                    var_export( $array[1], true ),
                    var_export( $array[2], true )
                );
                break;
            default:
                return false;
        }
    }

    $file = sprintf( '%s/pods-export-%s.php', get_stylesheet_directory(), date( 'U' ) );
    file_put_contents( $file, sprintf( "<?php\n\n%s", $php_export ) );

    return $file;
}
