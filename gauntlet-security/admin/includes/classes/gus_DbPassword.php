<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_DbPassword extends gus_TestBase
{
	protected function main_check()
	{
		if( defined('DB_PASSWORD') )
		{
			if(strlen(DB_PASSWORD) < 6)
			{
				$this->critical_fail();
			}
			elseif(strlen(DB_PASSWORD) < 8)
			{
				$this->fail();
			}
			else
			{
				$this->pass();
			}
		}
		else
		{
			// Is this condition even possible?
		}
	}
	
	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "The database password is at least eight characters long";
			break;
			
			case 'fail':
			case 'critical':
			return "The database password is less than eight characters long";
			break;
			
			case 'undetermined':
			default:
			return "Use a strong database password";
			break;			
		}
	}
	
	protected function result_text()
	{
		if( $this->pass !== 'pass' && $this->pass !== 'undetermined' )
		{
			return 'The database password is only ' . strlen(DB_PASSWORD) . ' characters long.';
		}
		elseif( $this->pass == 'pass' )
		{
			return $this->title();
		}
	}
		
	protected function why_important()
	{
		return <<<EOD

		Strong passwords are always important.
		This test only checks password length. 
		You should also be sure the password doesn't include any dictionary words
		and has a selection of uppercase, lowercase characters, numbers, and special characters.

EOD;
	}
	
	protected function how_to_fix()
	{
		return <<<EOD

		To change the password with zero downtime, create a new MySQL user with a strong password. 
		If there are multiple databases on this server, ideally the new user will only have access 
		to this one WordPress database.
		Edit the wp-config.php file with the new user settings.
		If the old MySQL user isn't being used for anything you should delete it.

EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Intermediate';
	}
	
}