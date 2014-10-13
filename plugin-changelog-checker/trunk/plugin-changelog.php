<?php
/*
Plugin Name: Plugin changelog checker
Version: 0.2.0
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Notifies you - the plugin developer - about WP.org not displaying changelog correctly.
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/plugin-changelog-checker/trunk
*/

if ( ! function_exists( 'add_filter' ) ) {
    error_log( 'Malicious sign detected: wpf2b_direct_access '
        . addslashes( $_SERVER['REQUEST_URI'] )
    );
    ob_get_level() && ob_end_clean();
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.0 403 Forbidden' );
    exit();
}

class O1_Plugin_Changelog_Checker {

    private $validator_url = "https://wordpress.org/plugins/about/validator/";
    private $compare_lines = 20;
    private $alert_address;

    private function get_top_lines( $html, $compare_lines ) {

        $lines = array();
        $i = 0;
        if ( ! $compare_lines )
            $compare_lines = $this->compare_lines;

        foreach( preg_split( '/((\r?\n)|(\r\n?))/', $html ) as $line ) {
            $lines[] = trim( strip_tags( $line ) );
            if ( $i++ > $compare_lines )
                break;
        }

        return $lines;
    }

    public function __construct() {

        add_filter( 'plugin_action_links', array( $this, 'plugin_link' ), 10, 4 );
        add_action( 'admin_enqueue_scripts', array( $this, 'plugin_script' ) );
        add_action( 'o1_daily_changelog_check', array( $this, 'check_changelogs' ) );
        add_action( 'wp_ajax_o1_plugin_changelog_watch', array( $this, 'watch_ajax' ) );

        register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
    }

    public function check_changelogs() {

        $this->alert_address = get_bloginfo( 'admin_email' );
        $watching = get_option( 'o1_plugin_changelog' );
        if ( ! $watching )
            $watching = array();

        foreach ( $watching as $plugin ) {
            if ( strpos( $plugin, '/' ) )
                $plugin = dirname( $plugin );
            $this->check_changelog( $plugin );
        }
    }

    private function check_changelog( $plugin ) {

        $changelog_page_url = sprintf(
            'https://wordpress.org/plugins/%s/changelog/',
            $plugin
        );
        $svn_url = sprintf(
            'plugins.svn.wordpress.org/%s/trunk/readme.txt',
            $plugin
        );
        $http_args = array( 'sslverify' => false );

        // the Changelog page
        $changelog_page = wp_remote_get( $changelog_page_url, $http_args );
        if ( is_wp_error( $changelog_page ) )
            return;
        $changelog_page_html = wp_remote_retrieve_body( $changelog_page );
        if ( empty( $changelog_page_html ) )
            return;
        $match = preg_match( '/<div class="block-content">(.+)<\/div><!-- block-content-->/sU',
            $changelog_page_html, $changelog_page_top );
        if (  1 !== $match )
            return;
        $changelog_page_20 = $this->get_top_lines( $changelog_page_top[1] );

        // readme.txt from trunk parsed with Readme Validator
        $post_args = array( 'body' => array( 'url' => '1', 'readme_url' => $svn_url ) );
        $svn = wp_remote_post( $this->validator_url, array_merge( $http_args, $post_args ) );
        if ( is_wp_error( $svn ) )
            return;
        $svn_html = wp_remote_retrieve_body( $svn );
        if ( empty( $svn_html ) )
            return;
        $match = preg_match( '/<h3>Changelog<\/h3>\n(.+)<hr \/>/sU',
            $svn_html, $svn_top );
        if ( 1 !== $match )
            return;
        // changelog can be shorter than $compare_lines
        $svn_20 = $this->get_top_lines( $svn_top[1], count( $changelog_page_20 ) );

        // compare
        if ( $changelog_page_20 === $svn_20 )
            return;

        $message = sprintf(
            'SVN first line: %s' . "\n" . 'Changelog page first line: %s' . "\n" . '%s',
            serialize($svn_20[0]),
            serialize($changelog_page_20[0]),
            $changelog_page_url
        );
        $subject = sprintf(
            '[%s] Changelog mismatch',
            $plugin
        );
        $sent = wp_mail( $this->alert_address, $subject, $message );
        if ( false === $sent )
            error_log( 'O1_Plugin_Changelog_Checker could not sent notification:' . $subject );
    }

    public function plugin_link( $actions, $plugin_file, $plugin_data, $context ) {

        if ( ! in_array( $context, array( 'search', 'mustuse', 'dropins' ) ) ) {
            $watching = get_option( 'o1_plugin_changelog' );
            if ( ! $watching )
                $watching = array();
            $watch = in_array( $plugin_file, $watching );
            $action = $watch ? 'Unwatch' : '<span style="color:green">Watch</span>';
            $actions['watch'] = sprintf( '<a data-plugin-file="%s" href="#">%s</a>', $plugin_file, $action );
        }
        return $actions;
    }

    public function watch_ajax() {

        check_ajax_referer( 'o1_plugin_changelog', '_nonce' );

        $plugin_file = sanitize_text_field( $_POST['plugin'] );
        $watching = get_option( 'o1_plugin_changelog' );
        if ( ! $watching )
            $watching = array();
        $watch = in_array( $plugin_file, $watching );
        $color = $watch ? '' : ' ';

        if ( $watch ) {
            // remove
            $action = '<span style="color:green">Watch</span>';
            $key = array_search($plugin_file, $watching);
            unset( $watching[$key] );
        } else {
            // add
            $action = 'Unwatch';
            $watching[] = $plugin_file;
        }

        update_option( 'o1_plugin_changelog', $watching );
        wp_send_json_success( $action );

    }

    public function plugin_script( $hook ) {

        // only on Plugins page
        if ( 'plugins.php' !== $hook )
            return;

        wp_enqueue_script( 'o1_plugin_changelog_admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ) );
        $nonce = wp_create_nonce( 'o1_plugin_changelog' );
        wp_localize_script( 'o1_plugin_changelog_admin', 'O1_PluginChangelog_nonce', $nonce );
    }

    static function activation() {

        wp_schedule_event( time(), 'daily', 'o1_daily_changelog_check' );
    }

    public function deactivation() {

        wp_clear_scheduled_hook( 'o1_daily_changelog_check' );
    }

}

new O1_Plugin_Changelog_Checker();

register_activation_hook( __FILE__, array( 'O1_Plugin_Changelog_Checker', 'activation' ) );
