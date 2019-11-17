<?php
/**
 * Disable registration emails to admin.
 *
 * @wordpress-plugin
 * Plugin Name: Disable registration emails to admin (MU)
 * Plugin URI:  https://github.com/szepeviktor/wordpress-plugin-construction
 * Description: Disable admin notification on new user registration.
 * Version:     1.0.0
 * License:     The MIT License (MIT)
 * Author:      Viktor Szépe
 * Author URI:  https://github.com/szepeviktor
 */

/**
 * Initiates email notifications related to the creation of new users.
 *
 * Notifications are sent only to the newly created user.
 *
 * @param int    $user_id
 * @param string $notify
 */
function o1_send_new_user_notifications( $user_id, $notify = 'user' ) {
    wp_new_user_notification( $user_id, null, $notify );
}

remove_action( 'register_new_user', 'wp_send_new_user_notifications' );
add_action( 'register_new_user', 'o1_send_new_user_notifications' );

/**
 * Disable notification in BuddyPress.
 */
add_filter( 'bp_core_send_user_registration_admin_notification', '__return_false' );
