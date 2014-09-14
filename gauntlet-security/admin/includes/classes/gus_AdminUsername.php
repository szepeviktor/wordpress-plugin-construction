<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_AdminUsername extends gus_TestBase
{
	protected $test_table_show = true;
	protected $test_table_headers = true;
	protected $test_table_fail_only = true;

	private $bad_names = array(
		'admin',
		'administrator',
		'test',
        'support',
		'adm',
	);
	private $bad_domain = '';

	public function __construct()
	{
		// The WP domain name is also a target
		$this->bad_domain = $this->get_sld(site_url());

		// Add it to bad names list
        if($this->bad_domain)
        {
    		array_push($this->bad_names, $this->bad_domain);
        }
		
		parent::__construct();
	}
	
	protected function main_check()
	{
		$users = get_users();
	    foreach ($users as $u) 
		{
			$this->run_sub_test( array(
				'user' => $u,
				'wordlist' => $this->bad_names,
			) );
	    }
	}
	
	protected function sub_test($args)
	{
		$user = $args['user'];
		$wordlist = $args['wordlist'];
		
		$sub_test = array(
			'pass' => 'pass',
			'table_columns' => array(
				'Name' => $user->user_login,
				'Display name' => $user->display_name,
			),
		);
		
		if( in_array(strtolower($user->user_login), $wordlist) )
		{
			if( strtolower($user->user_login) == "admin" )
			{
				$sub_test['pass'] = 'critical';
			}
			else
			{
				$sub_test['pass'] = 'fail';
			}
		}

		return $sub_test;
	}

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return 'There are no users with common login names such as "admin"';
			break;
			
			case 'fail':
			return 'There is at least one user with a common login name';
			break;
			
			case 'critical':
			return 'There is a user with the name "admin"';
			break;
			
			case 'undetermined':
			default:
			return "Do not use common user names (such as 'admin')";
			break;			
		}
	}
	
	protected function result_text()
	{
		if($this->pass == 'pass')
		{
			return 'There are no admin users with common login names such as "admin"';
		}
	}
	
	protected function why_important()
	{
		// Remove "admin" and bad domain from bad names list, just for display
		$other_names = array_diff($this->bad_names, array($this->bad_domain, 'admin'));
		$common = implode(', ', $other_names);
		
		$sld_warning = ($this->bad_domain) ? "(Avoid the username: <strong>{$this->bad_domain}</strong>.)" : '';
			
		return <<<EOD
		
		<p>Hackers will often use automated tools to guess passwords by brute force for commonly used usernames. 
		The most common username by far is 'admin' because it used to be the default user when creating 
		a new WordPress site. 
		Other common usernames you should avoid are: <strong>{$common}</strong>.</p>

		<p>You should also avoid using your domain name as a username. {$sld_warning}</p>
		
EOD;
	}
	
	protected function how_to_fix()
	{
		return <<<EOD
			
		<p>If you need to change the name of an existing user, here is perhaps the safest method:</p>	
		
		<ol>
		<li>Log in with an Administrator user account.</li>
		<li>Create a new Administrator user account with a harder to guess username. 
			You will need to use a different email address but you can change it back later.</li>
		<li>Log out of WordPress and log in as the new user.</li>
		<li>Delete the old user.</li>
		<li>You will be asked what to do with that user's posts and comments. 
			Assign all posts and comments to the user you just created.</li>
		<li>If you wish, you can edit the new admin user's email address and 
            profile info to match the old user.</li>
		</ol>
			
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Intermediate';
	}
}