<?php
/*
Plugin name: Tiny translation cache (MU)
Description: Cache .mo files in persistent object cache.
Version: 0.1.0
Plugin URI: https://developer.wordpress.org/reference/functions/load_textdomain/
*/

class O1_Tiny_Translation_Cache {

    const GROUP = 'mofile';

    public function __construct() {

        // Prevent usage as a normal plugin in wp-content/plugins
        // We need to cache plugin translations
        if ( did_action( 'muplugins_loaded' ) ) {
            $this->exit_with_instructions();
        }

        // Detect object cache
        if ( ! wp_using_ext_object_cache() ) {
            return;
        }

        add_action( 'muplugins_loaded', array( $this, 'init' ) );
    }

    public function init() {

        add_filter( 'override_load_textdomain', array( $this, 'load_textdomain' ), 30, 3 );
    }

    public function load_textdomain( $override, $domain, $mofile ) {

        // Copied from core
        do_action( 'load_textdomain', $domain, $mofile );
        $mofile = apply_filters( 'load_textdomain_mofile', $mofile, $domain );

        $mo = new \MO();
        $key = $this->get_key( $domain, $mofile );
        $found = null;
        // FIXME unserilalize() and gzinflate( $ )
        $cache = wp_cache_get( $key, self::GROUP, false, $found );

        if ( $found && isset( $cache['entries'], $cache['headers'] ) ) {
            // Cache hit
            $mo->entries = $cache['entries'];
            $mo->set_headers( $cache['headers'] );
        } else {
            // Cache miss
            if ( ! is_readable( $mofile ) || ! $mo->import_from_file( $mofile ) ) {
                return false;
            }
            $translation = array(
                'entries' => $mo->entries,
                'headers' => $mo->headers,
            );
            // Save translation for a week
            // FIXME serilalize() and gzdeflate( $, 6 )
            wp_cache_set( $key, $translation, self::GROUP, WEEK_IN_SECONDS );
        }

        // Setup localization global
        global $l10n;

        if ( array_key_exists( $domain, (array) $l10n ) ) {
            $mo->merge_with( $l10n[ $domain ] );
        }
        $l10n[ $domain ] = &$mo;

        return true;
    }

    private function get_key( $domain, $mofile ) {

        // Hash of text domain and .mo file path
        // @FIXME Why do we need text domain? Isn't the full path exact enough?
        return md5( $domain . $mofile );
    }

    private function exit_with_instructions() {

        $doc_root = array_key_exists( 'DOCUMENT_ROOT', $_SERVER ) ? $_SERVER['DOCUMENT_ROOT'] : ABSPATH;

        $iframe_msg = sprintf( '
<p style="font:14px \'Open Sans\',sans-serif">
<strong style="color:#DD3D36">ERROR:</strong> This is <em>not</em> a normal plugin,
and it should not be activated as one.<br />
Instead, <code style="font-family:Consolas,Monaco,monospace;background:rgba(0,0,0,0.07)">%s</code>
must be copied to <code style="font-family:Consolas,Monaco,monospace;background:rgba(0,0,0,0.07)">%s</code></p>',
            esc_html( str_replace( $doc_root, '', __FILE__ ) ),
            esc_html( str_replace( $doc_root, '', trailingslashit( WPMU_PLUGIN_DIR ) ) . basename( __FILE__ ) )
        );

        exit( $iframe_msg );
    }
}

new O1_Tiny_Translation_Cache();
