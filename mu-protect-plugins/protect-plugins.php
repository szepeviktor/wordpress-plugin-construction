<?php
/*
Plugin Name:       Protect normal plugins (MU)
Version:           1.2.0
Description:       Prevent deletion of normal plugins
Plugin URI:        https://github.com/szepeviktor/wordpress-plugin-construction
Author:            Viktor Szépe
License:           GNU General Public License v2
License URI:       http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

if ( ! function_exists( 'add_filter' ) ) {
	error_log( 'Malicious traffic detected: protect_plugins_direct_access '
		. addslashes( $_SERVER['REQUEST_URI'] )
	);
	ob_get_level() && ob_end_clean();
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.0 403 Forbidden' );
	exit();
}

class O1_Protect_Plugins {

	/**
	 * List of protected plugins.
	 *
	 * Add your plugins here! jQuery one-liner to list plugin paths.
	 *
	 *     // jQuery
	 *     //var parser=document.createElement('a');jQuery('#wpbody .plugins .plugin-title .deactivate a').each(function(){parser.href=jQuery(this).attr('href');console.log(decodeURIComponent(parser.search.split('&')[1].split('=')[1]));});
	 *     // ECMAScript 5.1 (ECMA-262)
	 *     var nodes=[].slice.call(document.querySelectorAll('#wpbody .plugins .plugin-title .deactivate a'));nodes.forEach(function(node){console.log(decodeURIComponent(node.search.split('&')[1].split('=')[1]));});
	 *
	 * @var array
	 * @access private
	 */
	private $protected_plugins = array(
		'password-bcrypt/wp-password-bcrypt.php',
	);

	/**
	 * Constructor.
	 *
	 * Registers filters, actions for protected plugins.
	 *
	 * @access public
	 */
	public function __construct() {

		// @FIXME Is it faster this way? add_filter( 'pre_option_active_plugins', array( $this, 'fix_protected' ) );
		foreach ( $this->protected_plugins as $protected ) {
			// reactivate on deactivation
			add_action( 'deactivate_' . $protected, array( $this, 'reactivate' ) );
			// remove Deactivate and Delete actions
			add_filter( 'network_admin_plugin_action_links_' . $protected, array( $this, 'remove_actions' ) );
			add_filter( 'plugin_action_links_' . $protected, array( $this, 'remove_actions' ) );
		}
	}

	/**
	 * Activate a plugin when it is deactivated.
	 *
	 * @access public
	 */
	public function reactivate() {

		add_filter( 'pre_update_option_' . 'active_plugins',
			array( $this, 'revert_values' ),
			10,
			2
		);
		add_filter( 'pre_update_site_option_' . 'active_sitewide_plugins',
			array( $this, 'revert_values' ),
			10,
			2
		);
	}

	/**
	 * Revert the previous value.
	 *
	 * @access public
	 * @param string $value The new value.
	 * @param bool $old_value The previous value.
	 * @return string The previous value.
	 */
	public function revert_values( $value, $old_value ) {

		return $old_value;
	}

	/**
	 * Remove the "Deactivate" and "Delete" links from plugin actions.
	 *
	 * @access public
	 * @param string $plugin Base plugin path from plugins directory.
	 * @return array Remaining plugin actions.
	 */
	public function remove_actions( $actions ) {

		$removed_actions = array( 'deactivate', 'delete', 'edit' );
		foreach ( $removed_actions as $action ) {
			if ( isset( $actions[ $action ] ) ) {
				unset( $actions[ $action ] );
			}
		}

		return $actions;
	}
}

new O1_Protect_Plugins();
