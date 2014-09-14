<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_AdminCount extends gus_TestBase
{
	private $found_users = array();
	
	protected $test_table_show = true;
	protected $test_table_headers = true;
	protected $test_table_fail_only = false;

	
	public function __construct()
	{
		$this->found_users = get_users(array('role' => 'administrator'));
		
		parent::__construct();
	}
	
	protected function main_check()
	{
		if( count($this->found_users) > 1 )
		{
			$result = 'fail';
		}
		else
		{
			$result = 'pass';
		}

        foreach($this->found_users as $u)
        {
			$this->run_sub_test( array(
				'username' => $u->user_login,
				'display_name' => $u->display_name,
                'pass' => $result,
			) );
        }
	}
		
	protected function sub_test($args)
	{
        $username = $args['username'];
        $display_name = $args['display_name'];
        $result = $args['pass'];
        
		$sub_test = array(
			'pass' => $result,
			'table_columns' => array(
				'Login Name' => $username,
				'Display Name' => $display_name,
			),
		);

		return $sub_test;
	}

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "There is only one admin user";
			break;
			
			case 'fail':
			case 'critical':
			return "There is more than one admin user";
			break;
			
			case 'undetermined':
			default:
			return "Minimize the number of admin users"; 
			break;			
		}
	}
	
	protected function result_text()
	{
		if( count($this->found_users) > 1 )
		{
			return "There are " . count($this->found_users) . " admin users.";
		}
		else
		{
			return "There is only one admin user.";
		}
	}
	
	protected function why_important()
	{
		return <<<EOD
			
		<p>Admin user accounts are especially valuable targets for hackers. 
        Limiting the number of admin users limits your exposure.</p>
	
		<p>Ideally a site would only require one admin. That user would be responsible for 
		managing plugins, users, and site settings. 
		Other users would be given just the capabilities that they need and nothing more.
        To really prevent leaking info, the administrator should only 
        use their account for admin-type tasks and log in with a user account with less
        capabilities to do things such as editing content. 
        </p>

		<p>
        Although keeping multiple admin accounts is not advised, sharing an account
        is even worse. 
        In cases where a developer manages a site for a client, it would make sense 
		for the developer and client to have separate admin accounts.</p>
		
EOD;
	}
	
	protected function how_to_fix()
	{
		return <<<EOD
			
		<p>Consider down-grading the capabilities of some admin users.</p>
		<p>Consult the codex for a complete description of what the various user roles can do:
		<a href='http://codex.wordpress.org/Roles_and_Capabilities' target='_blank'>codex.wordpress.org/Roles_and_Capabilities</a></p>

EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Easy';
	}

}
