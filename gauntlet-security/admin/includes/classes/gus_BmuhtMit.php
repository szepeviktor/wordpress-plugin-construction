<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_BmuhtMit extends gus_TestBase
{
	protected $test_table_show = true;
	protected $test_table_headers = true;
	protected $test_table_fail_only = true;

	private $nasty_name = '';
	private $latest_url = '';
	
	private $oldest_safe_version = '2.8.14';
	private $oldest_safe_version_date = 'June 2014';
	private $latest_vulnerability = 'June 2014';
	
	public function __construct()
	{
		$this->nasty_name = strrev('bmuhTmiT');
		$this->latest_url = strrev('php.bmuhtmit/knurt/nvs/moc.edocelgoog.bmuhtmit//:ptth');
		
		parent::__construct();		
	}
	
	protected function main_check()
	{
        // Default result
        $this->pass();

        $this->start_timer();

        // Only look in plugins and theme directories
		$theme_dirs = $this->get_dir_contents( get_theme_root() );
		$plugin_dirs = $this->get_dir_contents( WP_PLUGIN_DIR );
        $mu_plugin_dirs = $this->get_dir_contents( WP_CONTENT_DIR . '/mu-plugins');
        $directory_structure = array_merge($theme_dirs, $plugin_dirs, $mu_plugin_dirs);

        $timthumb_keywords = array(
            '/timthumb.php',
            '/thumb.php',
            '/pics.php',
            '/image.php',
            '/upload.php',
        );
        
        foreach( $directory_structure as $path ) 
        {
            $this->num_paths++;

            $file_part = strtolower(strrchr($path, '/'));
            if( in_array ( $file_part, $timthumb_keywords ) )
            {
                error_log("checking... " . $path);

                if( $file_handle = @fopen( $path, 'r' ) )
                {
                    $contents = @fread( $file_handle, 1250 ); // just the first few bytes
                    @fclose($file_handle);
                    
                    $this->run_sub_test( array(
                        'path' => $path,
                        'contents' => $contents,
                    ) );
                }
            }
        }
        
        $this->stop_timer();
	}
	
	protected function sub_test($args)
	{
		$sub_test = array(
			'pass' => 'pass',
			'table_columns' => array(
				'Path' => '',
				'Version' => '',
				'Threat' => '',
			),
		);
		
        if ( preg_match( "~http://code.google.com/p/" . strtolower($this->nasty_name) . "~", $contents ) ) 
        {
            // Found a copy of TimThumb - Red flag!
            $sub_test['pass'] = 'fail';
            $sub_test['table_columns']['Path'] = $this->strip_site_root( $path );
        
            /*
                Check version. If vulnerable, then Critical!
            */
            preg_match( "~define\s*\(.+?VERSION.+?\s*?,\s*?(.*?)\)~s", $contents, $matches );
            if( isset($matches[1]) )
            {
                $version = trim(str_replace(array("'", '"'), '', $matches[1]));
        
                if( version_compare($version, $this->oldest_safe_version, ">=") )
                {
                    // Should be safe
                    $sub_test['table_columns']['Version'] = $version;
                    $sub_test['table_columns']['Threat'] = "No known vulnerability";
                }
                else
                {
                    // Definitely not safe
                    $sub_test['pass'] = 'critical';
                    $sub_test['table_columns']['Version'] = "<span class='error'>{$version}</span>";
                    $sub_test['table_columns']['Threat'] = "<span class='error'>Known vulnerability</span>";
                }
        
            }
            else
            {
                // Could not figure out the version number, assume the worst
                $sub_test['pass'] = 'critical';
                $sub_test['table_columns']['Version'] = "<span class='error'>Undetermined</span>";
                $sub_test['table_columns']['Threat'] = "<span class='error'>Unknown</span>";
            }
        }
		
		return $sub_test;
	}
	
	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return 'A ' . $this->nasty_name . ' script was not found';
			break;
			
			case 'fail':
			return $this->nasty_name . ' was found somewhere in your content directory';
			break;
			
			case 'critical':
			return $this->nasty_name . ' was found somewhere in your content directory';
			break;
			
			case 'undetermined':
			default:
			return "Do not use " . $this->nasty_name;
			break;			
		}
	}
	
	protected function result_text()
	{
		if($this->pass == 'pass')
		{
			return 'A ' . $this->nasty_name . ' script was not found';
		}
		return '';
	}
		
	protected function why_important()
	{
		return <<<EOD

        <p>{$this->nasty_name} is a single file script which many older themes and plugins have used to make
        image resizing easy. Current versions of WordPress include built-in methods which make 
        {$this->nasty_name} pretty much redundent.</p>

        <p>{$this->nasty_name} is infamous for it's history of vulnerabilities. 
        The most recent vulnerability was discovered in {$this->latest_vulnerability}.</p>

EOD;
	}
	
	protected function how_to_fix()
	{
		return <<<EOD
		
        <p>
        The safest option is to remove or replace all plugins or themes which rely on {$this->nasty_name}.
        If it's not practical to do this right away, at least ensure that you do not have a vulnerable version installed.
        It's not as easy as updating a plugin but in some cases, all that's necessary to update 
        the script is to overwrite it with a newer version. 
        {$this->nasty_name} is just a single file.
        But as soon as that's done, make a plan to remove it completely.
		</p>
		
		<p>The latest version is available here: 
		<a href='{$this->latest_url}' target='_blank'>{$this->latest_url}</a></p>
		
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Advanced';
	}	
}