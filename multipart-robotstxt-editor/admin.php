<?php
class pc_robotstxt_admin {

	function pc_robotstxt_admin() {
		// stuff to do when the plugin is loaded
		add_action('admin_menu', array(&$this, 'admin_menu'));
	}

	function admin_menu() {
		add_options_page('PC Robots.txt Settings', 'PC Robots.txt', 'manage_options', __FILE__, array(&$this, 'settings_page'));
	}// end function

	function settings_page() {
		
		global $pc_robotstxt;
		$options = $pc_robotstxt->get_options();
		
		if ( isset($_POST['update']) ) {
			
			// check user is authorised
			if ( function_exists('current_user_can') && !current_user_can('manage_options') )
				die('Sorry, not allowed...');
			check_admin_referer('pc_robotstxt_settings');

			$options['user_agents'] = trim($_POST['user_agents']);

			isset($_POST['remove_settings']) ? $options['remove_settings'] = true : $options['remove_settings'] = false;

			update_option('pc_robotstxt', $options);

			echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
		
		}// end if

		echo '<div class="wrap">'
			.'<h2>PC Robots.txt Settings</h2>'
			.'<form method="post">';
		if ( function_exists('wp_nonce_field') ) wp_nonce_field('pc_robotstxt_settings');
		echo '<h3>User Agents and Rules for this blog</h3>'
			.'<p>As a general rule robots will obey the more specific rules over any generic rules, no matter where they appear in the robots.txt file.</p>'
			.'<p>You can <a href="'.get_bloginfo('url').'/robots.txt" target="_blank" onclick="window.open(\''.get_bloginfo('url').'/robots.txt\', \'popupwindow\', \'resizable=1,scrollbars=1,width=760,height=500\');return false;">preview your robots.txt file</a> or visit <a href="http://www.robotstxt.org/" target="_blank" title="robotstxt.org">robotstxt.org</a> to find out more about the robots.txt standard.</p>'
			.'<table class="form-table">'
			.'<tr>'
				.'<td colspan="2"><textarea name="user_agents" rows="6" id="user_agents" style="width:99%;height:300px;">'.stripslashes($options['user_agents']).'</textarea></td>'
			.'</tr>'
			.'<tr>'
				.'<th scope="row">Delete settings when deactivating this plugin:</th>'
				.'<td><input type="checkbox" id="remove_settings" name="remove_settings"';
					if ( $options['remove_settings'] ) echo 'checked="checked"';
					echo ' /> <span class="setting-description">When you tick this box all saved settings will be deleted when you deactivate this plugin.</span></td>'
			.'</tr>'
			.'</table>'
			.'<p class="submit"><input type="submit" name="update" class="button-primary" value="Save Changes" /></p>'
			.'</form>'
			.'</div>';
		
	}// end function

}// end class
$pc_robotstxt_admin = new pc_robotstxt_admin;
