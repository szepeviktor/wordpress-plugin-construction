<?php
/*
Plugin Name: Tiny navigation menu cache (MU)
Description: Cache nav menu's HTML content in persistent object cache.
Version: 0.1.1
Constants: TINY_CACHE_NAV_MENU_EXCLUDES
*/

class Tiny_Nav_Menu_Cache {

    const GROUP = 'navmenu';

    public function __construct() {

        add_action( 'init', array( $this, 'init' ) );
    }

    public function init() {

        add_action( 'save_post', array( $this, 'flush_all' ) );
        add_action( 'wp_create_nav_menu', array( $this, 'flush_all' ) );
        add_action( 'wp_update_nav_menu', array( $this, 'flush_all' ) );
        add_action( 'wp_delete_nav_menu', array( $this, 'flush_all' ) );
        add_action( 'split_shared_term', array( $this, 'flush_all' ) );

        // Learned from W3TC Page Cache rules and WP Super Cache rules
        if ( ! wp_using_ext_object_cache() // Object cache is unavailable
            || is_user_logged_in() // User is logged in
            || ! ( isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' === $_SERVER['REQUEST_METHOD'] ) // Not a GET request
            || ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) // DO-NOT-CACHE tag present
        ) {
            return;
        }

        add_filter( 'pre_wp_nav_menu', array( $this, 'get_nav_menu' ), 30, 2 );
        add_filter( 'wp_nav_menu', array( $this, 'save_nav_menu' ), PHP_INT_MAX, 2 );
    }

    public function get_nav_menu( $nav_menu, $args ) {

        $key = $this->get_cache_key( $args );
        // Check excluded nav menus
        if ( false !== $key ) {
            $found = null;
            $cache = wp_cache_get( $key, self::GROUP, false, $found );
            if ( $found ) {

                return $cache;
            }
        }

        return $nav_menu;
    }

    public function save_nav_menu( $nav_menu, $args ) {

        $key = $this->get_cache_key( $args );
        // Check excluded nav menus
        if ( false !== $key ) {
            wp_cache_set( $key, $nav_menu, self::GROUP, DAY_IN_SECONDS );
            $this->remember_key( $key );
        }

        return $nav_menu;
    }

    public function flush_all() {

        foreach ( $this->get_keys() as $key ) {
            wp_cache_delete( $key, self::GROUP );
        }
        wp_cache_delete( 'key_list', self::GROUP );
    }

    private function remember_key( $key ) {

        // @TODO Not atomic
        $found = null;
        $key_list = wp_cache_get( 'key_list', self::GROUP, false, $found );
        if ( $found ) {
            $key_list .= '|' . $key;
        } else {
            $key_list = $key;
        }
        wp_cache_set( 'key_list', $key_list, self::GROUP, DAY_IN_SECONDS );
    }

    private function get_keys() {

        $found = null;
        $key_list = wp_cache_get( 'key_list', self::GROUP, false, $found );
        if ( ! $found ) {
            $key_list = '';
        }

        return explode( '|', $key_list );
    }

    private function get_cache_key( $args ) {

        // Excluded theme locations
        if ( defined( 'TINY_CACHE_NAV_MENU_EXCLUDES' ) && TINY_CACHE_NAV_MENU_EXCLUDES ) {
            $excludes = explode( '|', TINY_CACHE_NAV_MENU_EXCLUDES );

            if ( property_exists( $args, 'theme_location' )
                && ! empty( $args->theme_location )
                && in_array( $args->theme_location, $excludes )
            ) {
                return false;
            }
        }

        return md5( serialize( $args ) . $_SERVER['REQUEST_URI'] );
    }
}

new Tiny_Nav_Menu_Cache();
