<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_PluginAudit extends gus_TestBase
{
	protected $test_table_show = true;
	protected $test_table_headers = true;
	protected $test_table_fail_only = false;

	private $rating_warning = '';
	private $update_warning = '';
	private $age_warning = '';		
	private $inactive_warning = '';		
	
	protected function main_check()
	{
		$all_plugins = get_plugins();
		foreach($all_plugins as $path => $plugin)
		{
			$this->run_sub_test( array(
				'path' => $path,
				'plugin' => $plugin
			) );
		}
	}
	
	protected function sub_test($args)
	{
		$path = $args['path'];
		$plugin = $args['plugin'];
		
		$sub_test = array(
			'pass' => 'undetermined',
			'table_columns' => array(
				'Plugin' => $plugin['Name'],
				'Last Updated' => '',
				'User Rating' => '',
				'Status' => '',
			),
		);

        /*
            Nicer, but only available in PHP 5.3+ ....
            $slug = strstr($path, '/', true);
        */
        $slug = substr( $path, 0, strpos( $path, '/' ) );
		
		$args = array();
		$response = wp_remote_request( 'http://api.wordpress.org/plugins/info/1.0/' . $slug . '.json', $args );

		if( ! is_object($response) && ( $response['response']['code'] == 200 ) && ( isset($response['body'])) )
		{
			$json = json_decode($response['body']);
		}
		
		// Default is a pass
		$sub_test['pass'] = 'pass';
		
		$statuses = array();


		/*
			Active or Inactive?
		*/
		if(is_plugin_inactive($path))
		{
			$sub_test['pass'] = ( $sub_test['pass'] == 'critical' ) ? 'critical' : 'fail';
			$statuses[] = 'Inactive';
		} 	


		if( is_object($json) )
		{
			/*
				User Rating
			*/			
			$minimum_acceptable_rating = 80;

			if($json->rating < $minimum_acceptable_rating)
			{
				$sub_test['pass'] = ( $sub_test['pass'] == 'critical' ) ? 'critical' : 'fail';
				$sub_test['table_columns']['User Rating'] = "<span class='error'>" . $json->rating . '%</span>';
			}
			else
			{
				$sub_test['table_columns']['User Rating'] = "<span class='okay'>" . $json->rating . '%</span>';
			}
			
			
			/*
				Last Updated
			*/
			$last_updated_date = strtotime($json->last_updated);
			$date_two_years_ago = time() - 60 * 60 * 24 * 365 * 2;
			
			$human_time_diff = human_time_diff($last_updated_date);
			
			if($last_updated_date < $date_two_years_ago || $human_time_diff == '2 years')
			{
				$sub_test['pass'] = ( $sub_test['pass'] == 'critical' ) ? 'critical' : 'fail';
				$sub_test['table_columns']['Last Updated'] = "<span class='error'>" . human_time_diff($last_updated_date) . "</span>";
			}
			else
			{
				$sub_test['table_columns']['Last Updated'] = "<span class='okay'>" . human_time_diff($last_updated_date) . "</span>";
			}
		
		
			/*
				Needs an update?
			*/
			// $installed = get_plugin_data( $path, false, false );
			if($json->version != $plugin['Version'])
			{
				$sub_test['pass'] = 'critical';
				$statuses[] = 'Needs an update';
			} 	
			
		}
		else
		{
			// Undetermined
			$sub_test['pass'] = ( $sub_test['pass'] == 'critical' ) ? 'critical' : 'fail';
			$statuses[] = 'Not in directory';
		}
		
		
		if( count($statuses) > 0 )
		{
			$status_str = implode(' &bull; ', $statuses);
			$sub_test['table_columns']['Status'] = '<span class="error">' . $status_str . '</span>';
		}
		

		return $sub_test;
	}
	
	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "Installed plugins are reputable and active";
			break;
			
			case 'fail':
			return 'There may be issues with some plugins';
			break;
			
			case 'critical':
			return 'There may be serious issues with some plugins';
			break;
			
			case 'undetermined':
			default:
			return "Review the development activity and reputation of all plugins";
			break;			
		}
	}
	
	protected function result_text()
	{
		$warnings = '';
		if( $this->inactive_warning )
			$warnings .= "{$this->inactive_warning}<br><br>";
		if( $this->update_warning )
			$warnings .= "{$this->update_warning}<br><br>";
		if( $this->age_warning )
			$warnings .= "{$this->age_warning}<br><br>";
		if( $this->rating_warning )
			$warnings .= "{$this->rating_warning}<br><br>";
		return $warnings;
	}
	
	protected function why_important()
	{
		return <<<EOD
			<p>One of the most vulnerable areas of most WordPress installs is the plugins directory. 
			The fewer plugins you use the less code there is that can potentially be exploited.

			This test will throw up a red flag if it spots that...
            </p>
            
            <ul>
			<li>the developer is no longer actively maintaining their plugin</li>
			<li>users give the plugin poor ratings</li>
			<li>the plugin is not listed in the WordPress Plugin Directory</li>
            <li>you haven't been keeping the plugin up-to-date</li>
            <li>you have the plugin installed but not activated</li>
            </ul>
            
EOD;
	}
	
	protected function how_to_fix()
	{
		return <<<EOD

            <p>Keep your plugins up to date!</p>

			<p>Download free plugins only from the 
			<a href='https://wordpress.org/plugins/' target='_blank'>WordPress.org Plugin Directory</a>. 
			Review the changelog. Are there a lot of security updates? 
			If so, it's good that the developer is fixing them, but the plugin functionality might 
			be prone to security issues. 
			Read through the plugin's support forum. Are people's questions being answered?</p>

			<p>Paid plugins are sometimes not available from as large a marketplace as the WP directory.
			But the same criteria should apply to paid plugins.
			You don't always get what you pay for.
			Find plugins with good reviews, proven support and a changelog you can review.
			</p>
		
			<p>Finally, if you have any unused plugins on the server, they should be deleted completely. 
			Even if a plugin is inactive, the files can still be accessed.</p>
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Easy';
	}

}