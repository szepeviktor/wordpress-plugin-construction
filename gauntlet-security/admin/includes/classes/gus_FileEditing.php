<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_FileEditing extends gus_TestBase
{
	protected function main_check()
	{
		if( defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT )
		{
			$this->pass();
		}
		else
		{
			$this->fail();
		}
	}
	
	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "Theme and plugin files cannot be edited from the control panel";
			break;
			
			case 'fail':
			case 'critical':
			return 'Theme and plugin files can be edited from the control panel';
			break;
			
			case 'undetermined':
			default:
			return "Turn off file editing in the control panel";
			break;			
		}
	}
	
	protected function result_text()
	{
		switch($this->pass)
		{
			case 'pass':
			return "Theme and plugin files cannot be edited from the control panel";
			break;
			
			case 'fail':
			case 'critical':
			return 'Theme and plugin files can be edited from the control panel';
			break;
		}
	}
	
	protected function why_important()
	{
		return <<<EOD
            
        <p>By default, admins can edit theme and plugin files through the control panel.
           Turning off this ability is as easy 
           as changing a single config setting and will prevent hackers who manage to 
           login as admins from editing plugins and themes this way.</p> 
        
        <p>Turning this off may help deter lazier hackers, but really, 
            if a hacker can login to your control panel, they can also 
            install plugins giving them complete access to your files and database.
            It's still worth doing though even if it's just to prevent other admins (who may 
            know just enough to be dangerous) from tinkering.</p>


            
EOD;
	}
	
	protected function how_to_fix()
	{
        $code1 = <<<EOD

define( 'DISALLOW_FILE_EDIT', true );

EOD;
        $code1 = trim($code1);
    
        $code2 = <<<EOD

function my_prevent_plugin_install( \$allcaps, \$cap, \$args ) {
    \$allcaps['install_plugins'] = false;
    return \$allcaps;
}
add_filter( 'user_has_cap', 'my_prevent_plugin_install', 0, 3 );

EOD;
        $code2 = trim($code2);
    
		return <<<EOD
            
        To turn off file editing capability, add this to your wp-config.php file:
        
        <code class='prettyprint'>{$code1}</code>
        
        To go a step further and turn off the ability to install new plugins 
        (but still allow current plugins to be updated), add this to your functions.php file:
        
        <code class='prettyprint'>{$code2}</code>

        
            
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Easy';
	}	
	
}