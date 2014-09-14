<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin Name: Gauntlet Security
 * Description: Performs a detailed security analysis of your WordPress installation. Gives tips on how to make your site more secure.
 * Plugin URI: 
 * Author: Cornelius Bergen, Matchbox Creative
 * Author URI: http://matchboxcreative.com
 * Version: 1.0
 * Text Domain: gauntlet
 */


/*
 * The main Gauntlet Security class
 */
if ( ! class_exists( 'Gauntlet_Security' ) )
{
    final class Gauntlet_Security
    {    
    	private $plugin_slug = 'gauntlet-security';
    	private $test_runner;

        public function __construct()
        {
            if( ! is_admin() )
                return;
                        
            // Add "Scan" link to plugin listing
            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_action_link' ), 10, 2 );        

    		// Add the menu item.
    		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );        
        
    		// Load admin style sheet and JavaScript.
    		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

            // WP Ajax action for a single test
        	add_action( 'wp_ajax_run_a_test', array( $this, 'run_a_test' ) );

            // Set up test runner
        	require_once( plugin_dir_path( __FILE__ ) . 'admin/includes/classes/gus_TestRunner.php' );
            $this->test_runner = new gus_TestRunner();
        }
    
        public function add_action_link( $links )
        {
            $action_link = '<a href="'. admin_url('admin.php?page=gauntlet-security') . '">Scan</a>';
            array_unshift($links, $action_link); 
            return $links; 
        }
    
        public function add_plugin_admin_menu()
        {
            $page_title = 'Gauntlet Security';
            $menu_title = 'Gauntlet Security';
            $capability = 'manage_options';
            $top_level_slug = $this->plugin_slug;
            $function = array( $this, 'display_plugin_admin_page' );

            // Add a sub-menu item under "Tools"
            $this->plugin_screens[] = add_management_page( 
                $page_title, 
                $menu_title, 
                $capability, 
                $top_level_slug, 
                $function 
            );

            // Add the About page
            $this->plugin_screens[] = add_submenu_page( 
                null,                                       // don't display the sub-page in the menu
                'Gauntlet More Info', 
                'More Info', 
                $capability, 
                'gauntlet-more-info', 
                array( $this, 'display_plugin_about_page' ) 
            );
        }
    
        public function display_plugin_admin_page() 
        {
            // Will this plugin work on this server environment?
            $server_info = $this->meets_requirements();
            
            if( $server_info['pass'] ) 
            {
                $this->test_runner->show_unrun_tests();		
                $test_results = $this->test_runner->results;
                include_once( 'admin/views/admin.php' );
            }
            else
            {
                include_once( 'admin/views/noreqs.php' );
            }        
        }
    
        public function display_plugin_about_page() 
        {
            include_once( 'admin/views/about.php' );
        }
    
        public function enqueue_admin_assets()
        {
    		if ( ! isset( $this->plugin_screens ) ) 
    			return;

    		$screen = get_current_screen();
    		if ( in_array($screen->id, $this->plugin_screens)  ) 
            {
    			wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'admin/assets/css/admin.css', __FILE__ ) );
    			wp_enqueue_style( $this->plugin_slug . '-prettifier-css', plugins_url( 'admin/assets/js/google-code-prettify/prettify.css', __FILE__ ) );
    			wp_enqueue_script( $this->plugin_slug . '-ajaxq', plugins_url( 'admin/assets/js/ajaxq.js', __FILE__ ) );
    			wp_enqueue_script( $this->plugin_slug . '-mustache-script', plugins_url( 'admin/assets/js/mustache.min.js', __FILE__ ) );
    			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'admin/assets/js/admin.js', __FILE__ ), array( 'jquery' ) );
    			wp_enqueue_script( $this->plugin_slug . '-prettifier', plugins_url( 'admin/assets/js/google-code-prettify/prettify.js', __FILE__ ) );
    		}
        }

        public function run_a_test()
        {
            if( ! current_user_can('manage_options') )
                return;
    
        	check_ajax_referer( 'run-the-gauntlet', 'nonce' );
            
            try
            {
                $test_result = $this->test_runner->run($_POST['test_id']);
            }
            catch(Exception $e)
            {
                $test_result = array('test_id' => false);
            }

            header( "Content-Type: application/json" );
            echo json_encode($test_result);
        	die(); // this is required to return a proper result
        }

        private function meets_requirements()
        {
            $pass_reqs = true;
            $req_wp_version = '3.4';
            $req_php_version = '5.2';

        	global $is_apache, $is_IIS, $is_iis7, $is_nginx;
        
            if( isset($is_apache) && $is_apache )
            {
                $web_server = 'Apache web server';
            }
            else
            {
                if( (isset($is_IIS) && $is_IIS) || isset($is_iis7) && $is_iis7 )
                {
                    $web_server = 'Microsoft-IIS web server';
                    $pass_reqs = false;
                }
                if( isset($is_nginx) && $is_nginx )
                {
                    $web_server = 'NGINX web server';
                    $pass_reqs = false;
                }
            }
            if( ! isset($web_server) )
            {
                $web_server = "Can't identify the web server - assuming Apache.";
            }

            $wp_version = get_bloginfo('version');
    		if( ! version_compare($req_wp_version, $wp_version, "<=") )
    		{
                $pass_reqs = false;
            }

            $php_version = phpversion();
    		if( ! version_compare($req_php_version, $php_version, "<=") )
    		{
                $pass_reqs = false;
            }
        
            if( is_multisite() )
            {
                $pass_reqs = false;
            }

            return array(
                'pass' => $pass_reqs,
                'req_wp_version' => $req_wp_version,
                'req_php_version' => $req_php_version,
                'wp_version' => $wp_version,
                'php_version' => $php_version,
                'web_server' => $web_server,
                'multisite' => is_multisite(),
            );
        }
    }
}
new Gauntlet_Security();