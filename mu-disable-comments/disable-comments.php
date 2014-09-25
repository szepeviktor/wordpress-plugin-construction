<?php
/*
Plugin Name: Disable Comments MU
Plugin URI: http://wordpress.org/extend/plugins/disable-comments/
Description: Globally disables comments.
Version: 0.1
Requires at least: 3.6
Author: Samir Shah
Author URI: http://rayofsolaris.net/
License: GPL2
*/

if( !defined( 'ABSPATH' ) )
    exit;

class Disable_Comments_MU {
    private $options;
    private $networkactive;
    private $modified_types = array();

    function __construct() {
        // are we network activated?
        $this->networkactive = is_multisite();

        add_action( 'widgets_init', array( $this, 'disable_rc_widget' ) );
        add_filter( 'wp_headers', array( $this, 'filter_wp_headers' ) );
        // before redirect_canonical
        add_action( 'template_redirect', array( $this, 'filter_query' ), 9 );

        // admin bar filtering has to happen here since WP 3.6
        add_action( 'template_redirect', array( $this, 'filter_admin_bar' ) );
        add_action( 'admin_init', array( $this, 'filter_admin_bar' ) );

        // this can happen later
        add_action( 'wp_loaded', array( $this, 'setup_filters' ) );
    }

    function setup_filters(){
/* It does not work this way. Something is too early?
        $typeargs = array( 'public' => true );
        if( $this->networkactive )
            // stick to known types for network
            $typeargs['_builtin'] = true;
        $types = get_post_types( $typeargs, 'objects' );
        foreach ( $types as $type ) {
            // we need to know what native support was for later
            if ( post_type_supports( $type, 'comments' ) ) {
                $this->modified_types[] = $type;
                remove_post_type_support( $type, 'comments' );
                remove_post_type_support( $type, 'trackbacks' );
            }
        }
*/
        add_filter( 'comments_open', array( $this, 'filter_comment_status' ), 20, 2 );
        add_filter( 'pings_open', array( $this, 'filter_comment_status' ), 20, 2 );

        // Filters for the admin only
        if ( is_admin() ) {
//commented below            add_action( 'admin_print_footer_scripts', array( $this, 'discussion_notice' ) );

            // remember the original post status
            add_action( 'edit_form_advanced', array( $this, 'edit_form_inputs' ) );
            add_action( 'edit_page_form', array( $this, 'edit_form_inputs' ) );

            add_action( 'admin_menu', array( $this, 'filter_admin_menu' ), 9999 );    // do this as late as possible
            add_action( 'admin_head', array( $this, 'hide_dashboard_bits' ) );
            add_action( 'wp_dashboard_setup', array( $this, 'filter_dashboard' ) );
            add_filter( 'pre_option_default_pingback_flag', '__return_zero' );
        }
        // Filters for front end only
        else {
            add_action( 'template_redirect', array( $this, 'check_comment_template' ) );
        }
    }

    function check_comment_template() {
        if ( is_singular() ) {
            // Kill the comments template. This will deal with themes that don't check comment stati properly!
            add_filter( 'comments_template', array( $this, 'dummy_comments_template' ), 20 );
            // Remove comment-reply script for themes that include it indiscriminately
            wp_deregister_script( 'comment-reply' );
        }
    }

//Could the template be this __FILE__ with a hack?
    function dummy_comments_template() {
        return dirname( __FILE__ ) . '/comments-template.php';
    }

    function filter_wp_headers( $headers ) {
        unset( $headers['X-Pingback'] );
        return $headers;
    }

    function filter_query() {
        if ( is_comment_feed() ) {
            if ( isset( $_GET['feed'] ) ) {
                wp_redirect( remove_query_arg( 'feed' ), 301 );
                exit;
            }

            // redirect_canonical will do the rest
            set_query_var( 'feed', '' );
            redirect_canonical();
        }
    }

    function filter_admin_bar() {
        if ( is_admin_bar_showing() ) {
            // Remove comments links from admin bar
            // WP <3.3
            remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 50 );
            // WP 3.3
            remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
            if ( is_multisite() )
                add_action( 'admin_bar_menu', array( $this, 'remove_network_comment_links' ), 500 );
        }
    }

    function remove_network_comment_links( $wp_admin_bar ) {
        if ( $this->networkactive ) {
            foreach ( (array) $wp_admin_bar->user->blogs as $blog )
                $wp_admin_bar->remove_menu( 'blog-' . $blog->userblog_id . '-c' );
        } else {
            // We have no way to know whether the plugin is active on other sites, so only remove this one
            $wp_admin_bar->remove_menu( 'blog-' . get_current_blog_id() . '-c' );
        }
    }

    function edit_form_inputs() {
        global $post;
        // Without a dicussion meta box, comment_status will be set to closed on new/updated posts
        if ( in_array( $post->post_type, $this->modified_types ) ) {
            printf( '<input type="hidden" name="comment_status" value="%s" /><input type="hidden" name="ping_status" value="%s" />',
                $post->comment_status,
                $post->ping_status
            );
        }
    }

/* same here:  $types
    function discussion_notice() {
        if ( get_current_screen()->id == 'options-discussion' ) {
            $names = array();
            foreach( $this->options['disabled_post_types'] as $type )
                $names[$type] = get_post_type_object( $type )->labels->name;

            printf( '<script>jQuery(function ($) { $(".wrap h2").first().after( %s ); });</script>',
                json_encode(  sprintf( '<div style="color: #900"><p>Note:
                    The <em>Disable Comments</em> plugin is currently active, and comments are completely disabled on: %s.
                    Many of the settings below will not be applicable for those post types.</p></div>',
                    implode( __( ', ' ), $names )
                ))
            );
        }
    }
*/

    function filter_admin_menu() {
        global $pagenow;

        if ( $pagenow == 'comment.php' || $pagenow == 'edit-comments.php' || $pagenow == 'options-discussion.php' )
            wp_die( __( 'Comments are closed.' ), '', array( 'response' => 403 ) );

        remove_menu_page( 'edit-comments.php' );
        remove_submenu_page( 'options-general.php', 'options-discussion.php' );
    }

    function filter_dashboard() {
        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
    }

    function hide_dashboard_bits() {
        if ( 'dashboard' == get_current_screen()->id )
            add_action( 'admin_print_footer_scripts', array( $this, 'dashboard_js' ) );
    }

    function dashboard_js() {
        if ( version_compare( $GLOBALS['wp_version'], '3.8', '<' ) ) {
            // getting hold of the discussion box is tricky. The table_discussion class is used for other things in multisite
            echo '<script>jQuery(function($){ $("#dashboard_right_now .table_discussion").has(\'a[href="edit-comments.php"]\').first().hide(); });</script>';
        } else {
            echo '<script>jQuery(function($){ $("#dashboard_right_now .comment-count, #latest-comments").hide(); });</script>';
        }
    }

    function filter_comment_status( $open, $post_id ) {
        return false;
    }

    function disable_rc_widget() {
        // This widget has been removed from the Dashboard in WP 3.8 and can be removed in a future version
        unregister_widget( 'WP_Widget_Recent_Comments' );
    }

//FIXME make enter_permanent_mode() a wp-cli command

}

new Disable_Comments_MU();
