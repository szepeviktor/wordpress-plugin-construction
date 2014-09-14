<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_PhpAllowUrl extends gus_TestBase
{
	protected $test_table_show = true;
	protected $test_table_headers = true;
	protected $test_table_fail_only = false;

	protected function main_check()
	{		
        $this->run_sub_test( array(
            'dangerous_option' => 'cURL extension',
            'threat_level' => 'medium',
        ) );
        $this->run_sub_test( array(
            'dangerous_option' => 'allow_url_include',
            'threat_level' => 'critical',
        ) );
		$this->run_sub_test( array(
			'dangerous_option' => 'allow_url_fopen',
			'threat_level' => 'medium',
		) );
	}

	protected function sub_test( $args )
	{
		$dangerous_option = $args['dangerous_option'];
		$threat_level = $args['threat_level'];
		
		$sub_test = array(
			'pass' => 'undetermined',
			'table_columns' => array(
				'Recommended' => 'Off',
				'Current' => '',
				'Description' => $dangerous_option,
			),
		);
		
        switch( $dangerous_option )
        {
            case 'cURL extension':
                $sub_test['table_columns']['Recommended'] = 'On';
                if ( ! function_exists( 'curl_init' ) || ! function_exists( 'curl_exec' ) )
                {
        			$sub_test['table_columns']['Current'] = '<span class="error">Off</span>';
    				$sub_test['pass'] = 'fail';
                }
                else
                {
        			$sub_test['table_columns']['Current'] = 'On';
    				$sub_test['pass'] = 'pass';
                }           
            break;
            default:
        		if( ini_get($dangerous_option) ) 
        		{
        			$sub_test['table_columns']['Current'] = '<span class="error">On</span>';
        			if( $threat_level == 'critical')
        			{
        				$sub_test['pass'] = 'critical';
        			}
        			else
        			{
        				$sub_test['pass'] = 'fail';
        			}
        		}
        		else
        		{
        			$sub_test['table_columns']['Current'] = 'Off';
        			$sub_test['pass'] = 'pass';
        		}
            break;            
        }

		return $sub_test;
	}

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "PHP can safely access remote files";
			break;
			
			case 'fail':
			case 'critical':
			return 'PHP can include or access remote files using "URL include wrappers"';
			break;
			
			case 'undetermined':
			default:
			return "Disable allow_url_include and allow_url_fopen PHP flags";
			break;			
		}
	}
	
	protected function result_text()
	{
		return '';
	}
	
	protected function why_important()
	{
		return <<<EOD
            
            <p>There are two primary ways WordPress can request files from other domains:
            cURL and filesystem functions enabled with URL wrappers. 
            
            Two PHP flags control whether or not URL wrappers are enabled. 

            If allow_url_include is ON, it is possible to directly execute PHP loaded from 
			remote files with common functions like include().
			If allow_url_fopen is ON, remote files can be loaded into PHP using functions
			such as file_get_contents(). The files can't be directly executed but you need to trust  
			the developer to responsibly validate or filter content received this way.</p>

            <p>Plugins and themes which rely on either of these two "allow_url" flags being enabled <em>could</em> be a risk.
    		Disabling them is a direct way to remove that risk.
    		WordPress offers safer <a href="http://codex.wordpress.org/HTTP_API" target="_blank">built-in methods</a> of 
    		retrieving content from remote URLs and plugins and themes should be using these.
    		</p>
			
EOD;
	}
	
	protected function how_to_fix()
	{
		$code = <<<EOD

allow_url_include = Off
allow_url_fopen = Off

EOD;
		$code = trim($code);
	
    	return <<<EOD
            
        <p>First, check that the PHP curl extension is enabled. 
            When the "allow_url" flags are turned off, WordPress will only be able to use curl functions for
            remote file access.</p>    

		<p>You may or may not have the ability to edit these flags depending on your server set-up. 
            They can't be set from an htaccess file.</p>

		<p>If you have access to your Apache virtual host config file (ie: httpd.conf) or php.ini file, add: </p>
	
        <code class='prettyprint'>{$code}</code>
		
		<p>If you don't have access to either of those, try adding that code to a php.ini in the root directory of your site. 
		If that doesn't work, contact your host and ask them if they can help you. 
        </p>
		
		<p>Be prepared to test your site thoroughly after changing these flags. If something breaks, check the server error log 
		for "allow_url_include" and "allow_url_fopen". That should give you a clue as to which plugins or themes are dependent on
		them.</p>

EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Advanced';
	}
	
}