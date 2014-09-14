<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_UserNames extends gus_TestBase
{
	private $example_url = '';
	private $example_redirected_url = '';	

	protected function main_check()
	{
		$users = get_users();
		$first_user_id = $users[0]->ID;
			
		/*
			Can usernames be easily enumerated? (like with WPScan)
		*/
		$url = site_url() . '/?author=' . $first_user_id;
		$args = array();
		$response = wp_remote_head( $url, $args );
		if( 
			! is_object($response) && 
			( $response['response']['code'] == 200 ||
			$response['response']['code'] == 301 )
		)
		{
			$this->fail();
			
			$this->example_url = $url;
			
			/*
				Also display the (redirected) permalink for the URL if applicable
					Ex: http://wp-security-plugin:8888/author/user-login
			*/
			if($response['response']['code'] == 301)
			{
				$this->example_redirected_url = get_author_posts_url( $first_user_id );
			}
		}
		else
		{
			$this->pass();
		}
	}

	public function title()
	{
		switch($this->pass)
		{
			case 'pass':
			return "Usernames are not easy to discover through standard author URLs.";
			break;
			
			case 'fail':
			case 'critical':
			return "Usernames are easy to discover through standard author URLs.";
			break;
			
			case 'undetermined':
			default:
			return "Prevent username enumeration through standard author URLs.";
			break;			
		}
	}
	
	protected function result_text()
	{
		if($this->example_url)
		{
            $redirected = '';
			if($this->example_redirected_url)
			{
				$redirected = "
<a href='" . $this->example_redirected_url . "' target='_blank'>" . $this->example_redirected_url . "</a>";
			}
			$text = "Usernames can be discovered by visiting the default author archive url and editing the 
				querystring with likely user IDs:<br>
                <code class=''><a href='{$this->example_url}' target='_blank'>{$this->example_url}</a>{$redirected}</code>
				";

			return $text;
		}
		elseif( $this->pass != 'undetermined' )
		{
			return "Usernames are not easy to discover through standard author URLs";
		}
	}
	
	protected function why_important()
	{
		return <<<EOD

            <p>For paranoid web masters only.
            If a hacker wants to guess a login using brute force, it gives them a slight
            advantage to know which user name to target.
            If everyone used strong passwords this wouldn't be an issue.
            </p>

			<p>
            By default, WordPress includes a standard URL format for displaying author archives.
            For example, a hacker might try: 
            <code class='prettyprint'>yoursite.com/?author=1</code>
            If you have pretty permalinks enabled, that would redirect to something like: 
            <code class='prettyprint'>yoursite.com/author/loginname</code>
            </p>
                
            <p>
            If you are actually using those URLs for author archives, 
            it would be too much trouble than it's worth to try to remove the 
            usernames from the permalinks and the HTML of the author archive pages.
            But if you are not using author archive permalinks on your site, 
            you may as well disable those pages completely.             
            </p>
                
EOD;
	}
	
	protected function how_to_fix()
	{
		$code = <<<EOD

function my_disable_author_archive() {
    if ( is_author() ) {
        global \$wp_query;
        \$wp_query->set_404();
        status_header(404);
    } else {
        redirect_canonical();
    }
}
remove_filter( 'template_redirect', 'redirect_canonical' );
add_action( 'template_redirect', 'my_disable_author_archive' );

EOD;
		$code = trim($code);
		
		return <<<EOD

		<p>To completely disable the author archive pages add this to your functions.php file:</p>
	
        <code class='prettyprint'>{$code}</code>
	

EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Intermediate';
	}
}