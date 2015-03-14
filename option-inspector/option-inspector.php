<?php
/*
Plugin Name: Option Inspector
Version: 0.1
Description: Debug options, even serialized ones.
*/

if ( ! function_exists( 'add_filter' ) ) {
    ob_get_level() && ob_end_clean();
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

final class O1_Option_Inspector {

    private $plugin_url;

    public function __construct() {

        $this->plugin_url = plugin_dir_url( __FILE__ );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ) );
        add_action( 'wp_ajax_o1_inspect_option', array( $this, 'ajax_receiver' ) );
        add_action( 'admin_menu', array( $this, 'menu' ) );
    }

    public function admin_script( $hook ) {

        if ( 'options.php' !== $hook ) {
            return;
        }

        add_thickbox();
        $nonce = wp_create_nonce( 'option_inspector' );
        wp_enqueue_style( 'option_inspector_dbug_style', $this->plugin_url . 'css/dbug.css' );
        wp_enqueue_style( 'option_inspector_style', $this->plugin_url . 'css/option-inspector.css' );

        wp_enqueue_script( 'option_inspector_dbug', $this->plugin_url . 'js/dbug.js' );
        wp_enqueue_script( 'option_inspector', $this->plugin_url . 'js/option-inspector.js', array( 'thickbox', 'option_inspector_dbug' ) );
        wp_localize_script( 'option_inspector', 'OPTIONINS', array( 'nonce' => $nonce ) );
    }

    public function ajax_receiver() {
        check_ajax_referer( 'option_inspector', '_nonce' );

        $option_name = sanitize_key( $_REQUEST['option_name'] );
        $value = get_option( $option_name );
        require_once( plugin_dir_path( __FILE__ ) . 'inc/dBug.php' );
        new dBug\dBug( $value );
        wp_die();
    }

    public function menu() {

        global $submenu;

        // hack into the Settings menu
        $submenu['options-general.php'][14] = array( __( 'Options' ), 'manage_options', 'options.php' );
        ksort( $submenu['options-general.php'] );
    }

}

new O1_Option_Inspector();
