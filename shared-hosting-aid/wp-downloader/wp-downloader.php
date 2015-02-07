<?php
/*
Plugin Name: Theme and Plugin Downloader
Version: 2.1
Plugin URI: http://wordpress.org/plugins/wp-downloader/
Description: Download themes and plugins installed on your site as a ZIP archive, ready to install on another site.
Author: Viktor Szépe, Wojtek Szałkiewicz
Author URI: http://www.online1.hu/webdesign/
License: GNU General Public License (GPL) version 2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/shared-hosting-aid/wp-downloader
*/

class WP_Downloader {

    private $script_template = '<script type="text/javascript" id="wp-downloader">
            jQuery(function ($) {
                $("#wpbody .theme .theme-actions .load-customize").each(function (i, e) {
                    var url = $(e).prop("href");

                    $(e).removeClass("load-customize");
                    $(e).text("%s");
                    $(e).prop("href", "%s" + url.replace(/.*theme=(.*)(&|$)/, "$1") );
                });
            });
</script>
    ';
    private $verb = 'Download';

    public function __construct() {

        add_action('plugins_loaded', array( $this, 'load' ) );
        add_filter('plugin_action_links', array( $this, 'plugin_action_links' ), 10, 4 );
        add_action('admin_footer-themes.php', array( $this, 'theme_script' ), 99 );
    }

    public function load() {

        if ( isset( $_GET['wpd'] )
            && wp_verify_nonce( $_GET['_wpnonce'], 'wpd-download' )
        ) {
            $this->download();
        }
    }

    public function plugin_action_links( $links, $file, $plugin_data, $context ) {

        if ( 'dropins' === $context )
            return $links;

        if ( 'mustuse' === $context ) {
            $what = 'mustuse';
        } else {
            $what = 'plugin';
        }

        $dowload_query = build_query( array( 'wpd' => $what, 'object' => $file ) );
        $download_link = sprintf( '<a href="%s">%s</a>',
            wp_nonce_url( admin_url( '?' . $dowload_query ), 'wpd-download' ),
            $this->verb
        );
        array_push( $links, $download_link );

        return $links;
    }

    public function theme_script() {

        // scripts don't need HTML encoding
        $url = admin_url( '?wpd=theme&_wpnonce='. wp_create_nonce( 'wpd-download' ) . '&object=' );
        printf( $this->script_template, $this->verb, $url );
    }

    private function download() {

        if ( ! class_exists( 'PclZip' ) ) {
            require ABSPATH . 'wp-admin/includes/class-pclzip.php';
        }

        $what = $_GET['wpd'];
        $object = $_GET['object'];

        switch ( $what ) {
            case 'plugin':
                // plugin in a subdir
                if ( strpos( $object, '/' ) )
                    $object = dirname( $object );
                $root = WP_PLUGIN_DIR;
                break;
            case 'mustuse':
                $root = WPMU_PLUGIN_DIR;
                break;
            case 'theme':
                $root = get_theme_root( $object );
                break;
            default:
                // bad URL
                wp_die( 'Cheatin&#8217; uh?' );
        }

        $object = sanitize_file_name( $object );
        if ( empty( $object ) )
                wp_die( 'Cheatin&#8217; uh?' );

        $path = $root . '/' . $object;

        // create ZIP file in the uploads directory
        $upload_dir = wp_upload_dir();
        $zip = trailingslashit( $upload_dir['path'] ) . $object . '.zip';

        $archive = new PclZip( $zip );
        $archive->add( $path, PCLZIP_OPT_REMOVE_PATH, $root );

        header( 'Content-type: application/zip' );
        header( 'Content-Disposition: attachment; filename="' . $object . '.zip"' );

        readfile( $zip );

        // remove temporary ZIP file
        unlink( $zip );

        // no wp_die(), it produces HTML
        exit;
    }
}

new WP_Downloader();
