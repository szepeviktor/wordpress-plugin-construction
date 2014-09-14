<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_WpTable extends gus_TestBase
{
	protected function main_check()
	{
		global $wpdb;
		
		if( $wpdb->prefix !== 'wp_' )
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
			return "The database table prefix has been changed from the default";
			break;
			
			case 'fail':
			case 'critical':
			return 'The default database table is being used: "wp_"';
			break;
			
			case 'undetermined':
			default:
			return "Change the default database table prefix";
			break;			
		}
	}
	
	protected function result_text()
	{
        global $wpdb;
        
		return <<<EOD
            
        The database table prefix is "{$wpdb->prefix}".
EOD;
	}
	
	protected function why_important()
	{
		return <<<EOD

        <p>Changing the table prefix could thwart hackers from altering 
            your database. By default, core WordPress tables will have the same names from one
            installation to another. Any SQL statement referencing "wp_users" for 
            example will fail if you've changed the table name to "omg_users".</p>
            
EOD;
	}
	
	protected function how_to_fix()
	{
        $code1 = <<<EOD

\$table_prefix  = 'omg_'; 

EOD;
        $code1 = trim($code1);
        
        $code2 = <<<EOD

RewriteCond %{REQUEST_URI} !.*wp-admin.*
RewriteRule .* - [R=503,L]

EOD;
        $code2 = trim($code2);
        
		return <<<EOD
            
        <p>This is easiest to change when first running the WordPress install. 
           But even if your site has been live for a while, it's highly recommended
           to change the prefix.</p>
        
        <strong>For a fresh install</strong>

        <p>When first installing WordPress, if not using a custom wp-config.php file,
            the install wizard will ask for a table prefix on the same page as the
            other database info.</p> 

        <p>If you are using a custom wp-config.php file before installation, you can 
            change the table prefix there.</p>
        
        <p>Use an underscore at the end of the prefix to make the table names easier to 
            read.</p>

        <code class='prettyprint'>{$code1}</code>
        
        <strong>After WordPress has been installed</strong>

        <p>There are plugins which do this but I would not trust them 100%. 
            Whether using a plugin or doing it yourself, make sure you 
            <strong>back up your database and have a plan for recovery</strong>. 
            This is a potentially dangerous procedure.
            One risk is that a poorly written plugin will have saved hard-coded references to 
            table names in their own database tables. Some plugins or themes may also 
            use hard-coded table names in their code. If that's the case, you'll need 
            to also find an alternative to that plugin or theme.
            </p>
        
        <p>Turn off your site while you make the change. A quick way to display
            a "Service Temporarily Unavailable" message is to add this to the top
            of your htaccess file...</p>

        <code class='prettyprint'>{$code2}</code>
        
        <p>Rename core WordPress tables. You can do this using phpMyAdmin. </p>
        
        <p>The prefix is hard-coded into other core WordPress tables as well...</p>

        <ul>
        <li>In the old <strong>wp_options</strong> table look for `option_name` = "wp_user_roles" and edit it to use the new prefix.</li>
        <li>In the old <strong>wp_usermeta</strong> table look for all values in the `meta_key` column that uses the old
             prefix and change them to the new one.</li>
        </ul>
             
        <p>Change the wp-config.php setting to your new prefix...</p>
        
        <code class='prettyprint'>{$code1}</code>
        
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Advanced';
	}
}