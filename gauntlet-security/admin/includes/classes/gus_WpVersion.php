<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_WpVersion extends gus_TestBase
{
	private $installed_version;
	
	public function __construct()
	{
		$this->installed_version = get_bloginfo('version');
		
		parent::__construct();
	}
	
	protected function main_check()
	{
		$args = array();
		$response = wp_remote_request( 'http://api.wordpress.org/core/version-check/1.7/', $args );

		if( ! is_object($response) && ( $response['response']['code'] == 200 ) && ( isset($response['body'])) )
		{
			$json = json_decode($response['body']);
		}
		
		if( is_object($json) )
		{
			$version_current = $json->offers[0]->version;

			if( version_compare($version_current, $this->installed_version, "<=") )
			{
				$this->pass();
			}
			else
			{
				$this->critical_fail();
			}
		}
		else
		{
			$this->undetermined();
		}
	}

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "WordPress is up-to-date";
			break;
			
			case 'fail':
			case 'critical':
			return 'WordPress is not up-to-date';
			break;
			
			case 'undetermined':
			default:
			return "Keep WordPress up-to-date";
			break;			
		}
	}
	
	protected function result_text()
	{
		if( $this->pass !== 'undetermined' )
		{
			return "You are using version " . $this->installed_version . ".";
		}
	}
	
	protected function why_important()
	{
		return <<<EOD
            
        When there's a new vulnerability identified in the WordPress core, 
        it doesn't take long for it to be exploited by hackers. Not every WordPress
        update includes security patches but it happens often enough that keeping
        it up to date is essential.       
        
EOD;
	}
	
	protected function how_to_fix()
	{
        $update_url = admin_url() . 'update-core.php';
        
		return <<<EOD
            
        <p>Go to the <a target='_blank' href='{$update_url}'>WordPress Updates</a> page and click "Update Now".</p>
        
        <p>
        If your theme and plugins are well-coded and under active development, there are rarely issues.
        But if you want to be absolutely sure that updating WordPress won't somehow break your site, 
        make a back-up of your site and test the update on a test server first. 
        </p>
        
        <p>
        It's also recommended to have auto-updates working and enabled. 
        The best way to check that they are is to use the 
        <a href='http://wordpress.org/plugins/background-update-tester/' target='_blank'>Background Update Tester</a>
        plugin.
        You just need to run it once and then you can delete it.
        </p>
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Easy';
	}
	

}