<?php
/*
Plugin Name: WordPress plugin changelog checker
Version: 0.1.0
*/

//403

class O1_Plugin_Changelog_Checker {

    private $validator_url = "https://wordpress.org/plugins/about/validator/";
    private $compare_lines = 20;
    private $plugin;
    private $alert_address;
    private $changelog_page_url;
    private $svn_url;

    private function get_top_lines( $html ) {

        $lines = array();
        $i = 0;

        foreach( preg_split( '/((\r?\n)|(\r\n?))/', $html ) as $line ) {
            $lines[] = trim( strip_tags( $line ) );
            if ( ++$i > $this->compare_lines )
                break;
        }

        return $lines;
    }

    public function __construct() {

        //TODO: options
        $this->plugin = "better-wp-security";

        add_action( 'o1_daily_changelog_check', array( $this, 'check_changelog' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
    }

    public function check_changelog() {

        $this->alert_address = get_bloginfo( 'admin_email' );
        $this->changelog_page_url = sprintf(
            'https://wordpress.org/plugins/%s/changelog/',
            $this->plugin
        );
        $this->svn_url = sprintf(
            'plugins.svn.wordpress.org/%s/trunk/readme.txt',
            $this->plugin
        );
        $http_args = array( 'sslverify' => false );

        // the Changelog page
        $changelog_page = wp_remote_get( $this->changelog_page_url, $http_args );
        //if ( is_wp_error( $changelog_page ) ) { $response->get_error_message()
        $changelog_page_html = wp_remote_retrieve_body( $changelog_page );
        // if empty(
        preg_match( '/<div class="block-content">(.+)$/sD', $changelog_page_html, $changelog_page_top );
        // 1 !==
        $changelog_page_10 = $this->get_top_lines( $changelog_page_top[1] );

        // readme.txt from trunk parsed with Readme Validator
        $post_args = array( 'body' => array( 'url' => '1', 'readme_url' => $this->svn_url ) );
        $svn = wp_remote_post( $this->validator_url, array_merge( $http_args, $post_args ) );
        //if ( is_wp_error( $changelog_page ) ) { $response->get_error_message()
        $svn_html = wp_remote_retrieve_body( $svn );
        // if empty(
        preg_match( '/<h3>Changelog<\/h3>\n(.+)$/sD', $svn_html, $svn_top );
        // 1 !==, empty([1]
        $svn_10 = $this->get_top_lines( $svn_top[1] );

        // compare
        if ( $changelog_page_10 === $svn_10 )
            return;

        $message = sprintf(
            'SVN first line: %s' . "\n" . 'Changelog page first line: %s' . "\n" . '%s',
            serialize($svn_10[0]),
            serialize($changelog_page_10[0]),
            $this->changelog_page_url
        );
        $subject = sprintf(
            '[%s] Changelog mismatch',
            $this->plugin
        );
        wp_mail( $this->alert_address, $subject, $message );
        // if false ===
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
