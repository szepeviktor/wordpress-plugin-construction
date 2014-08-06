<?php
/**
 * Plugin Name: Prevent category archive content duplication
 * Version:     0.1
 * Plugin URI:  http://cheatsheet.davidprog.hu/
 * Description: Prevent category archive content duplication
 * Author:      Latkóczy Dávid
 * Author URI:  http://davidprog.hu/
 */

add_action('wp', 'prevent_category_archive_duplication');

function pcad_sub($custom_tax, $parent_id, $recursive=false) {
    $children = get_terms($custom_tax, array(
        'hide_empty' => false,
        'fields'     => 'ids',
        'parent'     => $parent_id
    ));
    // zero children
    if (is_wp_error($children)) return $recursive? $parent_id : false;

    // more children
    if (count($children) !== 1) return false;

    // 1 child
    $onechild = intval($children[0]);
    return $recursive? pcad_sub($custom_tax, $onechild, true) : $onechild;
}

function prevent_category_archive_duplication() {
//FIXME
    $custom_tax = 'termekkategoria';
    if (get_query_var('taxonomy') !== $custom_tax) return false;

    $parent_term = get_query_var($custom_tax);
    $parent_data = get_term_by('slug', $parent_term, $custom_tax);

    $childID = pcad_sub($custom_tax, $parent_data->term_taxonomy_id);
    if ($childID) {
        wp_redirect(get_term_link($childID, $custom_tax));
        exit;
    }
}

