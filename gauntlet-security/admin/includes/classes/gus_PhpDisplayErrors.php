<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_PhpDisplayErrors extends gus_TestBase
{
	protected function main_check()
	{
        if( ! defined( 'WP_DEBUG' ) || WP_DEBUG == false )
		{
			$this->pass();
		}
		elseif( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY == false )
        {
			$this->pass();
        }
        else
		{
			$this->critical_fail();
		}
	}

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "PHP errors are not being displayed to the user";
			break;
			
			case 'fail':
			case 'critical':
			return 'PHP errors are being displayed to the user';
			break;
			
			case 'undetermined':
			default:
			return "Turn off the display of PHP errors";
			break;			
		}
	}
	
	protected function result_text()
	{
		switch($this->pass)
		{
			case 'pass':
			return "PHP errors are not being displayed to the user";
			break;
			
			case 'fail':
			case 'critical':
			return 'PHP errors are being displayed to the user';
			break;
		}
	}
	
	protected function why_important()
	{
		return <<<EOD
			
		When errors are displayed on the public site, it not only makes your site look bad, it 
		can reveal the structure of the files on the server and potential opportunities for attack.
		
EOD;
	}
	
	protected function how_to_fix()
	{
        $code2 = <<<EOD

define( 'WP_DEBUG', true );           // turn on debugging
define( 'WP_DEBUG_LOG', true );       // save debug info to a file
define( 'WP_DEBUG_DISPLAY', false );  // do not display debug info in the HTML

EOD;
        $code2 = trim($code2);
        
        $content_dir = $this->strip_site_root( WP_CONTENT_DIR );
        
		return <<<EOD

        If you are not doing any debugging on a public site then add this configuration option 
        in wp-config.php:
	
        <code class='prettyprint'>define( 'WP_DEBUG', false );</code>

        If you are temporarily debugging a production site, then use this combination of settings:
        
        <code class='prettyprint'>{$code2}</code>

        The debugging info will be saved to a log file here: <br>
        <code>{$content_dir}/debug.log</code>
        
        When you're done, delete that log file since it's easily accessible.
        
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Easy';
	}	
}