<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_WpGenerator extends gus_TestBase
{
	protected function main_check()
	{
		$tag_found = false;
		
		global $wp_filter;
		foreach($wp_filter['wp_head'] as $filter_set)
		{
			foreach($filter_set as $filter)
			{
				if( isset($filter['function']) && $filter['function'] == 'wp_generator')
				{
					$tag_found = true;
				}
			}
		}
		
		if( ! $tag_found )
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
			return "The WordPress version is not being advertised in the HTML Head";
			break;
			
			case 'fail':
			case 'critical':
			return 'The WordPress version is being advertised in the HTML Head';
			break;
			
			case 'undetermined':
			default:
			return "Don't advertise the WordPress version you are running";
			break;			
		}
	}
	
	protected function result_text()
	{
		switch($this->pass)
		{
			case 'pass':
			return "The WordPress version is not being advertised in the HTML Head";
			break;
			
			case 'fail':
			case 'critical':
			return 'The WordPress version is being advertised in the HTML Head';
			break;
		}
	}
	
	protected function why_important()
	{
		return <<<EOD
			
		<p>Actually, it's really not that important. Despite nearly every "top 10 security tips" blog post recommending
			that you should hide your WordPress version, it really isn't going to make a big difference.
			The only person likely to care about your version number is the hacker targeting your site
			specifically. And for the determined hacker, there are many other ways to figure it out. 
            </p>

		<p>But it's pretty easy to hide the version from a few common places so go ahead and do it! 
			It's not going to hurt.</p>
		
EOD;
	}
	
	protected function how_to_fix()
	{
        $code = <<<EOD

// Remove WP version from head
remove_action( 'wp_head', 'wp_generator' );

// Remove WP version from css & scripts
function my_remove_wp_ver_css_js( \$src ) {
    if ( strpos( \$src, 'ver=' ) ) {
        \$src = remove_query_arg( 'ver', \$src );
    }
    return \$src;
}
add_filter( 'style_loader_src', 'my_remove_wp_ver_css_js', 9999 );
add_filter( 'script_loader_src', 'my_remove_wp_ver_css_js', 9999 );	

// Remove WP version from RSS
add_filter( 'the_generator', '__return_empty_string' );


EOD;
        $code = trim($code);
        
		$jquery_link = includes_url() . 'js/jquery/jquery.js';
		
		return <<<EOD
		
        To remove version information add this to your functions.php file:
        
        <code class='prettyprint'>{$code}</code>

		<p>You can run but you cannot hide! 
            The version info is also located in the readme.html file. 
			The built-in version of jQuery is updated almost every time WordPress is. 
            So by viewing the source of <a target='_blank' href='{$jquery_link}'>your jQuery file</a> and comparing it to 
            <a target='_blank' href='http://wordpress.org/download/release-archive/'>older WordPress versions</a>, 
            yours can be pretty easily deduced. 
            </p>
        
        
			
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Intermediate';
	}	
	
}