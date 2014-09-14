<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_ExecutableUploads extends gus_TestBase
{
	protected function main_check()
	{
		$test_string = 'tmp_' . md5(time());

		$upload_dir = wp_upload_dir();

        // Can this test be run?
        if( ! is_writable($upload_dir['basedir']) )
        {
            $this->undetermined();
            return;
        }

		// Save a test php file in uploads directory
		$test_path = $upload_dir['basedir'] . '/' . $test_string . '.php';
		$test_data = '<?php /* Created by the Gauntlet Security plugin as a test. You may safely delete me. TEST'.$test_string.' */ echo "HIYA"; ?>';
		file_put_contents($test_path, $test_data);
        
		/*
			Try accessing the file 
		*/
		$full_url = $upload_dir['baseurl'] . '/' . $test_string . '.php';
		$response = wp_remote_request( $full_url );

		if( is_array($response) && isset($response['response']['code']) )
		{
			if( $response['response']['code'] >= 400 )
			{
				$this->pass();
			}
			elseif( $response['body'] == "HIYA" )
			{
				$this->critical_fail();
			}
			elseif( $response['response']['code'] == 200 && strpos($response['body'], 'TEST'.$test_string) !== false)
			{
				$this->fail();
			}
			else
			{
				$this->undetermined();
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
			return "The uploads directory doesn't allow PHP code execution";
			break;
			
			case 'fail':
			return "Code can't be executed but PHP files are accessible from the uploads directory";
			break;
			
			case 'critical':
			return "The uploads directory allows code execution";
			break;
			
			case 'undetermined':
			default:
			return "Prevent code execution in the uploads directory";
			break;			
		}
	}
	
	protected function result_text()
	{
        $upload_dir = $this->strip_site_root( wp_upload_dir() );
        $html = <<<EOD

            Your uploads directory:
            <code>{$upload_dir['basedir']}</code>        

EOD;
        
		switch($this->pass)
		{
			case 'pass':
			return "The uploads directory doesn't allow PHP execution. {$html}";
			break;
			
			case 'fail':
			return "It might be possible for PHP files in the uploads directory to be downloaded as plain text. {$html}";
			break;
			
			case 'critical':
			return "The uploads directory allows PHP execution. {$html}";
			break;

            case 'undetermined':
            return "Could not complete test. It could be that WordPress does not have the proper permissions to write a test file to the uploads directory. Or, the front-end of the site is not accessible (maintenance mode or password-protected) {$html}";
            break;
		}
	}
	
	protected function why_important()
	{
		return <<<EOD
			
		The uploads directory is a prime target for hackers attempting to upload their own malicious files.
        Preventing script execution in the uploads directory is one way to protect against this attack.

EOD;
	}
	
	protected function how_to_fix()
	{
        $code1 = <<<EOD

Order deny,allow
Deny from all
<Files ~ ".(jpe?g|png|gif|mp3|wav|ogg|m4a|mp4|mov|wmv|avi|mpg|ogv|3gp|3g2|pdf|docx?|pptx?|ppsx?|odt|xlsx?|zip)$">
Allow from all
</Files>

EOD;
        $code1 = htmlentities(trim($code1));
        
		return <<<EOD
			
		Create an .htaccess file in your uploads directory:
		
        <code class='prettyprint'>{$code1}</code>
        
        This whitelists only file types that are typically uploaded to the media library. 
        If it is too restrictive, you can add other file extensions.
		
EOD;
	}
	
	protected function fix_difficulty()
	{
		return "Intermediate";
	}
}