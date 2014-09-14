<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_AnyoneCanRegister extends gus_TestBase
{
	protected function main_check()
	{
		if( ! intval(get_option('users_can_register')) )
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
			return "Self-registration is turned off";
			break;
			
			case 'fail':
			case 'critical':
			return 'Anyone can register';
			break;
			
			case 'undetermined':
			default:
			return "Turn off self-registration";
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
		
		<p>When self-registration is enabled, a "Register" link is available on the login screen. 
			Anyone can use that link to create a new "subscriber" account on your site.</p>
		
		<p>Giving strangers subscriber access to your blog shouldn't be a security risk, but 
			unless you are managing a membership site, there's probably no need to allow self-registration.
			In the worst case scenario, a plugin or theme vulnerability that allows privilege escalation could
			give a newly created subscriber administrator access.</p>

EOD;
	}
	
	protected function how_to_fix()
	{
		$settings_link = admin_url() . 'options-general.php';
		return <<<EOD
			
		<p>Under 
		<a href='{$settings_link}' target='_blank'>Settings > General</a>, uncheck the option: "Anyone can register".</p>
		
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Easy';
	}
	
}