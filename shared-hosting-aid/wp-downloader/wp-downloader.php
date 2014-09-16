<?php
/**
 * Plugin Name: WP Downloader
 * Plugin URI: http://szalkiewicz.pl/
 * Description: This plugin allows you to download other plugins and themes installed on your site as a zip package, ready to install on another site.
 * Version: 1.1
 * Author: Wojtek Szałkiewicz
 * Author URI: http://szalkiewicz.pl
 * Requires at least: 3.5
 * Tested up to: 3.7.1
 */

add_action('plugins_loaded', 'wpd_load');

function wpd_load() {
    add_filter('plugin_action_links', 'wpd_plugin_action_links', 10, 2);
    add_filter('theme_action_links', 'wpd_theme_action_links', 10, 2);
    add_action('admin_footer', 'wpd_scripts');

    // zip & download
    if ( isset( $_GET['wpd'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'wpd-download' ) ) {
        wpd_download();
    }
}

function wpd_plugin_action_links( $links, $file ) {

    $settings_link = '<a href="'
                     . wp_nonce_url( admin_url( '?wpd=plugin&object='.$file), 'wpd-download') . '">'
                     . __('Download') . '</a>';
    array_push($links, $settings_link);

    return $links;
}

function wpd_theme_action_links($links, $theme){

    $settings_link = '<a href="'
                     . wp_nonce_url( admin_url( '?wpd=theme&object='.$theme->get_stylesheet()), 'wpd-download') . '">'
                     . __('Download') . '</a>';
    array_push($links, $settings_link);

    return $links;
}

function wpd_scripts() {

    $screen = get_current_screen()->id;

    if ( $screen == 'themes') {
        $url = wp_nonce_url( admin_url( '?wpd=theme&object=' . get_stylesheet() ), 'wpd-download' );
        ?>
<script>
(function($) {
    $(function() {
        $('#current-theme .theme-options')
            .after('<div class="theme-options"><a href="<?php echo $url; ?>"><?php _e('Download'); ?></a></div>')
    });
}(jQuery))
</script>
        <?php
    }
}

function wpd_download(){

    if(!class_exists('PclZip')){
        include ABSPATH . 'wp-admin/includes/class-pclzip.php';
    }

    $what = $_GET['wpd'];
    $object = $_GET['object'];

    switch ($what) {
        case 'plugin':
            if ( strpos($object, '/' ) ) {
                $object = dirname( $object );
            }
            $root = WP_PLUGIN_DIR;
            break;

        case 'theme':
            $root = get_theme_root($object);
            break;
    }

    $path = $root . '/' . $object;
    $fileName = $object . '.zip';

    $archive = new PclZip($fileName);
    $archive->add($path, PCLZIP_OPT_REMOVE_PATH, $root);

    header('Content-type: application/zip');
    header('Content-Disposition: attachment; filename="'.$fileName.'"');
    readfile($fileName);

    // remove tmp zip file
    unlink($fileName);

    exit;
}

