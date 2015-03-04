<?php
/**
 * Plugin Name: Frontend Debugger
 * Plugin URI: http://wordpress.org/plugins/frontend-debugger/
 * Description: Display output of all active plugins
 * Version: 0.1
 * Author: Viktor Szépe
 * Author URI: http://www.online1.hu/webdesign/
 * License: GNU General Public License (GPL) version 2
 */

if ( ! function_exists( 'add_filter' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

class Frontend_Debugger {

    private static $singletone;
    private $debugger_template_path;
    private $header_separator = '<!-- frontend_debugger_HEADER -->';
    private $was_loop_start = false;
    private $footer_separator = '<!-- frontend_debugger_FOOTER -->';
    /**
     * Parts of HTML output.
     */
    private $parts = array();
    /**
     * Template files.
     */
    private $includes = array();

    public function __construct() {

        if ( is_admin() )
            return;

        $this->debugger_template_path = plugin_dir_path( __FILE__ ) . 'template/frontend.php';

        add_filter( 'template_include', array( $this, 'load_template' ) );
        add_action( 'wp_loaded', array( $this, 'ob_pre' ) );
        add_action( 'shutdown', array( $this, 'ob_post' ), 0 );
        add_action( 'get_header', array( $this, 'set_header' ) );
        // no way to detect END OF HEADER
        add_action( 'loop_start', array( $this, 'set_loop_start' ) );
        add_filter( 'the_content', array( $this, 'print_content_id' ), 0 );
        add_action( 'get_footer', array( $this, 'set_footer' ) );
    }

    public static function get_instance() {

        if ( ! isset( self::$singletone ) )
            self::$singletone = new Frontend_Debugger();

        return self::$singletone;
    }

    public function load_template( $name ) {

        $this->includes[] = $name;
        echo $name; exit;
        $header = preg_split( '/\bget_header\s*\(.*\)\s*;/', $html, 1 );

        return $name;
    }

    public function set_header( $name ) {

        $name = (string) $name;
        if ( '' === $name )
            $name = 'header.php';
        $this->includes[] = get_stylesheet_directory() . '/' . $name;

    }

    // no way to detect END OF HEADER
    public function set_loop_start() {

        if ( $this->was_loop_start )
            return;

        print $this->header_separator;
        $this->was_loop_start = true;
    }

    public function print_content_id( $content ) {

        print "<!-- frontend_debugger_post_ID:" . get_the_ID() . " -->";

        return $content;
    }

    public function set_footer( $name ) {

        $name = (string) $name;
        if ( '' === $name )
            $name = 'footer.php';
        $this->includes[] = get_stylesheet_directory() . '/' . $name;

        print $this->footer_separator;
    }

    public function ob_pre() {

        ob_start();
    }

    public function ob_post() {
        $html = ob_get_contents();
        ob_end_clean();

        // no way to detect END OF HEADER
        $header = explode( $this->header_separator, $html );
        if ( count( $header ) !== 2 )
            die( 'Header separator is missing!' );
        $this->part['header'] = htmlspecialchars( $header[0] );
        $content = explode( $this->footer_separator, $header[1] );
        if ( count( $content ) !== 2 )
            die( 'Footer separator is missing!' );
        $this->part['content'] = htmlspecialchars( $content[0] );
        $this->part['footer'] = htmlspecialchars( $content[1] );

        wp_reset_query();
        $thumbnails = '';
        if ( have_posts() ) :
            while ( have_posts() ) {
                the_post();
                if ( has_post_thumbnail() ) :
                    $thumbnails .= htmlspecialchars( get_the_post_thumbnail( null, 'thumbnail' ) );
                    $thumbnails .= "<br/>";
                    $thumbnails .= get_the_post_thumbnail( null, 'thumbnail' );
                endif;
            }
        endif;
        $this->part['thumbnails'] = $thumbnails;

        $this->part['includes'] = $this->includes;

        require( $this->debugger_template_path );
    }

    public function get_parts() {

        return $this->part;
    }
}

Frontend_Debugger::get_instance();
