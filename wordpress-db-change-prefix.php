<?php
/**
 * Change WordPress table prefix.
 *
 * wp eval-file wordpress-db-change-prefix.php
 */


// @TODO Compare with https://github.com/iandunn/wp-cli-rename-db-prefix/blob/master/wp-cli-rename-db-prefix.php

$new_prefix = 'NEWPREFIX_';

$prefix_change_error = wpdb_change_prefix( $new_prefix );
if ( false !== $prefix_change_error ) {
    print 'Error: ' . $prefix_change_error . PHP_EOL;
} else {
    print 'Change $table_prefix in wp-config.php to ' . $new_prefix . PHP_EOL;
    // $wp_config_regex = '/(\$table_prefix\s*=\s*)([\'"]).+?\\2(\s*;)/';
    // $config = preg_replace( $wp_config_regex, "\${1}'$new_prefix'\${3}", $config );
}

function wpdb_change_prefix( $new_prefix = 'wp_' ) {

    global $wpdb;

    /**
     * 1. Rename database tables
     */
    $tables_columns = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->base_prefix . '%' ), 0 );
    foreach ( $tables_columns as $table ) {
        // Table name without prefix
        $table_basename = substr( $table, strlen( $wpdb->base_prefix ) );
        // prepare() would quote table names
        $rename_query = $wpdb->query( sprintf( 'ALTER TABLE `%s` RENAME `%s`',
            $wpdb->base_prefix . $table_basename,
            $new_prefix . $table_basename
        ) );
        if ( false === $rename_query ) {

            return 'table rename failure';
        }
    }

    /**
     * 2. Rename each blogs' options on multisite
     */
    if ( is_multisite() ) {

        // Get list of blog id-s
        $blogs_columns = $wpdb->get_col( sprintf(
            'SELECT `blog_id` FROM `%s` WHERE `public` = 1 AND `archived` = 0 AND `mature` = 0 AND `spam` = 0 ORDER BY `blog_id` DESC',
            $new_prefix . 'blogs'
        ) );

        // Update each blog's user_roles option
        if ( is_array( $blogs_columns ) ) {
            foreach ( $blogs_columns as $blog ) {
                $new_blog_options_table = $new_prefix . $blog. '_options';
                $blog_id_query = $wpdb->query( $wpdb->prepare(
                    sprintf( 'UPDATE `%s`', $new_blog_options_table ) . ' SET `option_name` = %s WHERE `option_name` = %s LIMIT 1',
                    $new_blog_option_table . '_user_roles',
                    $wpdb->base_prefix . $blog . '_user_roles'
                ) );
                if ( false === $blog_id_query ) {

                    return 'user_roles update failure';
                }
            }
        }
    }

    /**
     * 3. Update wp_options table
     */
    $update_option_query = $wpdb->query( $wpdb->prepare(
        sprintf( 'UPDATE `%soptions`', $new_prefix ) . 'SET `option_name` = %s WHERE `option_name` = %s LIMIT 1',
        $new_prefix . 'user_roles',
        $wpdb->base_prefix . 'user_roles'
    ) );
    if ( false === $update_option_query ) {

        return 'wp_options update failure';
    }

    /**
     * 4. Update wp_usermeta table
     */
    $usermeta_results = $wpdb->get_results( $wpdb->prepare(
        sprintf( 'SELECT * FROM `%susermeta`', $new_prefix ) . ' WHERE meta_key LIKE %s',
        $wpdb->base_prefix . '%'
    ) );
    foreach ( $usermeta_results as $meta ) {
        // Meta name without prefix
        $meta_basename = substr( $meta->meta_key, strlen( $wpdb->base_prefix ) );

        $meta_update_query = $wpdb->query( $wpdb->prepare(
            sprintf( 'UPDATE `%susermeta`', $new_prefix ) . ' SET meta_key = %s WHERE meta_key = %s LIMIT 1',
            $new_prefix . $meta_basename,
            $meta->meta_key
        ) );
        if ( false === $meta_update_query ) {

            return 'wp_usermeta update failure';
        }
    }

    return false;
}
