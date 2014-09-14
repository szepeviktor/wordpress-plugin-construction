<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_FilePermissions extends gus_TestBase
{
	protected $test_table_show = true;
	protected $test_table_headers = true;
	protected $test_table_fail_only = false;

	protected function main_check()
	{
		$paths = array();

        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(WP_CONTENT_DIR, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );
        $iter->setMaxDepth(0);
        foreach ($iter as $path => $dir) 
        {
            $this->num_paths++;
            if ( $dir->isDir() ) 
            {
                $paths[] = array('Directory', $dir->getPathname(), 755, 755);
            }
        }
        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(ABSPATH, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );
        $iter->setMaxDepth(0);
        foreach ($iter as $path => $dir) 
        {
            $this->num_paths++;
            if ( $dir->isDir() ) 
            {
                $paths[] = array('Directory', $dir->getPathname(), 755, 755);
            }
        }


		$paths[] = array('Php.ini file', ABSPATH . 'php.ini', 400, 644);
		$paths[] = array('Config file', $this->wp_config_path(), 400, 644);
		$paths[] = array('Htaccess file', ABSPATH . '.htaccess', 404, 644);
		$paths[] = array('Index file', ABSPATH . 'index.php', 600, 644);
		$paths[] = array('Blog header file', ABSPATH . 'wp-blog-header.php', 600, 644);
		
		clearstatcache();
        
        $this->start_timer();
        
		foreach($paths as $path_arr)
		{
            $this->run_sub_test( array('path_arr' => $path_arr) );
		}

        $this->stop_timer();
	}
    
	protected function sub_test( $args )
	{
		$path_arr = $args['path_arr'];
		$name_plural = $path_arr[0];
		$path = $path_arr[1];
		$best_permissions = $path_arr[2];
		$min_permissions = $path_arr[3];
		
		$recommended = ($best_permissions !== $min_permissions) ? $best_permissions . ' &ndash; ' . $min_permissions : $best_permissions;
		
        $name = ($name_plural == 'Directories') ? 'Directory' : 'File';
        
		$sub_test = array(
			'pass' => 'undetermined',
			'table_columns' => array(
				'Recommended' => $recommended,
				'Current' => '',
				'Description' => $name_plural,
				'Path' => $this->strip_site_root( $path ),
			),
		);

        if( is_dir($path) || is_file($path) )
		{
		    $current_permissions = substr(sprintf("%o", fileperms($path)), -3);
			$sub_test['table_columns']['Current'] = $current_permissions;
            $this->num_paths++;

			if($best_permissions == $current_permissions)
			{
				$sub_test['pass'] = 'pass';
			}
			elseif($current_permissions <= $min_permissions)
			{
				$sub_test['pass'] = 'pass';
			}
			else
			{
				$sub_test['pass'] = 'critical';
			}
		}
		else
		{
			$sub_test['pass'] = 'pass';
			$sub_test['table_columns']['Current'] = '';
			$sub_test['table_columns']['Path'] = 'Not found';
		}		
		
		return $sub_test;
	}

	
	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return 'File and directory permissions allow for a minimum amount of access';
			break;
			
			case 'fail':
			case 'critical':
			return 'File and directory permissions allow for more access than necessary';
			break;
			
			case 'undetermined':
			default:
			return 'Set correct file and directory permissions';
			break;			
		}
	}
	
	protected function why_important()
	{
		return <<<EOD
			
		If another website on your server is compromised, correct permissions can prevent the hacker from 
		accessing your files.
		
EOD;
	}

	protected function how_to_fix()
	{
		$ABSPATH = ABSPATH;
		$wp_config_path = $this->wp_config_path();
		
        $code = <<<EOD

find {$ABSPATH} -type d -exec chmod 755 {} \; # for directories
find {$ABSPATH} -type f -exec chmod 644 {} \; # for files

EOD;
        $code = trim($code);
        
        $code3 = <<<EOD

chmod 400 {$ABSPATH}php.ini;
chmod 400 {$wp_config_path};
chmod 404 {$ABSPATH}.htaccess;
chmod 600 {$ABSPATH}index.php;
chmod 600 {$ABSPATH}wp-blog-header.php;
        
EOD;
        $code3 = trim($code3);
        
        $your_sapi = strtolower(php_sapi_name());
        if(strpos($your_sapi, 'apache') !== false)
        {
            $your_sapi = "<span class='warning'>It looks like your site's PHP handler <i>is</i> an Apache module. Following the directions below could break upload functionality and auto-update functionality if you have that currently working.</span>";
        }
        else
        {
            $your_sapi = "It looks like your site's PHP handler <i>is not</i> an Apache module. But it's not clear whether it's running as CGI, FastCGI, suPHP, or something else.";
        }

        
        
		return <<<EOD

		<p>
		There are many ways a server can be configured and the correct permissions will 
        largely depend on how Apache is handling PHP requests.        
        
        If you're not sure what you're doing here, please read this guide first:
		<a href='http://codex.wordpress.org/Changing_File_Permissions' target='_blank'>codex.wordpress.org/Changing_File_Permissions</a>.
        </p>
        
        <p>
        In order for WordPress to do auto-updates the user it is running as 
        needs to have write permissions on the core files.
        By far, the easiest PHP handler to use for this is either suPHP or FastCGI.  
        It can be very tricky to securely set up file permissions - and allow WordPress auto-updates -
        when using CGI or a PHP Apache module (mod_php).
        A good introduction to PHP handlers is:
        <a href='http://boomshadow.net/tech/php-handlers/' target='_blank'>boomshadow.net/tech/php-handlers</a>
        </p>
        
        <p><strong>{$your_sapi}</strong></p>
    
		<p>If you have access to your server's shell, 
		you can change all directory and file permissions with these commands:</p>
		
		<code class='prettyprint'>{$code}</code>

        <p>
            Some core files can be restricted further than those defaults.
            Try the most restrictive (lowest number) permissions listed here. 
            If something breaks, test again after increasing the permissions. 
            For instance, try setting the wp-config.php file to 400. 
            If WordPress breaks, then try 440, then 600, then 640, then 644.</p>
            
		<code class='prettyprint'>{$code3}</code>

EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Advanced';
	}
}