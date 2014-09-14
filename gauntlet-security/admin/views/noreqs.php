<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class='wrap gauntlet'>

    <div class='header'>	
        <div class='header_liner content_wrap'>
        	<a class='logo' href='<?php echo admin_url('admin.php?page=gauntlet-security'); ?>'>Gauntlet Security</a>
            <a class='info_link' href='<?php echo admin_url('admin.php?page=gauntlet-more-info'); ?>'>More Info</a>
            <div class='clear'></div>
        </div>
    </div>


    <?php // Placeholder for admin_notices ?>
    <h2></h2>


    <div class="gauntlet-wrap">
	
    	<?php
    	?>

        <p class='plugin_blurb'>
            <strong>
                Sorry, this plugin will not work in your server environment.<br><br>
            </strong>
        </p>

        <p>
            <strong>Requirements:</strong>
        </p>
    
        <ul>
            <li>WordPress <?php echo $server_info['req_wp_version']; ?>+</li>
            <li>PHP <?php echo $server_info['req_php_version']; ?>+</li>
            <li>Apache web server</li>
            <li>Multisite is not supported (yet)</li>
        </ul>
    
        <p>
            <strong>Your info:</strong>
        </p>
    
        <ul>
            <li>WordPress <?php echo $server_info['wp_version']; ?></li>
            <li>PHP <?php echo $server_info['php_version']; ?></li>
            <li><?php echo $server_info['web_server']; ?></li>
            <li><?php 
                if( $server_info['multisite'] ) 
                {
                    echo "Multisite";
                }
                else
                {
                    echo "Not multisite";
                }
                ?></li>
        </ul>
    
    </div>

</div>