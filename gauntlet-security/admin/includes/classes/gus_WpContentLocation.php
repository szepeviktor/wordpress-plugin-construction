<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_WpContentLocation extends gus_TestBase
{
    private $wp_content_exists = false;

	protected function main_check()
	{
        $default_dir = ABSPATH . 'wp-content';

        /*
            Has content dir been moved or renamed?
        */
        if( trim($default_dir, '/') !== trim(WP_CONTENT_DIR, '/') )
        {
			$this->pass();
        }
		else
		{
			$this->fail();
		}
        
        
        /*
            If the directory HAS been moved, a plugin may have recreated it anyway.
            Look for the presence of the default wp-content directory
        */
        if( $this->pass == 'pass' && file_exists( $default_dir ) )
        {
            $this->wp_content_exists = true;
            $this->fail();
        }        
	}

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "The content directory has been renamed or moved";
			break;
			
			case 'fail':
			case 'critical':
			return "The content directory is in it's default location";
			break;
			
			case 'undetermined':
			default:
			return "Rename or move the content directory";
			break;			
		}
	}
	
	protected function result_text()
	{
        $location = $this->strip_site_root(WP_CONTENT_DIR);
        $default_location = $this->strip_site_root(ABSPATH . 'wp-content');
        
        if( $this->pass == 'pass' )
        {
            return "The content directory is not in it's default location. New location: <code>{$location}</code>";
        }
        elseif( $this->wp_content_exists )
        {
            return <<<EOD

            <p>The content directory has been moved
                <strong>but there is still a wp-content directory located in the default location.</strong>            
                This could be the result of a plugin naively assuming that the directory hasn't been 
                moved and placing it's own files in the default location.</p>

            Actual content directory: <code>{$location}</code>
            Default location: <code>{$default_location}</code>
            
EOD;
        }
        elseif( $this->pass !== 'undetermined' )
        {
            return "You haven't moved or renamed the wp-content directory. Default location: <code>{$location}</code>";
        }
	}
		
	protected function why_important()
	{
		return <<<EOD
            
        It's impossible to truly hide this directory. All theme assets, uploaded images, and plugin files are located here and it's
        trivial to "view source" of any web page to see the true path to this directory. But by moving or renaming
        it, it's harder for automated scanners to find exploitable plugins. 

EOD;
	}
	
	protected function how_to_fix()
	{
        $site_url = site_url();

        // Tweak location of wp-content dir depending on wp-config location
		$new_dir = '';
		if ( ! file_exists( ABSPATH . 'wp-config.php') &&  
               file_exists( dirname(ABSPATH) . '/wp-config.php' ) && 
             ! file_exists( dirname(ABSPATH) . '/wp-settings.php' ) ) 
		{
			$new_dir = '/' . basename(ABSPATH);
		}
        
		return <<<EOD
            
        <p>This is something best done soon after installing WordPress. 
        If you've already had the site live for a while, there will probably be 
        lot of references to the original content directory in the database. 
        After changing the name of the content directory, you will need to go through
        the database or at least each Post and Page and change those URLs to reference
        the new content location.</p>

        <p>If you're working on a fresh WordPress install, 
        the simplest way to change the default content directory is to just rename it. 
        For example, you could rename the folder "wp-content" to "assets" and then 
        add this to your wp-config.php file:</p>
    
        <code class='prettyprint'>define( 'WP_CONTENT_DIR', dirname(__FILE__) . '{$new_dir}/assets' );
define( 'WP_CONTENT_URL', '{$site_url}/assets' );</code>

        <p>
        If your site has been live for a while, check your site for broken media library links. 
        It's also possible that some naively coded plugins or themes will be hard-coding the
        default wp-content path. 
        So after making this change thoroughly test your site.</p>

        <p>While we're obscuring directories, we can use the same technique to change the name of 
           the plugins directory. After renaming the "plugins" folder (in your content directory) to
           "add-ons", add this just below the code above:</p>
        
        <code class='prettyprint'>define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/add-ons' );
define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/add-ons' );</code>
            
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Advanced';
	}
	
}