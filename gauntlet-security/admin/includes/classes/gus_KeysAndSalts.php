<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_KeysAndSalts extends gus_TestBase
{
	private $found_keys = 0;
	private $found_salts = 0;
	
	protected function main_check()
	{
		if( defined('AUTH_KEY') && (AUTH_KEY !== 'put your unique phrase here' || AUTH_KEY !== '') )
		{
			$this->found_keys++;
		}
		if( defined('SECURE_AUTH_KEY') && (SECURE_AUTH_KEY !== 'put your unique phrase here' || SECURE_AUTH_KEY !== '') )
		{
			$this->found_keys++;
		}
		if( defined('LOGGED_IN_KEY') && (LOGGED_IN_KEY !== 'put your unique phrase here' || LOGGED_IN_KEY !== '') )
		{
			$this->found_keys++;
		}
		if( defined('NONCE_KEY') && (NONCE_KEY !== 'put your unique phrase here' || NONCE_KEY !== '') )
		{
			$this->found_keys++;
		}
		
		
		if( $this->found_keys == 4 )
		{
			$this->pass();
		}
		else
		{
			$this->critical_fail();
		}


		/*
			Just for information...
		*/
		if( defined('AUTH_SALT') && (AUTH_SALT !== 'put your unique phrase here' || AUTH_SALT !== '') )
		{
			$this->found_salts++;
		}
		if( defined('SECURE_AUTH_SALT') && (SECURE_AUTH_SALT !== 'put your unique phrase here' || SECURE_AUTH_SALT !== '') )
		{
			$this->found_salts++;
		}
		if( defined('LOGGED_IN_SALT') && (LOGGED_IN_SALT !== 'put your unique phrase here' || LOGGED_IN_SALT !== '') )
		{
			$this->found_salts++;
		}
		if( defined('NONCE_SALT') && (NONCE_SALT !== 'put your unique phrase here' || NONCE_SALT !== '') )
		{
			$this->found_salts++;
		}
		
	}

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "Security keys are set in the WP-Config file";
			break;
			
			case 'fail':
			case 'critical':
			return 'There are insufficient security keys set in the WP-Config file';
			break;
			
			case 'undetermined':
			default:
			return "Set security keys in WP-Config file";
			break;			
		}
	}
	
	protected function result_text()
	{
        /*
            Has keys and salts
        */
        if( $this->found_keys == 4 && $this->found_salts == 4 )
        {
            return <<<EOD
            
            You have the necessary security keys defined in the config file.
            
EOD;
        }
        
        /*
            Has keys but not salts
        */
        if( $this->found_keys == 4 && $this->found_salts < 4 )
        {
            return <<<EOD
            
            You have the necessary security keys but don't have the salts defined in the config file.
            This is not a security issue since if they're not defined in the config file WordPress will
            create them for you and store them in the database.
            
EOD;
        }

        if( $this->pass == 'fail' || $this->pass == 'critical' )
        {
            return <<<EOD
            
            There are insufficient security keys set in the WP-Config file.   
                
EOD;
        }
        
	}
	
	protected function why_important()
	{
		return <<<EOD
			
		Since WordPress version 2.7, the wp-config.php file has included four keys which help
		protect the browsing session of a logged in user.
	
		Without these keys, it is easier for an attacker to hijack a logged in user's session
		and access the control panel.
			
EOD;
	}
	
	protected function how_to_fix()
	{
		return <<<EOD
			
		<p>You can get a fresh, randomly generated set of keys and salts from:
		<a target='_blank' href='https://api.wordpress.org/secret-key/1.1/salt/'>https://api.wordpress.org/secret-key/1.1/salt/</a>
		Copy and paste all of that into your wp-config.php file.</p>
		
		<p>After this change, logged in users will be forced to log back in again. 
		Other than that, there's no side-effects.</p>
		
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Easy';
	}	
	
}