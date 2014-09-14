<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_TestRunner
{
	public $results = array();
	public $tests = array();
	
	public function __construct()
	{
		require_once( plugin_dir_path( __FILE__ ) . 'gus_TestBase.php' );

		// File access
		$this->tests[] = array('gus_FilePermissions', 'Files');
		$this->tests[] = array('gus_DirectoryIndexing', 'Files');
		$this->tests[] = array('gus_ExecutableUploads', 'Files');
		$this->tests[] = array('gus_SecureIncludes', 'Files');
		$this->tests[] = array('gus_WpContentLocation', 'Files');
                                
		// PHP configuration    
		$this->tests[] = array('gus_PhpFunctions', 'PHP');
		$this->tests[] = array('gus_PhpAllowUrl', 'PHP');
                                
		// Database             
		$this->tests[] = array('gus_DbPassword', 'Database');
		$this->tests[] = array('gus_WpTable', 'Database');
                                
		// WordPress configuration
		$this->tests[] = array('gus_WpVersion', 'WordPress');
		$this->tests[] = array('gus_PhpDisplayErrors', 'WordPress');
		$this->tests[] = array('gus_FileEditing', 'WordPress');
		$this->tests[] = array('gus_KeysAndSalts', 'WordPress');
		$this->tests[] = array('gus_WpGenerator', 'WordPress');
		$this->tests[] = array('gus_AnyoneCanRegister', 'WordPress');
		$this->tests[] = array('gus_SslAdmin', 'WordPress');
	                            
		// Plugins / Themes     
		$this->tests[] = array('gus_PluginAudit', 'Plugins & Themes');
		$this->tests[] = array('gus_UnusedThemes', 'Plugins & Themes');
		$this->tests[] = array('gus_BmuhtMit', 'Plugins & Themes'); 
                                
		// Users                
		$this->tests[] = array('gus_AdminUsername', 'Users');
		$this->tests[] = array('gus_CommonPasswords', 'Users');
		$this->tests[] = array('gus_UserIdOne', 'Users');
		$this->tests[] = array('gus_AdminCount', 'Users');
		$this->tests[] = array('gus_NickNames', 'Users');
		$this->tests[] = array('gus_UserNames', 'Users');
	}
	
	private function check_test_name($name)
	{
		foreach($this->tests as $test)
		{
			if($test[0] == $name)
			{
				return true;
			}
		}
		return false;
	}
	
	public function get_test($test_name)
	{
        // We need to verify the test name since it could be user input        
		if( ! $this->check_test_name($test_name) )
			return false;

		require_once( plugin_dir_path( __FILE__ ) . $test_name . '.php' );
		return new $test_name;
	}
	
	public function show_unrun_tests()
	{
		foreach($this->tests as $t)
		{
			if($test = $this->get_test($t[0]))
			{
				$test->show();
				$this->results[$t[1]]['undetermined'][] = $test;
			}
		}
	}

    public function run($test_name)
    {
        if( $test = $this->get_test($test_name) )
        {
            // Prepare for the worst
    		if ( (int) @ini_get( 'memory_limit' ) < 256 ) 
            {
    			@ini_set( 'memory_limit', '256M' );
    		}
    		@set_time_limit( 90 );
            
            $test->run();
            return array(
                'test_id' => $test->test_id,
                'class' => $test->pass,
                'title' => $test->title(),
                'message' => $test->message,
            );
        }
        else
        {
            return array( 'test_id' => false );  // Error
        }
    }
}