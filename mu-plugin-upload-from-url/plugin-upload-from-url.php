<?php
/*
Plugin Name: Plugin Upload from URL (MU)
Version: 0.1.0
Description: Enables plugin installation from a URL.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-plugin-upload-from-url
Author: Viktor Szépe
License: GNU General Public License (GPL) version 2
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

if ( ! function_exists( 'add_filter' ) ) {
    error_log( 'Break-in attempt detected: plugin_upload_from_url_mu_direct_access '
        . addslashes( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' )
    );
    ob_get_level() && ob_end_clean();
    if ( ! headers_sent() ) {
        header( 'Status: 403 Forbidden' );
        header( 'HTTP/1.1 403 Forbidden', true, 403 );
        header( 'Connection: Close' );
    }
    exit;
}

class O1_Plugin_Upload_From_Url {

    private $admin_menu;

    public function __construct() {

        add_action( 'admin_menu', array( $this, 'download_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'upload_script_styles' ) );
        add_action( 'update-custom_' . 'download-plugin', array( $this, 'download_plugin' ) );
    }


    /**
     * Install plugin from a URL.
     *
     * A mix of install-plugin and upload-plugin actions from wp-admin/update.php:93.
     */
    public function download_plugin() {

        if ( ! current_user_can( 'upload_plugins' ) ) {
            wp_die( __( 'You do not have sufficient permissions to install plugins on this site.' ) );
        }

        check_admin_referer( 'plugin-download' );

        require_once ABSPATH . 'wp-admin/admin-header.php';

        $download_url = esc_url_raw( $_REQUEST['pluginurl'] );

        // Remove "-master" from GitHub URL-s
        if ( false !== strstr( $download_url, '//github.com/' ) ) {
            add_filter( 'upgrader_source_selection', array( $this, 'remove_github_master' ), 9, 1 );
        }

        $type  = 'web';
        $title = sprintf( __( 'Installing Plugin from URL: %s' ), esc_html( $download_url ) );
        $url   = 'update.php?action=install-plugin';
        $nonce = 'plugin-download';

        $upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'type', 'title', 'url', 'nonce' ) ) );
        $upgrader->install( $download_url );

        include ABSPATH . 'wp-admin/admin-footer.php';
    }


    public function upload_script_styles( $hook ) {

        if (  $this->admin_menu !== $hook ) {

            return;
        }

        $style = '.plugins_page_plugin-download .download-plugin-form {
            background: none repeat scroll 0 0 #fafafa; border: 1px solid #e5e5e5;
            margin: 30px auto; max-width: 380px; padding: 30px; text-align: right; }
            #pluginurl { width: 100%; margin-bottom: 10px; }
        ';

        wp_add_inline_style( 'wp-admin', $style );
    }

    public function download_form() {

?>
<div class="wrap">
<h2>Upload Plugin from URL
<?php

    $href = self_admin_url( 'plugin-install.php' );
    $text = _x( 'Browse', 'plugins' );
    echo ' <a href="' . $href . '" class="upload add-new-h2">' . $text . '</a>';

?>
</h2>
<div class="download-plugin upload-plugin">
    <p class="install-help"><?php _e( 'If you have a plugin in a .zip hosted somewhere, you may install it by entering the URL here.' ); ?></p>
    <form method="post" class="download-plugin-form" action="<?php echo self_admin_url( 'update.php?action=download-plugin' ); ?>">
        <?php wp_nonce_field( 'plugin-download' ); ?>
        <label class="screen-reader-text" for="pluginurl"><?php _e( 'Plugin URL' ); ?></label>
        <input type="url" id="pluginurl" name="pluginurl" autofocus required />
        <?php submit_button( __( 'Install Now' ), 'button', 'install-download-plugin-submit', false ); ?>
    </form>
</div>
</div>
<?php

    }

    public function download_menu() {

        $this->admin_menu = add_plugins_page( __( 'Add Plugins' ), 'Upload from URL', 'install_plugins', 'plugin-download', array( $this, 'download_form' ) );
    }

    public function remove_github_master( $source ) {

        global $wp_filesystem;

        $gh = '-master';

        $new_source = $this->remove_trailing_part( $source, $gh );

        if ( $wp_filesystem->move( $source, $new_source ) ) {

            return $new_source;
        }

        return $source;
    }

    private function remove_trailing_part( $haystack, $needle ) {

        $length = strlen( $needle );
        if ( 0 === $length ) {

            return  $haystack;
        }

        if ( substr( $haystack, -$length ) !== $needle ) {
            // Try with a slash
            if ( substr( $haystack, -$length - 1 ) === $needle . '/' ) {

                return substr( $haystack, 0, -$length - 1 ) . '/';
            }

            return  $haystack;
        }

        return substr( $haystack, 0, -$length );
    }
}

new O1_Plugin_Upload_From_Url();
