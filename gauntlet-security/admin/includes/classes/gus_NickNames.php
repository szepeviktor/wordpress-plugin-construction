<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_NickNames extends gus_TestBase
{
	protected $test_table_show = true;
	protected $test_table_headers = true;
	protected $test_table_fail_only = true;

	protected function main_check()
	{
		$users = get_users();

	    foreach ($users as $u) 
		{
			$this->run_sub_test( array(
				'user' => $u,
			) );
	    }
	}

	protected function sub_test($args)
	{
		$u = $args['user'];

		$sub_test = array(
			'pass' => 'pass',
			'table_columns' => array(
				'User name' => $u->user_login,
				'Display name' => $u->display_name,
			),
		);
		
		if( strtolower($u->user_login) == strtolower($u->display_name) )
		{
    		$sub_test['pass'] = 'fail';
			$sub_test['table_columns']['Display name'] = '<span class="error">' . $u->display_name . '</span>';
		}
		
		return $sub_test;
	}

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "All users have different login and display names";
			break;
			
			case 'fail':
			case 'critical':
			return "Not all users have different login and display names";
			break;
			
			case 'undetermined':
			default:
			return "Users should not display their login usernames publicly";
			break;			
		}
	}
	
	protected function result_text()
	{
		if( $this->pass == 'pass')
		{
			return "All users have different login and display names";
		}
	}
	
	protected function why_important()
	{
		return <<<EOD
		
        <p>For paranoid web masters only.
        If everyone used strong passwords, knowledge of a person's username would not be an issue.
        </p>
        
		<p>By default when a new user is created, their "display name" is the same as their login name.
		In that case, their published byline will include their login name. 
		If login names are easily accessible, a targeted brute force login attack can be pulled off a little easier.
        The hacker would not need to also guess the username.
        </p>

		<p>But even if each user has a different display name, there are still other areas of the site 
        which can give away their login name. 
		The next test explains how usernames can be found through default author archive permalinks.</p>
		
EOD;
	}
	
	protected function how_to_fix()
	{
		return <<<EOD
			
		Change a user's display name by by editing their "Display name publicly as" setting.

EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Easy';
	}

}