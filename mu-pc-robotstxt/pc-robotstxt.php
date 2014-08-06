<?php
/*
Plugin Name: PC Robots.txt
Plugin URI: http://petercoughlin.com/robotstxt-wordpress-plugin/
Description: Automatically creates a virtual robots.txt file for your blog.
Author: Peter Coughlin
Version: 1.3
Author URI: http://petercoughlin.com/

Version History
----------------
1.3		Now uses do_robots hook and checks for is_robots() in action
1.2		Added support for existing sitemap.xml.gz file
1.1		Added option to delete or preserve settings on deactivation
		Added action link to settings page
1.0		Initial release
*/

class pc_robotstxt {

	function pc_robotstxt() {

		// make sure we have the right paths...
		if ( !defined('WP_PLUGIN_URL') ) {
			if ( !defined('WP_CONTENT_DIR') ) define('WP_CONTENT_DIR', ABSPATH.'wp-content');
			if ( !defined('WP_CONTENT_URL') ) define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
			if ( !defined('WP_PLUGIN_DIR') ) define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
			define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
		}// end if

		// stuff to do when the plugin is loaded
		// i.e. register_activation_hook(__FILE__, array(&$this, 'activate'));
		// i.e. register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
		register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));

		// add_filter('hook_name', 'your_filter', [priority], [accepted_args]);
		// i.e. add_filter('the_content', array(&$this, 'filter'));
		
		// add_action ('hook_name', 'your_function_name', [priority], [accepted_args]);
		// i.e. add_action('wp_head', array(&$this, 'action'));

		// only if we're public
		if ( get_option('blog_public') ) {
			remove_action('do_robots', 'do_robots');
			add_action('do_robots', array(&$this, 'do_robots'));
		}// end if

		//add quick links to plugins page
		$plugin = plugin_basename(__FILE__);
		if ( is_admin() )
			add_filter("plugin_action_links_$plugin", array(&$this, 'settings_link'));

	}// end function

	function activate() {
		// stuff to do when the plugin is activated
	}// end function
	
	function deactivate() {
		// stuff to do when plugin is deactivated
		// i.e. delete_option('pc_robotstxt');
		$options = $this->get_options();
		if ( $options['remove_settings'] )
			delete_option('pc_robotstxt');
	}// end function
	
	function settings_link($links) {
		$settings_link = '<a href="options-general.php?page=pc-robotstxt/admin.php">Settings</a>';
		array_unshift($links,$settings_link);
		return $links;
	}// end function
	
	function do_robots() {
		
		if ( is_robots() ) {
			
			$options = $this->get_options();

			$output = "# This virtual robots.txt file was created by the PC Robots.txt WordPress plugin.\n";
			$output .= "# For more info visit: http://petercoughlin.com/robotstxt-wordpress-plugin/\n\n";

			if ( '' != $options['user_agents'] )
				$output .= stripslashes($options['user_agents']);

			// if there's an existing sitemap file or we're using pc-xml-sitemap plugin add a reference..
			if ( file_exists($_SERVER['DOCUMENT_ROOT'].'/sitemap.xml.gz') )
				$output .= "\n\n".'Sitemap: http://'.$_SERVER['HTTP_HOST'].'/sitemap.xml.gz';
			elseif ( class_exists('pc_xml_sitemap') || file_exists($_SERVER['DOCUMENT_ROOT'].'/sitemap.xml') )
				$output .= "\n\n".'Sitemap: http://'.$_SERVER['HTTP_HOST'].'/sitemap.xml';

			header('Status: 200 OK', true, 200);
			header('Content-type: text/plain; charset='.get_bloginfo('charset'));
			echo $output;
			exit;

		}// end if
		
	}// end function
	
	function get_options() {
		$options = get_option('pc_robotstxt');
		if ( !is_array($options) )
			$options = $this->set_defaults();
		return $options;
	}// end function
	
	function set_defaults() {
		$options = array(
			'user_agents' => "User-agent: Alexibot\nDisallow: /\n\n"
				."User-agent: Aqua_Products\nDisallow: /\n\n"
				."User-agent: asterias\nDisallow: /\n\n"
				."User-agent: b2w/0.1\nDisallow: /\n\n"
				."User-agent: BackDoorBot/1.0\nDisallow: /\n\n"
				."User-agent: BlowFish/1.0\nDisallow: /\n\n"
				."User-agent: Bookmark search tool\nDisallow: /\n\n"
				."User-agent: BotALot\nDisallow: /\n\n"
				."User-agent: BotRightHere\nDisallow: /\n\n"
				."User-agent: BuiltBotTough\nDisallow: /\n\n"
				."User-agent: Bullseye/1.0\nDisallow: /\n\n"
				."User-agent: BunnySlippers\nDisallow: /\n\n"
				."User-agent: CheeseBot\nDisallow: /\n\n"
				."User-agent: CherryPicker\nDisallow: /\n\n"
				."User-agent: CherryPickerElite/1.0\nDisallow: /\n\n"
				."User-agent: CherryPickerSE/1.0\nDisallow: /\n\n"
				."User-agent: Copernic\nDisallow: /\n\n"
				."User-agent: CopyRightCheck\nDisallow: /\n\n"
				."User-agent: cosmos\nDisallow: /\n\n"
				."User-agent: Crescent Internet ToolPak HTTP OLE Control v.1.0\nDisallow: /\n\n"
				."User-agent: Crescent\nDisallow: /\n\n"
				."User-agent: DittoSpyder\nDisallow: /\n\n"
				."User-agent: EmailCollector\nDisallow: /\n\n"
				."User-agent: EmailSiphon\nDisallow: /\n\n"
				."User-agent: EmailWolf\nDisallow: /\n\n"
				."User-agent: EroCrawler\nDisallow: /\n\n"
				."User-agent: ExtractorPro\nDisallow: /\n\n"
				."User-agent: FairAd Client\nDisallow: /\n\n"
				."User-agent: Flaming AttackBot\nDisallow: /\n\n"
				."User-agent: Foobot\nDisallow: /\n\n"
				."User-agent: Gaisbot\nDisallow: /\n\n"
				."User-agent: GetRight/4.2\nDisallow: /\n\n"
				."User-agent: Harvest/1.5\nDisallow: /\n\n"
				."User-agent: hloader\nDisallow: /\n\n"
				."User-agent: httplib\nDisallow: /\n\n"
				."User-agent: HTTrack 3.0\nDisallow: /\n\n"
				."User-agent: humanlinks\nDisallow: /\n\n"
				."User-agent: InfoNaviRobot\nDisallow: /\n\n"
				."User-agent: Iron33/1.0.2\nDisallow: /\n\n"
				."User-agent: JennyBot\nDisallow: /\n\n"
				."User-agent: Kenjin Spider\nDisallow: /\n\n"
				."User-agent: Keyword Density/0.9\nDisallow: /\n\n"
				."User-agent: larbin\nDisallow: /\n\n"
				."User-agent: LexiBot\nDisallow: /\n\n"
				."User-agent: libWeb/clsHTTP\nDisallow: /\n\n"
				."User-agent: LinkextractorPro\nDisallow: /\n\n"
				."User-agent: LinkScan/8.1a Unix\nDisallow: /\n\n"
				."User-agent: LinkWalker\nDisallow: /\n\n"
				."User-agent: LNSpiderguy\nDisallow: /\n\n"
				."User-agent: lwp-trivial/1.34\nDisallow: /\n\n"
				."User-agent: lwp-trivial\nDisallow: /\n\n"
				."User-agent: Mata Hari\nDisallow: /\n\n"
				."User-agent: Microsoft URL Control - 5.01.4511\nDisallow: /\n\n"
				."User-agent: Microsoft URL Control - 6.00.8169\nDisallow: /\n\n"
				."User-agent: Microsoft URL Control\nDisallow: /\n\n"
				."User-agent: MIIxpc/4.2\nDisallow: /\n\n"
				."User-agent: MIIxpc\nDisallow: /\n\n"
				."User-agent: Mister PiX\nDisallow: /\n\n"
				."User-agent: moget/2.1\nDisallow: /\n\n"
				."User-agent: moget\nDisallow: /\n\n"
				."User-agent: Mozilla/4.0 (compatible; BullsEye; Windows 95)\nDisallow: /\n\n"
				."User-agent: MSIECrawler\nDisallow: /\n\n"
				."User-agent: NetAnts\nDisallow: /\n\n"
				."User-agent: NICErsPRO\nDisallow: /\n\n"
				."User-agent: Offline Explorer\nDisallow: /\n\n"
				."User-agent: Openbot\nDisallow: /\n\n"
				."User-agent: Openfind data gatherer\nDisallow: /\n\n"
				."User-agent: Openfind\nDisallow: /\n\n"
				."User-agent: Oracle Ultra Search\nDisallow: /\n\n"
				."User-agent: PerMan\nDisallow: /\n\n"
				."User-agent: ProPowerBot/2.14\nDisallow: /\n\n"
				."User-agent: ProWebWalker\nDisallow: /\n\n"
				."User-agent: psbot\nDisallow: /\n\n"
				."User-agent: Python-urllib\nDisallow: /\n\n"
				."User-agent: QueryN Metasearch\nDisallow: /\n\n"
				."User-agent: Radiation Retriever 1.1\nDisallow: /\n\n"
				."User-agent: RepoMonkey Bait & Tackle/v1.01\nDisallow: /\n\n"
				."User-agent: RepoMonkey\nDisallow: /\n\n"
				."User-agent: RMA\nDisallow: /\n\n"
				."User-agent: searchpreview\nDisallow: /\n\n"
				."User-agent: SiteSnagger\nDisallow: /\n\n"
				."User-agent: SpankBot\nDisallow: /\n\n"
				."User-agent: spanner\nDisallow: /\n\n"
				."User-agent: suzuran\nDisallow: /\n\n"
				."User-agent: Szukacz/1.4\nDisallow: /\n\n"
				."User-agent: Teleport\nDisallow: /\n\n"
				."User-agent: TeleportPro\nDisallow: /\n\n"
				."User-agent: Telesoft\nDisallow: /\n\n"
				."User-agent: The Intraformant\nDisallow: /\n\n"
				."User-agent: TheNomad\nDisallow: /\n\n"
				."User-agent: TightTwatBot\nDisallow: /\n\n"
				."User-agent: toCrawl/UrlDispatcher\nDisallow: /\n\n"
				."User-agent: True_Robot/1.0\nDisallow: /\n\n"
				."User-agent: True_Robot\nDisallow: /\n\n"
				."User-agent: turingos\nDisallow: /\n\n"
				."User-agent: TurnitinBot/1.5\nDisallow: /\n\n"
				."User-agent: TurnitinBot\nDisallow: /\n\n"
				."User-agent: URL Control\nDisallow: /\n\n"
				."User-agent: URL_Spider_Pro\nDisallow: /\n\n"
				."User-agent: URLy Warning\nDisallow: /\n\n"
				."User-agent: VCI WebViewer VCI WebViewer Win32\nDisallow: /\n\n"
				."User-agent: VCI\nDisallow: /\n\n"
				."User-agent: Web Image Collector\nDisallow: /\n\n"
				."User-agent: WebAuto\nDisallow: /\n\n"
				."User-agent: WebBandit/3.50\nDisallow: /\n\n"
				."User-agent: WebBandit\nDisallow: /\n\n"
				."User-agent: WebCapture 2.0\nDisallow: /\n\n"
				."User-agent: WebCopier v.2.2\nDisallow: /\n\n"
				."User-agent: WebCopier v3.2a\nDisallow: /\n\n"
				."User-agent: WebCopier\nDisallow: /\n\n"
				."User-agent: WebEnhancer\nDisallow: /\n\n"
				."User-agent: WebSauger\nDisallow: /\n\n"
				."User-agent: Website Quester\nDisallow: /\n\n"
				."User-agent: Webster Pro\nDisallow: /\n\n"
				."User-agent: WebStripper\nDisallow: /\n\n"
				."User-agent: WebZip/4.0\nDisallow: /\n\n"
				."User-agent: WebZIP/4.21\nDisallow: /\n\n"
				."User-agent: WebZIP/5.0\nDisallow: /\n\n"
				."User-agent: WebZip\nDisallow: /\n\n"
				."User-agent: Wget/1.5.3\nDisallow: /\n\n"
				."User-agent: Wget/1.6\nDisallow: /\n\n"
				."User-agent: Wget\nDisallow: /\n\n"
				."User-agent: wget\nDisallow: /\n\n"
				."User-agent: WWW-Collector-E\nDisallow: /\n\n"
				."User-agent: Xenu's Link Sleuth 1.1c\nDisallow: /\n\n"
				."User-agent: Xenu's\nDisallow: /\n\n"
				."User-agent: Zeus 32297 Webster Pro V2.9 Win32\nDisallow: /\n\n"
				."User-agent: Zeus Link Scout\nDisallow: /\n\n"
				."User-agent: Zeus\nDisallow: /\n\n"
				."User-agent: Adsbot-Google\nDisallow:\n\n"
				."User-agent: Googlebot\nDisallow:\n\n"
				."User-agent: Mediapartners-Google\nDisallow:\n\n"		
				."User-agent: *\n"
				."Disallow: /cgi-bin/\n"
				."Disallow: /wp-admin/\n"
				."Disallow: /wp-includes/\n"
				."Disallow: /wp-content/plugins/\n"
				."Disallow: /wp-content/cache/\n"
				."Disallow: /wp-content/themes/\n"
				."Disallow: /wp-login.php\n"
				."Disallow: /wp-register.php",
			'remove_settings' => false
		);
		update_option('pc_robotstxt', $options);
		return $options;
	}// end function

}// end class
$pc_robotstxt = new pc_robotstxt;

if ( is_admin() )
	include_once dirname(__FILE__).'/admin.php';
