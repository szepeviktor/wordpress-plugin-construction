<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_CommonPasswords extends gus_TestBase
{
	protected $test_table_show = true;
	protected $test_table_headers = true;
	protected $test_table_fail_only = true;

    private $other_bad_words = array();

	private $dictionary_count = 300;
	
	protected function main_check()
	{
		// Load dictionary
		@include( plugin_dir_path( __FILE__ ) . '../password-dictionary.php' );

		if( isset($password_dictionary) )
		{
			// normalize linebreaks
			$password_dictionary = str_replace("\n\r", "\n", trim($password_dictionary));
			
			// split string by line breaks and store in an array
			$password_dictionary = explode("\n", $password_dictionary);
			
			// only use the first x number of passwords
			$password_dictionary = array_slice($password_dictionary, 0, $this->dictionary_count);
            
            
            // Add usernames to dictionary
    		$users = get_users();
    	    foreach ($users as $u) 
    		{
                $this->other_bad_words[] = strtolower($u->user_login);
                $password_dictionary[] = strtolower($u->user_login);
                
                $display_name_arr = explode(' ', $u->display_name);
                foreach($display_name_arr as $n)
                {
                    $this->other_bad_words[] = strtolower($n);
                    $password_dictionary[] = strtolower($n);
                }
    	    }
            
            
    		// The WP domain name is also a potential password guess
            if($bad_domain = $this->get_sld(site_url()))
            {
                $this->other_bad_words[] = strtolower($bad_domain);
                $password_dictionary[] = strtolower($bad_domain);
            }

            sort($this->other_bad_words);
            $this->other_bad_words = array_unique($this->other_bad_words);

			/*
				This is a very slow test. Only check admin users.
			*/
			$users = get_users(array('role' => 'administrator'));
			
			foreach($users as $u)
			{
				$this->run_sub_test( array(
					'user' => $u,
					'password_dictionary' => $password_dictionary,
				) );
			}
		}
		else
		{
			$this->undetermined();
		}
	}
	
	protected function sub_test($args)
	{
		$user = $args['user'];
		$password_dictionary = $args['password_dictionary'];

		$sub_test = array(
			'pass' => 'undetermined',
			'table_columns' => array(
				'User name' => $user->user_login,
				'Display name' => $user->display_name,
			),
		);
		
		$sub_test['pass'] = 'pass';

		$start = microtime(true);
		foreach($password_dictionary as $p)
		{
			if( wp_check_password(trim($p), $user->user_pass, $user->ID) )
			{
				$sub_test['pass'] = 'critical';
				break;
			}
		}
		$duration = microtime(true) - $start;

		return $sub_test;
	}

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return 'All admin users have passwords stronger than the ' . $this->dictionary_count . ' most popular passwords';
			break;
			
			case 'fail':
			case 'critical':
			return 'At least one admin user has a very weak password';
			break;
			
			case 'undetermined':
			default:
			return "Do not use weak passwords";
			break;			
		}
	}
	
	protected function result_text()
	{
		switch($this->pass)
		{
			case 'pass':
			return 'All admin users have passwords stronger than the ' . $this->dictionary_count . ' most popular passwords';
			break;
			
			case 'fail':
			case 'critical':
            $other_bad_words = implode(', ', $this->other_bad_words);
            return <<<EOD
                
                The admin users listed below have very weak passwords. 
                The dictionary used for this test is made up of the {$this->dictionary_count}
                most used passwords on any site, plus a list of words based on this site's 
                usernames and domain name.
                These site-specific words are easy for hackers to discover and add to their brute force attempts: 
                <strong>{$other_bad_words}</strong>

EOD;
			break;
			
			case 'undetermined':
			default:
			return 'Test was not completed. The password dictionary could not be loaded.';		
			break;			
		}
	}

	protected function why_important()
	{
		return <<<EOD
			
		Having strong passwords is one of the most important things you can do to keep your site secure.
		This test checks admin user's passwords against the most popular {$this->dictionary_count}
		passwords. It also checks that no admin user is using an easy to guess password based on the site domain
        name or a username.
		
EOD;
	}
	
	protected function how_to_fix()
	{
		return <<<EOD
			
		Ways you can encourage or enforce strong passwords:
		<ul>
			<li>Recommend that all users select passwords that the WordPress password strength meter says is strong.</li>
			<li>Recommend that all users use a password wallet such as 1Password or Keepass.</li>
			<li>Use a plugin which enforces strong passwords.</li>
		</ul>		
			
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Intermediate';
	}
}