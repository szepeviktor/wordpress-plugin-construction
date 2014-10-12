<?php
/*
Plugin Name: Prevent category archive content duplication
Version: 0.2
Description: Prevent category archive content duplication.
Plugin URI: http://cheatsheet.davidprog.hu/
Author: Latkóczy Dávid
Author URI: http://davidprog.hu/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/prevent-category-archive-content-duplication
*/

define( 'PCAD_CUSTOM_TAXONOMY', 'termekkategoria' );
add_action( 'wp', 'prevent_category_archive_duplication' );

function pcad_sub($custom_tax, $parent_id, $recursive = false) {
    $children = get_terms($custom_tax, array(
        'hide_empty' => false,
        'fields'     => 'ids',
        'parent'     => $parent_id
    ));
    // zero children
    if (is_wp_error($children))
        return $recursive ? $parent_id : false;

    // more children
    if (count($children) !== 1)
        return false;

    // 1 child
    $onechild = intval($children[0]);
    return $recursive ? pcad_sub($custom_tax, $onechild, true) : $onechild;
}

function prevent_category_archive_duplication() {
    if (get_query_var('taxonomy') !== PCAD_CUSTOM_TAXONOMY)
        return false;

    $parent_term = get_query_var(PCAD_CUSTOM_TAXONOMY);
    $parent_data = get_term_by('slug', $parent_term, PCAD_CUSTOM_TAXONOMY);

    $childID = pcad_sub(PCAD_CUSTOM_TAXONOMY, $parent_data->term_taxonomy_id);
    if ($childID) {
        wp_redirect(get_term_link($childID, PCAD_CUSTOM_TAXONOMY));
        exit;
    }
}
