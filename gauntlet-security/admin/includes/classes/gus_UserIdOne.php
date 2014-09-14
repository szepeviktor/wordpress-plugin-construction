<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_UserIdOne extends gus_TestBase
{
	protected function main_check()
	{
		if(array_reduce( get_users(), array( $this, 'is_one_callback' ) ))
		{
			$this->fail();
		}
		else
		{
			$this->pass();
		}
	}
    
    private function is_one_callback($carry, $user)
    {
		return $user->ID == 1;
    }

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "There is no user with ID = 1";
			break;
			
			case 'fail':
			case 'critical':
			return "There is a user with ID = 1";
			break;
			
			case 'undetermined':
			default:
			return "Do not have a user with an ID = 1";
			break;			
		}
	}
	
	protected function result_text()
	{
		if( $this->pass != 'undetermined' )
		{
			return $this->title();
		}
	}
	
	protected function why_important()
	{
		return <<<EOD
		
		<p>There is a history of hackers using scripts which blindly attempt
    		to edit the database record for user ID 1. 
    		When WordPress is installed the first user created is an administrator
            and this user id is always 1.
    		</p>
		
EOD;
	}
	
	protected function how_to_fix()
	{
		return <<<EOD
			
		<p>If you need to change the ID of an existing user, you can create a new user and then move the old user's
		data and profile info over to the new one. You won't have control over what user ID will be selected but that's ok. 
		Here's how:</p>	
		
		<ol>
		<li>Log in with an Administrator user account.</li>
		<li>Create a new user account. Make sure the user role is the same as the account you are changing.
			You will need to use a different email address but you can change it later.</li>
		<li>If the new user is an administrator, log out of WordPress and log in as the new user.</li>
		<li>Delete the old user.</li>
		<li>You will be asked what to do with that user's posts and comments. 
			Assign all posts and comments to the user you just created.</li>
		<li>Edit the new user's email address and profile info to match the old one.</li>
		</ol>
			
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Intermediate';
	}

}