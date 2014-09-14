<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_PhpFunctions extends gus_TestBase
{
	protected $test_table_show = true;
	protected $test_table_headers = false;
	protected $test_table_fail_only = false;

	private $dangerous = array(
		'exec', 
		'passthru', 
		'shell_exec', 
		'system', 
		'proc_open', 
        'pcntl_exec',
	);

	
	protected function main_check()
	{
		$disabled_functions = explode(',', ini_get('disable_functions'));
		foreach($this->dangerous as $func)
		{
			$this->run_sub_test( array(
				'dangerous_function' => $func,
				'disabled_functions' => $disabled_functions
			) );
		}
	}
	
	protected function sub_test( $args )
	{
		$dangerous_function = $args['dangerous_function'];
		$disabled_functions = $args['disabled_functions'];
		
		$sub_test = array(
			'pass' => 'undetermined',
			'table_columns' => array(
				'Function' => $dangerous_function,
				'Description' => '',
			),
		);
		
		if( ! in_array($dangerous_function, $disabled_functions))
		{
			$sub_test['pass'] = 'fail';
			$sub_test['table_columns']['Description'] = '<span class="error">Enabled</span>';
		}
		else
		{
			$sub_test['pass'] = 'pass';
			$sub_test['table_columns']['Description'] = 'Disabled';
		}
		
		return $sub_test;
	}

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "Dangerous PHP functions are disabled";
			break;
			
			case 'fail':
			case 'critical':
			return "There are some dangerous PHP functions enabled";
			break;
			
			case 'undetermined':
			default:
			return "Disable dangerous PHP functions";
			break;			
		}
	}
	
	protected function result_text()
	{
		return <<<EOD
EOD;
	}
	
	protected function why_important()
	{
		$dangerous_csv = implode(', ', $this->dangerous);

		return <<<EOD
			<p>These PHP functions should all be disabled: {$dangerous_csv}</p>
        
            <p>There are a lot more PHP functions that could be used for nefarious 
            purposes however many of them are very useful and very popular. 
            We can't disable all of them. 
            The list above includes some of the least necessary and most dangerous of the
            PHP functions.
            </p>
EOD;
	}
	
	protected function how_to_fix()
	{
		$dangerous_csv = implode(',', $this->dangerous);

		return <<<EOD

		<p>WordPress does not require any of these functions but some plugins might. 
        Plugins which provide back-up functionality often do, and to use
        these you'll need to re-enable those specific functions.</p>

		<p>You may or may not have the ability to disable these - it depends on your server set-up. 
		(This can't be set from an htaccess file.)</p>

		<p>If you have access to your system php.ini file, add:</p>
	
        <code class='prettyprint'>disable_functions ={$dangerous_csv}</code>

		<p>Then restart Apache.</p>
	
		<p>If you don't have access to to the system php.ini file, try adding that code to a php.ini in the root directory of your site. 
		If that doesn't work, contact your host and ask them if they can help you. 
        Hosts differ widely on how to customize the PHP configuration from one domain to another.
        </p>
		

EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Advanced';
	}

}