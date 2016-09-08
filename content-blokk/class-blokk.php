<?php
/**
 * _file comment
 *
 * @package blokk
 */


/**
 * Register a custom post type called blokk.
 */
class Blokk {

    public function __construct() {

        add_action( 'init', array( $this, 'register_cpt' ) );
        add_action( 'init', array( $this, 'register_shortcodes' ) );
        // Polylang support
        add_filter( 'pll_get_post_types', array( $this, 'pll_get_post_types' ), 0 );
    }

    public function pll_get_post_types( $types ) {

        return array_merge( $types, array( 'blokk' => 'blokk' ) );
    }

    public function register_cpt() {

        $args = array (
            'label' => 'Blokk',
            'labels' => array (
                'name' => 'Blokks',
                'singular_name' => 'Blokk',
                'menu_name' => 'Blokks',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Blokk',
                'new_item' => 'New Blokk',
                'edit' => 'Edit',
                'edit_item' => 'Edit Blokk',
                'view' => 'View Blokk',
                'view_item' => 'View Blokk',
                'all_items' => 'All Blokks',
                'search_items' => 'Search Blokks',
                'not_found' => 'No Blokks Found',
                'not_found_in_trash' => 'No Blokks Found in Trash',
                'parent' => 'Parent Blokk',
                'parent_item_colon' => 'Parent Blokk:',
                'feature_image' => 'Featured Image',
                'set_featured_image' => 'Set featured image',
                'remove_featured_image' => 'Remove featured image',
                'use_featured_image' => 'Use as featured image',
                'archives' => 'Blokk Archives',
                'insert_into_item' => 'Insert into Blokk',
                'uploaded_to_this_item' => 'Uploaded to this Blokk',
                'filter_items_list' => 'Filter Blokks lists',
                'items_list_navigation' => 'Blokks navigation',
                'items_list' => 'Blokks list',
            ),
            'description' => 'Content blokk',
            'public' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'menu_icon' => 'dashicons-layout',
            'menu_position' => 18,
            'capability_type' => 'post',
            'map_meta_cap' => true,
            'hierarchical' => false,
            'supports' => array (
                0 => 'title',
                1 => 'editor',
                2 => 'revisions',
            ),
            'has_archive' => false,
            'rewrite' => false,
            'query_var' => false,
            'can_export' => true,
        );
        register_post_type( 'blokk', $args );
    }

    public function register_shortcodes() {

        add_shortcode( 'blokk', array( $this, 'blokk_shortcode' ) );
    }

    /**
     * Return a blokks content by its ID or slug
     *
     * @param
     * @param
     * @param
     *
     * @return string Blokk contents.
     */
    public function blokk_shortcode( $attr, $content, $shortcode_tag ) {

        $attr = shortcode_atts( array(
            'id'   => 0,
            'name' => '',
        ), $attr, $shortcode_tag );
        $post_content = '';

        // Get blokk by name or slug
        if ( 0 !== $attr['id'] ) {
            $post = get_post( $attr['id'] );
        } elseif ( '' !== $attr['name'] ) {
            $post = get_page_by_path( $attr['name'], OBJECT, 'blokk' );
        }

        // Display content or a warning
        if ( $post instanceof WP_Post ) {
            $post_content = apply_filters( 'the_content', $post->post_content );
        } elseif( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $post_content = '<span style="color: red;">WARNING: Invalid blokk ID/slug!</span>';
        }

        return $post_content;
    }
}
