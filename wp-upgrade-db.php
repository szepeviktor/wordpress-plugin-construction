<?php
/**
 * Force database upgrade.
 *
 * Copy this file beside wp-load.php
 * then remove it.
 */

define( 'WP_INSTALLING', true );

require './wp-load.php';

// For wp_guess_url()
define( 'WP_SITEURL', get_option( 'siteurl' ) );
require_once 'wp-admin/includes/upgrade.php';

wp_upgrade();
delete_site_transient( 'update_core' );

print( 'WordPress database upgrade OK.' );
