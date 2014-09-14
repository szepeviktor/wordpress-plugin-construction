<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_SecureIncludes extends gus_TestBase
{
	protected function main_check()
	{
        $this->pass();
		$test_string = 'tmp_' . md5(time());

		$includes_dir = ABSPATH . 'wp-includes/';

        // Can this test be run?
        if( ! is_writable($includes_dir) )
        {
            $this->undetermined();
            return;
        }

		// Save a test php file in includes directory
		$test_path = $includes_dir . $test_string . '.php';
		$test_data = '<?php /* Created by the Gauntlet Security plugin as a test. You may safely delete me. */ echo strrev("echo_' . $test_string . '"); ?>';
        file_put_contents($test_path, $test_data);
		
		/*
			Try accessing the file 
		*/
        $full_url = includes_url() . $test_string . '.php';

        // if using a self-signed ssl cert
        $args = (is_ssl()) ? array('sslverify' => false) : array() ; 
        $response = wp_remote_request( $full_url, $args );
        
        if( is_array($response) && isset($response['response']['code']) )
        {
            if( $response['body'] == strrev('echo_' . $test_string) )
            {
                // PHP has been executed
                $this->critical_fail();
            }
            elseif( false !== strpos( $response['body'], 'echo_' . $test_string ) )
            {
                // File is accessible, but PHP not executed
                $this->fail();
            }
            else
            {
                $this->pass();
            }
        }
        else
        {
            $this->undetermined();
        }
        
        // Delete the test file
        if( is_file($test_path) )
        {
            unlink($test_path);
        }
	}

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "Files in the includes directory are blocked";
			break;
			
			case 'fail':
			case 'critical':
			return "Files in the includes directory are not blocked";
			break;
			
			case 'undetermined':
			default:
			return "Block files in the includes directory";
			break;			
		}
	}
	
	protected function result_text()
	{
        $includes_dir = $this->strip_site_root( ABSPATH . 'wp-includes/' );
        $html = <<<EOD

            Your includes directory:
            <code>{$includes_dir}</code>        
            
EOD;
        
		switch($this->pass)
		{
			case 'pass':
			return "Files in the includes directory are blocked. {$html}";
			break;
			
			case 'fail':
			return "It might be possible for PHP files in the includes directory to be downloaded as plain text. {$html}";
			break;
			
            case 'critical':
            return "Files in the includes directory are not blocked. {$html}";
            break;

            case 'undetermined':
            return "Could not complete test. It could be that WordPress does not have the proper permissions to write a test file to the includes directory. {$html}";
            break;
		}
        
        return $html;
	}
	
	protected function why_important()
	{
		return <<<EOD
		
        Almost all of the files in the includes directory are meant to be used by WordPress itself.
        If a hacker does manage to move a malicious file into your includes directory, it shouldn't 
        be directly executable.
        
EOD;
	}
	
	protected function how_to_fix()
	{
        $code1 = <<<EOD

<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^wp-admin/includes/ - [F,L]
RewriteRule !^wp-includes/ - [S=3]
RewriteRule ^wp-includes/[^/]+\.php$ - [F,L]
RewriteRule ^wp-includes/js/tinymce/langs/.+\.php - [F,L]
RewriteRule ^wp-includes/theme-compat/ - [F,L]
</IfModule>

EOD;
        $code1 = htmlentities(trim($code1));
        
		return <<<EOD
			
		Add this to the .htaccess file in your root web directory:
		
        <code class='prettyprint'>{$code1}</code>
        
EOD;
	}
	
	protected function fix_difficulty()
	{
		return "Intermediate";
	}
}