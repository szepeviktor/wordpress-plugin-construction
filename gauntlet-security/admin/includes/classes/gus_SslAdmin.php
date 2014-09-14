<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class gus_SslAdmin extends gus_TestBase
{
    private $force_ssl_login = false;

	protected function main_check()
	{
		if( force_ssl_login() )
		{
			$this->force_ssl_login = true;
		}

        /*
            Even if FORCE_SSL_LOGIN is true,
            if FORCE_SSL_ADMIN is not set, there will be security issues.
        
            https://core.trac.wordpress.org/ticket/10267#comment:20
        
            TODO: This may not be true in WP v.4. The functionality of 
            FORCE_SSL_LOGIN may become the same as FORCE_SSL_ADMIN.
        */

		if( force_ssl_admin() )
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
			return "The login page and admin area can only be accessed over SSL";
			break;
			
			case 'fail':
			case 'critical':
			return 'The login and admin pages can be accessed without SSL';
			break;
			
			case 'undetermined':
			default:
			return "Force SSL when accessing the admin area";
			break;			
		}
	}
	
	protected function result_text()
	{
        /*
            FORCE_SSL_ADMIN is not set but FORCE_SSL_LOGIN is: Boo
        */
        if( $this->pass !== 'pass' && $this->force_ssl_login == true )
        {
            return <<<EOD

            <p>This site enforces SSL for the login page but not for the general 
                admin area. While the act of logging
                in is encrypted, any of the logged-in users' cookies will still be 
                accessible over regular HTTP.</p>

            <div class='subtests'>
                <table>
                    <tbody>
                        <tr>
                            <td>FORCE_SSL_LOGIN</td>
                            <td>true</td>
                        </tr>
                        <tr>
                            <td>FORCE_SSL_ADMIN</td>
                            <td><span class="error">false</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

EOD;
        }
        
        /*
            Neither FORCE_SSL_ADMIN nor FORCE_SSL_LOGIN is set: Boo
        */
        if( $this->pass !== 'pass' )
        {
            return 'An SSL connection is not being enforced on admin pages. <code class="prettyprint">FORCE_SSL_ADMIN = false</code>';
        }
        
        /*
            FORCE_SSL_ADMIN is set: Yay
        */
        if( $this->pass == 'pass' )
        {
            return 'An SSL connection is being enforced on admin pages. <code class="prettyprint">FORCE_SSL_ADMIN = true</code>';
        }
	}
	
	protected function why_important()
	{
		return <<<EOD
            
        <p>If you are logging in to your WordPress control panel using a public wifi
           network, it's not too difficult for hackers nearby to snoop on your traffic. 
           They can then hijack your browsing session by copying your cookies. Your browser 
           uses those cookies to prove to your site that you are an authenticated user. 
           This is called a "man-in-the-middle" attack.</p>
          
        <p>When your admin area is accessed over SSL (https://), traffic
            is encrypted and these sorts of attacks are prevented.</p>

EOD;
	}
	
	protected function how_to_fix()
	{
		return <<<EOD
			
        Changing the WordPress configuration setting is easy. Add this to your wp-config.php file...
        
        <code class="prettyprint">define('FORCE_SSL_ADMIN', true);</code>
        
        ...but setting up SSL on your server is not so simple. For more information, start here:
		<a target='_blank' href='http://codex.wordpress.org/Administration_Over_SSL'>codex.wordpress.org/Administration_Over_SSL</a>
		
EOD;
	}
	
	protected function fix_difficulty()
	{
		return 'Advanced';
	}
	
}