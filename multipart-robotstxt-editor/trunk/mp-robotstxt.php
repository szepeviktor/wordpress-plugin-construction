<?php
/*
Plugin Name: Multipart robots.txt editor
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Customize your site's robots.txt and include remote content to it
Version: 0.1
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
*/

if ( ! function_exists( 'add_filter' ) ) {
    // for fail2ban
    error_log( 'File does not exist: errorlog_direct_access '
        . esc_url( $_SERVER['REQUEST_URI'] ) );

    ob_get_level() && ob_end_clean();
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

/**
 * Multipart robots.txt editor WordPress plugin
 *
 */
class Multipart_Robotstxt {

    private $public;
    private $plugin_path;

    public function __construct() {

        register_activation_hook( __FILE__, array( $this, 'activation' ) );

        $this->plugin_path = plugin_dir_path( __FILE__ );
        $this->public = get_option( 'blog_public' );

        if ( $this->public ) {
            remove_action( 'do_robots', 'do_robots' );
            add_action( 'do_robots', array( $this, 'generate' ) );
        }

        add_filter( 'pre_update_option_mprt_records', array( $this, 'purge_remote' ), 10, 2 );
        add_action( 'admin_menu', array( $this, 'settings' ) );
    }

    /**
     * Generate multipart robots.txt by concatenating enebled parts.
     *
     */
    public function generate() {

        // core do_robots() is removed, restore its functionality
        header( 'Content-Type: text/plain; charset=utf-8' );
        do_action( 'do_robotstxt' );

        $options = get_option( 'mprt_records' );
        if ( ! is_array ( $options ) ) {
            // use standard WP robots.txt
            print $this->core_robots() . $this->do_robots();
            return;
        }

        $robotstxt = '';
        $separator =  PHP_EOL . '################' . PHP_EOL . PHP_EOL;

        if ( $options['enable_core_robotstxt_hook'] ) {
            $robotstxt .= trim( $this->core_robots() ) . $separator;
        }

        if ( $options['enable_plugin_robotstxt_hook'] ) {
            $robotstxt .= trim( $this->do_robots() ) . $separator;
        }

        if ( $options['enable_remote_url'] ) {
            $robotstxt .= trim( $this->get_transient( $options['remote_url'] ) ) . $separator;
        }

        if ( $options['enable_manual_records'] ) {
            $robotstxt .= trim( $options['manual_records'] ) . $separator;
        }

        $robotstxt = $this->join_any_robot_records( $robotstxt );

        // prevent empty robots.txt
        if ( '' == trim( $robotstxt ) )
            $robotstxt = $this->core_robots() . $this->do_robots();

        print $robotstxt;
    }

    /**
     * Add admin settings page
     *
     */
    public function settings() {

        require_once( $this->plugin_path . 'display-callbacks.php' );
        require_once( $this->plugin_path . 'sanitize-callbacks.php' );
        if ( ! class_exists( 'Voce_Settings_API' ) ) {
            require_once( $this->plugin_path . 'voce-settings-api.php' );
        }

        $site_url = parse_url( site_url() );
        $path = ( ! empty( $site_url['path'] ) ) ? $site_url['path'] : '';

        $records_page_desc = 'The robots.txt is to disallow access to specific pages of your site for robots.';

        $records_group_desc = sprintf( '
<p>Activates only when you leave Settings / Reading / "Discourage search engines from indexing this site" <strong>unchecked</strong>.</p>
<p>More information on robots.txt in <a href="http://www.w3.org/TR/html4/appendix/notes.html#h-B.4.1.1" target="_blank">World Wide Web Consortium Recommendation</a>,
<a href="https://developers.google.com/webmasters/control-crawl-index/docs/robots_txt" target="_blank">Google\'s Robots.txt Specifications</a>,
<a href="http://moz.com/learn/seo/robotstxt" target="_blank">What is Robots.txt? by MOZ</a>.</p>
<p><a href="https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/multipart-robotstxt-editor/" target="_blank"
>List of useful robots\' user agent IDs</a>,
and an up-to-date ,
recommended sitemaps: <a href="http://smythies.com/robots.txt" target="_blank">smythies.com</a>,
<a href="http://www.lemgo.net/robots.txt" target="_blank">Lemgo</a></p>
<p><a class="button" href="%s" target="_blank">Preview robots.txt</a>',
            home_url( 'robots.txt' )
        );

        $manual_records_default = sprintf( 'User-agent: MJ12bot
Crawl-delay: 10
Disallow: %s/wp-admin/
Disallow: %1$s/%s/

# all robots
User-agent: *
Crawl-delay: 10
Disallow: %1$s/wp-admin/
Disallow: %1$s/%2$s/

#Sitemap: %s
#Sitemap: %s
',
            $path,
            WPINC,
            home_url( 'sitemap.xml' ),
            home_url( 'sitemap_index.xml' )
        );

        Voce_Settings_API::GetInstance()->add_page( 'Multipart robots.txt editor', 'Multipart robots.txt', 'mp-robotstxt',
            'manage_options', $records_page_desc, 'options-general.php', 'no')
            ->add_group( 'Parts of robots.txt', 'mprt_records', '', $records_group_desc )
                ->add_setting( 'Add WordPress core record', 'enable_core_robotstxt_hook', array(
                    'default_value' => true,
                    'display_callback' => 'vs_display_checkbox',
                    'description' => 'Enable or disable this part.',
                    'sanitize_callbacks' => array( 'vs_santize_checkbox' )
                ))->group
                ->add_setting( 'WordPress core record', 'core_robotstxt_hook', array(
                    'default_value' => $this->core_robots(),
                    'display_callback' => 'vs_display_textarea',
                    'description' => 'This is generated content, <strong>not editable</strong>. It makes up the TOP part of your robots.txt.',
                    'sanitize_callbacks' => array( $this, 'sanitize_core_robots_txt' ),
                    'attributes' => array( 'disabled' => true )
                ))->group
                ->add_setting( 'Add plugin and theme generated records', 'enable_plugin_robotstxt_hook', array(
                    'default_value' => true,
                    'display_callback' => 'vs_display_checkbox',
                    'description' => 'Enable or disable this part.',
                    'sanitize_callbacks' => array( 'vs_santize_checkbox' )
                ))->group
                ->add_setting( 'Plugin and theme generated records', 'plugin_robotstxt_hook', array(
                    'default_value' => $this->do_robots(),
                    'display_callback' => 'vs_display_textarea',
                    'description' => 'This is generated content, <strong>not editable</strong>. It comes after the TOP part of your robots.txt.',
                    'sanitize_callbacks' => array( $this, 'sanitize_plugin_robots_txt' ),
                    'attributes' => array( 'disabled' => true )
                ))->group
                ->add_setting( 'Add remote robots.txt', 'enable_remote_url', array(
                    'default_value' => true,
                    'display_callback' => 'vs_display_checkbox',
                    'description' => 'Enable or disable this part.',
                    'sanitize_callbacks' => array( 'vs_santize_checkbox' )
                ))->group
                ->add_setting( 'URL of the remote robots.txt', 'remote_url', array(
                    'default_value' => plugins_url( 'assets/badbots.txt', __FILE__ ),
                    'description' => 'The records from this file is updated <strong>every day</strong>. <a href="http://www.szepe.net/badbots.txt" target="_blank">List of bad bots</a>. This makes up the MIDDLE part.',
                    'sanitize_callbacks' => array( 'vs_sanitize_url' )
                ))->group
                ->add_setting( 'Add custom records', 'enable_manual_records', array(
                    'default_value' => true,
                    'display_callback' => 'vs_display_checkbox',
                    'description' => 'Enable or disable this part.',
                    'sanitize_callbacks' => array( 'vs_santize_checkbox' )
                ))->group
                ->add_setting( 'Custom records', 'manual_records', array(
                    'default_value' => $manual_records_default,
                    'display_callback' => 'vs_display_textarea',
                    'description' => 'Lines starting with a hash sign (#) are comments. This makes up the BOTTOM part.',
                    'sanitize_callbacks' => array( 'vs_sanitize_text' )
                ))->group->page
            ->add_group( 'Plugin options', 'mprt_plugin' )
                ->add_setting( 'Delete records on uninstallation', 'forget_records', array(
                    'default_value' => false,
                    'display_callback' => 'vs_display_checkbox',
                    'description' => 'Detele all settings when the plugin is Uninstalled.',
                    'sanitize_callbacks' => array( 'vs_santize_checkbox' )
            ) );
    }

    /**
     * Retrieve and update the transient when necessary.
     *
     * @param string $remote_url
     * @return variable
     */
    private function get_transient( $remote_url ) {

        $content = get_transient( 'mprt_remote_content' );

        if ( false === $content ) {
            // get the remote content again
            $http_args = array(
                'timeout'     => 10,
                'sslverify'   => false
            );
            $response = wp_remote_get( $remote_url, $http_args );
            if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
                $content = wp_remote_retrieve_body( $response );
            } else {
                // fallback to default robots.txt
                $content = $this->core_robots() . $this->do_robots();
            }

            set_transient( 'mprt_remote_content', $content, DAY_IN_SECONDS );
        }

        return $content;
    }

    /**
     * Clean up old transient on plugin option update.
     *
     */
    public function purge_remote( $new, $old) {

        delete_transient( 'mprt_remote_content' );
        return $new;
    }

    /**
     * Join recorde for any robot
     *
     * 'If the value is "*", the record describes the default access policy for any robot that has not matched any of the other records.
     * It is not allowed to have multiple such records in the "/robots.txt" file.'
     *
     * @param string $robotstxt
     */
    private function join_any_robot_records( $robotstxt ) {
        $records = array();
        $any_records = array();
        $any_policies = array();
        $buffer = '';
        $lines = preg_split( '/\n|\r\n?/', $robotstxt );

        // find records
        foreach ( $lines as $line ) {
            if ( '' == trim( $line ) && ! empty( $buffer ) ) {
                //FIXME "User-agent: crawler\nUser-agent: *\nDisallow: /url" is not handled
                if ( 1 === preg_match( '/\s*User-agent:\s*\*\s*/i', $buffer ) ) {
                    $any_records[] = $buffer;
                } else {
                    $records[] = $buffer;
                }
                $buffer = '';
            } else {
                $buffer .= $line . PHP_EOL;
            }
        }
        // add an empty line at the end
        if ( ! empty( $records ) )
            $records[] = '';

        // remove every line "User-agent:" in it
        $any_policies = preg_replace( '/(^|\n|\r\n?)\s*User-agent:.*(\n|\r\n?)/i', '\\1', $any_records );
        // drop comment lines
        $any_policies = preg_replace( '/(^|\n|\r\n?)\s*#.*(\n|\r\n?)/i', '\\1', $any_policies );

        // 'any' policies
        $any_string = '';
        $any_count = count( $any_records );
        if ( $any_count > 0 ) {
            if ( $any_count > 1 )
                $any_string .= '# ' . $any_count . ' record(s) for any robot joined' .  PHP_EOL;
            $any_string .= 'User-agent: *' . PHP_EOL
                . implode( '', $any_policies ) . PHP_EOL;
        }

        return implode( PHP_EOL, $records ) . $any_string;
    }

    /**
     * Always reset to the core record
     *
     * @param variable $value
     * @param Voce_Setting $setting
     * @param array $args
     * @return variable
     */
    function sanitize_core_robots_txt( $value, $setting, $args ) {

        return $this->core_robots();
    }

    /**
     * Always reset to the output of 'robots_txt' hook
     *
     * @param variable $value
     * @param Voce_Setting $setting
     * @param array $args
     * @return variable
     */
    function sanitize_plugin_robots_txt( $value, $setting, $args ) {

        return $this->do_robots();
    }

    /**
     * Return the core record. (copied from WP core function)
     *
     * @return variable
     */
    private function core_robots() {

        $output = "User-agent: *\n";

        if ( '0' == $this->public ) {
            $output .= "Disallow: /\n";
        } else {
            $site_url = parse_url( site_url() );
            $path = ( ! empty( $site_url['path'] ) ) ? $site_url['path'] : '';
            $output .= "Disallow: $path/wp-admin/\n";
        }
        return $output;
    }

    /**
     * Return the core record. (copied from WP core function)
     *
     * @return variable
     */
    private function do_robots() {

        $output = '';

        /**
         * Filter the robots.txt output.
         *
         * @param string $output Robots.txt output.
         * @param bool   $public Whether the site is considered "public".
         */
        return apply_filters( 'robots_txt', $output, $this->public );
    }

    /**
     * Set default settings on plugin activation.
     *
     */
    public function activation() {

        if ( ! current_user_can( 'activate_plugins' ) )
            return;

        $MPRT = new Multipart_Robotstxt();
        $MPRT->settings();
        Voce_Settings_API::GetInstance()->set_defaults( 'mp-robotstxt' );
        // force? Voce_Settings_API::GetInstance()->set_defaults( 'mp-robotstxt', true );
    }

    /**
     * Delete settings on plugin deletion based on the forget_records option.
     *
     */
    static function uninstall() {

        if ( ! current_user_can( 'activate_plugins' ) )
            return;

        check_admin_referer( 'bulk-plugins' );

        $plugin_option = get_option( 'mprt_plugin' );
        if ( $plugin_option && true == $plugin_option['forget_records'] )
            del_option( 'mprt_records' );
            del_option( 'mprt_plugin' );
    }
}

new Multipart_Robotstxt();

register_uninstall_hook( __FILE__, array( 'Multipart_Robotstxt', 'uninstall' ) );
