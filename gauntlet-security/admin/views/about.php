<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class='wrap gauntlet'>

    <div class='header'>	
        <div class='header_liner content_wrap'>
        	<a class='logo' href='<?php echo admin_url('admin.php?page=gauntlet-security'); ?>'>Gauntlet Security</a>
            <a class='info_link' href='<?php echo admin_url('admin.php?page=gauntlet-security'); ?>'>Gauntlet Security Home</a>
            <div class='clear'></div>
        </div>
    </div>


    <?php // Placeholder for admin_notices ?>
    <h2></h2>



    <div class="gauntlet-wrap gauntlet-about content_wrap">

        <div class='columns'>
        
            <div class='main'>
            
                <div class='how_to_use content_box'>
                
                    <h3>How To Use This Plugin</h3>
    
                    <p>
                    Gauntlet Security can alert you to things that you can do to 
                    make your site more secure.
                    It does not make changes to your database or to any of your files and 
                    it should be compatible with all other plugins.
                    </p>

                    <p>
                    Many of the recommendations Gauntlet Security makes involves 
                    editing your site's
                    php.ini, wp-config.php, .htaccess, or functions.php files. 
                    Doing so is not without risk and it's important
                    to understand what you're doing and how to revert your changes.
                    This is not a "one-click" solution.
                    The problem with many security plugins which do attempt to automate these 
                    types of changes is that there's no way to undo in case something breaks. 
                    If you're not sure what the plugin did and you need to revert its changes, 
                    you may be stuck with doing a full site restore from back-ups.
                    </p>                
                
                    <p>
                    Many of the things that Gauntlet Security suggests are based on 
                    recommendations from the WordPress codex:
                    <a href='http://codex.wordpress.org/Hardening_WordPress'>http://codex.wordpress.org/Hardening_WordPress</a><br>
                
                    If you are just starting to learn about WordPress security, that 
                    is a good place to start.            
                    </p>
            
                </div>

                <div class='remediation content_box'>
                
                    <h3>My Site Has Been Hacked!</h3>

                    <p>I feel for you. 
                        Please consult this guide which outlines the steps you should take:
                        <a href='http://codex.wordpress.org/FAQ_My_site_was_hacked'>http://codex.wordpress.org/FAQ_My_site_was_hacked</a>
                
                    </p>
                
                </div>

                <div class='strategy content_box'>
                
                    <h3>A Basic Security Strategy</h3>

                    In addition to the tips that this plugin suggests,
                    there are other things that should be an essential part of 
                    your security strategy.

                    <h4>Use strong passwords</h4>
                
                    Use strong passwords for everything - 
                    email, web host, domain registrar... 
                    If an administrator's email account gets hacked, 
                    their WordPress password can be reset using the "forgot
                    password" feature on the WordPress login page.
                
                    <h4>Keep your software up to date</h4>
                
                    This includes all software installed on your server - 
                    not just WordPress.
                                
                    <h4>Use a good web host</h4>
                
                    <p>A good web host will be pro-active in keeping the server 
                    software up to date. 
                    They can also allow you to run PHP with secure settings.</p>
                
                    <h4>Keep good back-ups</h4>
                
                    <p>"Good" back-ups mean back-ups you can confidently use to
                        rescue your site. Full site back-ups including all files 
                        and database info should be saved regularly and automatically.
                        You should have access to multiple back-ups so that if it 
                        takes some time before you discover your
                        site has been hacked, you can use an earlier back-up.</p>
                
                    <h4>Keep logs</h4>
                
                    <p>You should have access to your server access and error logs. 
                        These can be helpful in tracking down hack attempts.
                        Some plugins can also keep track of all login attempts and other 
                        important actions happening in your admin area.</p>
                
                    <h4>Isolate your site</h4>
                
                    <p>The fewer pieces of software you have running on your 
                        server and the fewer users that have access, the better.
                    </p>
        
                </div>

            </div>
        
            <div class='sidebar'>
            
                <div class='support_this_plugin content_box'>

                    <h3>Hi!</h3>
                
                    <p>I built this plugin to use on my own clients' sites as a general purpose
                        security checklist. 
                        There are other plugins that have similar functionality
                        but many of these didn't include the tests I needed, or were 
                        "all in one" plugins that included a lot of unwanted functionality.
                        </p>

                    <p>I intend to build on this group of tests and will continually 
                        revise the suggestions here to keep current with WordPress best practices.
                        If you have any ideas on how I can make this plugin better, please let me know.
                    </p>
                
                    <p>&mdash;Cornelius Bergen,
                    <a href='http://matchboxcreative.com'>Matchbox Creative</a></p>
                                
                </div>

            </div>
        
        </div>

    </div>
    
    
</div>