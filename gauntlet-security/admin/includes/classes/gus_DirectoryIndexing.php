<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_DirectoryIndexing extends gus_TestBase
{
	protected function main_check()
	{
        $base_plugin_dir = dirname(dirname(dirname(__FILE__))); //      plugin name / admin / includes / classes / __FILE__

        // if using a self-signed ssl cert
        $args = (is_ssl()) ? array('sslverify' => false) : array() ; 
		$response = wp_remote_request( plugins_url( '/', $base_plugin_dir ), $args );

		if( is_array($response) && isset($response['response']['code']) )
		{
			if( $response['response']['code'] >= 403 )
			{
				$this->pass();
			}
			else
			{
				$this->fail();
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
			return "Directory indexing is turned off";
			break;
			
			case 'fail':
			case 'critical':
			return "Directory indexing is turned on";
			break;
			
			case 'undetermined':
			default:
			return "Turn off directory indexing";
			break;			
		}
	}
	
	protected function result_text()
	{
		if( $this->pass != 'undetermined' )
		{
			return $this->title();
		}
	}
		
	protected function why_important()
	{
		return <<<EOD
			
		When directory indexing is left on, visitors can navigate to a directory on your site
		and (if there's no index file) view a listing of all the files inside. This information
		can be useful to hackers who are targetting your site and want to understand the structure
		and contents of the server. 
		
EOD;
	}
	
	protected function how_to_fix()
	{
		return <<<EOD
			
		To stop files from being listed in a specific directory, you can simply put an empty 
		index.php or index.html in that directory and it will be served by default. 
        But it's a lot of work to include an empty file in every single sub-directory.
		So to turn off directory indexing site-wide, add this to the .htaccess file in your web root:
		
        <code class='prettyprint'>Options -Indexes</code>
		
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Intermediate';
	}
	
}