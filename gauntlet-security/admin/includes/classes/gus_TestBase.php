<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_TestBase
{
	public $test_id = '';

	public $pass = 'notrun'; // 'notrun' | 'undetermined' | 'pass' | 'fail' | 'critical'

	public $message = '';
	
	public $sub_tests = array();
	
	protected $test_table_show = false;
	protected $test_table_headers = false;
	protected $test_table_fail_only = false;
	
    protected $debug = array();
    protected $num_paths = 0;
	

	public function __construct()
	{
		$this->test_id = get_class($this);
	}
	
	public function show()
	{
		$this->format_result_info();
	}
		
	public function run()
	{
        try
        {
            $this->main_check();
            $this->format_result_info();
        }
        catch(Exception $e)
        {
            error_log('Error: ' . $this->test_id);
            $this->format_result_info();
        }
	}
	
	
	public function run_sub_test( $args )
	{
		$sub_test = $this->sub_test( $args );
		$this->sub_tests[] = $sub_test;
		
		// Update the main test's pass fail/status		
		switch($sub_test['pass'])
		{
			case 'pass':
				if($this->pass == 'notrun')
				{
					$this->pass();
				}
			break;

			case 'fail':
				if($this->pass !== 'critical')
				{
					$this->fail();
				}
			break;

			case 'critical':
				$this->critical_fail();
			break;

			case 'undetermined':
				$this->undetermined();
			break;
		}
	}

		
	protected function result_text(){}
	protected function why_important(){}
	protected function how_to_fix(){}
	protected function fix_difficulty(){}
	
	
	protected function pass()
	{
		$this->pass = 'pass';
	}
	protected function fail()
	{
		$this->pass = 'fail';
	}
	protected function critical_fail()
	{
		$this->pass = 'critical';
	}
	protected function undetermined()
	{
		$this->pass = 'undetermined';
	}

	public function html_class()
	{
		return ' ' . $this->test_id . ' ' . $this->pass;
	}
	
	
	protected function sub_test_table()
	{
		$sub_tests_to_show = array();
		
		if($this->test_table_fail_only)
		{
			foreach($this->sub_tests as $test)
			{
				if($test['pass'] !== 'pass')
				{
					$sub_tests_to_show[] = $test;
				}
			}
		}
		else
		{
			$sub_tests_to_show = $this->sub_tests;
		}
		
		if( count($sub_tests_to_show) == 0 )
			return '';
		

		$table = '<table>';

		if($this->test_table_headers)
		{
			$table .= '<thead><tr>';
			foreach($sub_tests_to_show[0]['table_columns'] as $column_name => $val)
			{
				$table .= '<th>' . $column_name . '</th>';
			}
			$table .= '</tr></thead>';
		}
		
		$table .= '<tbody>';
		
		foreach($sub_tests_to_show as $row => $test)
		{
			$table .= '<tr class="' . $test['pass'] . '">';
			foreach($test['table_columns'] as $cell)
			{
				$table .= '<td>' . $cell . '</td>';
			}
			$table .= '</tr>';
		}

		$table .= '</tbody>';
		$table .= '</table>';
		return $table;
	}
	
	public function format_result_info()
	{
        /*
            Debugging
        */
        $debug = false;
        $debug_text = array();
        if( $debug )
        {
            $run_time = ($this->end_time - $this->start_time);
            $debug_text[] = $run_time . ' seconds';

            if( $this->num_paths )
            {
                $debug_text[] = $this->num_paths . ' paths : ' . ($run_time / $this->num_paths * 1000000) . ' microsecs/path';
            }
            
            list($min1, $min5, $min15) = sys_getloadavg();
            $debug_text[] = 'Memory usage: ' . $this->convert_bytes(memory_get_peak_usage(true)) . ' : ' . 'CPU load: ' . $min1;
        }
        $debug_text = implode('<br>', $debug_text);
        
        
		$this->message .= <<<EOD
			
			<div class='result_details'>			
EOD;
		if($this->pass == "notrun")
		{
			$this->message .= '<h3>Result: <span class="sub-head">Test not run.</span></h2>';
		}
		else
		{
			$this->message .= '<h3>Result</h3>';
            
			$this->message .= $debug_text;

			// Only display test results if test is not undetermined
			if( $html = $this->result_text() )
			{
				$this->message .= $html;
			}

		}
		$this->message .= <<<EOD
			
			</div>
EOD;
		
		if( $this->test_table_show && $table = $this->sub_test_table())
		{
			$this->message .= "<div class='sub_tests'>\n";
			$this->message .= $table;
			$this->message .= "\n</div>\n";
		}
		
		if( $html = $this->why_important() )
		{
			switch($this->pass)
			{
				case 'pass':
					$title = 'Why is this important?';
				break;
				case 'critical':
					$title = 'Why is this a problem?';
				break;
				case 'fail':
				default:
					$title = 'Why might this be an issue?';
				break;
			}
			
			$this->message .= <<<EOD
			
			<div class='why_important'>			
				<h3>{$title}</h3>			
				{$html}				
			</div>
EOD;
		}
		
		if( $html = $this->how_to_fix() )
		{
			if( $difficulty = $this->fix_difficulty() )
			{
				$difficulty = "<p><strong>Difficulty:</strong> {$difficulty}</p>\n";
			}
			else
			{
				$difficulty = '';
			}
			
			$this->message .= <<<EOD
			
			<div class='how_to_fix'>			
				<h3>How can this be fixed?</h3>				
				{$difficulty}		
				{$html}				
			</div>
EOD;
		}
	}

	
	public function get_dir_contents( $path ) 
	{
		if ( ! $handle = @opendir( $path ) ) 
			return array(); // Error

		$dir_contents = array();
		while ( $file = readdir( $handle ) ) 
		{
			if ( $file == '.' || $file == '..' ) 
				continue;

			$fullpath = $path . '/' . $file;
			if ( is_dir( $fullpath ) ) 
			{
				$dir_contents = @array_merge( $dir_contents, $this->get_dir_contents( $fullpath ) );
			} 
			else 
			{
				$dir_contents[] = $fullpath; 
			}
		}
		closedir( $handle );
		return $dir_contents;
	}
	
    protected function wp_config_path()
    {
		if ( file_exists( ABSPATH . 'wp-config.php') ) 
		{
			return ABSPATH . 'wp-config.php';
		} 
		elseif ( file_exists( dirname(ABSPATH) . '/wp-config.php' ) && ! file_exists( dirname(ABSPATH) . '/wp-settings.php' ) ) 
		{
			return dirname(ABSPATH) . '/wp-config.php';
		}
        else
        {
            return false;
        }        
    }
    
    protected function convert_bytes($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }
 
    protected function strip_site_root($path)
    {
        return str_replace(ABSPATH, '<span class="abspath">[ SITE ROOT ] /</span>', $path);
    }

	protected function get_sld($url)
	{
		if($url_host = parse_url($url, PHP_URL_HOST))
		{
			$host_parts = explode('.', $url_host);
			$host_parts = array_diff($host_parts, array(
				'blog',
				'www',
				'test',
				'news',
			));
			// Ignore SLD's of 3 chars or less since it's not a likely SLD!
            $slds = array();
            foreach($host_parts as $p)
            {
                if(strlen($p) > 3)
                {
                    $slds[] = $p;
                }
            }
			if( isset($slds[0]) )
			{
				return $slds[0];
			}
		}
		return '';
	}
    
    
    protected function start_timer()
    {
        $this->start_time = microtime(true);
    }
    protected function stop_timer()
    {
        $this->end_time = microtime(true);
    }
    
}