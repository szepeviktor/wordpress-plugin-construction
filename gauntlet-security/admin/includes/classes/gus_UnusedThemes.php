<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_UnusedThemes extends gus_TestBase
{
	protected $test_table_show = true;
	protected $test_table_headers = false;
	protected $test_table_fail_only = false;
    
    private $has_parent = false;
	
	protected function main_check()
	{
		$args = array();
		$all_themes = wp_get_themes( $args );
        
        // Get current theme and parent theme, if any...
        $current_theme = wp_get_theme();
        $parent_theme = $current_theme->parent();
        
        // Check if there's any other themes besides those two on the server
        foreach($all_themes as $t)
        {
    		$this->run_sub_test( array(
    			'theme' => $t,
                'current' => $current_theme,
                'parent' => $parent_theme,
    		) );
        }
	}

	protected function sub_test($args)
	{
		$theme = $args['theme'];
		$current = $args['current'];
		$parent = $args['parent'];
		
		$sub_test = array(
			'pass' => 'pass',
			'table_columns' => array(
				'Name' => '',
				'Status' => '',
			),
		);
        
		$sub_test['table_columns']['Name'] = $theme->get('Name');

        if($theme->get('Name') !== $current->get('Name'))
        {
            if( ! $parent || ($theme->get('Name') !== $parent->get('Name')) )
            {
        		$sub_test['pass'] = 'fail';
        		$sub_test['table_columns']['Status'] = '<span class="error">Inactive</span>';
            }
            else
            {
                $this->has_parent = true;
        		$sub_test['pass'] = 'pass';
        		$sub_test['table_columns']['Status'] = 'Active (Parent)';
            }
        }
        else
        {
    		$sub_test['pass'] = 'pass';
    		$sub_test['table_columns']['Status'] = 'Active';
        }
		
		
		return $sub_test;
	}
	
	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "There are no inactive themes still on the server";
			break;
			
			case 'fail':
			case 'critical':
			return 'There are inactive themes on the server';
			break;
			
			case 'undetermined':
			default:
			return "Remove unused themes from the server";
			break;			
		}
	}
	
	protected function result_text()
	{
        if( $this->pass == 'pass' )
        {
            if($this->has_parent)
            {
                return "You have just one parent and one child theme installed.";
            }
            else
            {
                return "You have just one theme installed.";
            }
        }
        if( $this->pass !== 'pass' && $this->pass !== 'undetermined')
        {
            return "You have inactive themes installed on the server.";
        }
	}
	
	protected function why_important()
	{
		return <<<EOD
		Even if themes are inactive, the files are still accessible. 
		Vulnerabilities in unused themes are just as dangerous as in active themes.
EOD;
	}
	
	protected function how_to_fix()
	{
		return <<<EOD
		You should delete all unneeded themes completely. 		
		To delete a theme, go to Appearance > Themes > Theme Details > Delete.
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Easy';
	}
	
}