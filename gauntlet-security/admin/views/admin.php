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
	
        <div class='plugin_intro content_wrap'>
        
            <p class='plugin_blurb'>
                <strong>
                    Gauntlet Security will examine your WordPress installation and
                    give you tips on how to make it more secure.
                </strong>
            </p>
    
        	<?php
        	$ajax_nonce = wp_create_nonce( 'run-the-gauntlet' );
        	?>
        	<button class='button button-primary button-scan' data-nonce='<?php echo $ajax_nonce; ?>'>Scan Now</button>

	
            <div class='disclaimer'>
                <a href='#' class='disclaimer_toggle'>Disclaimer</a>
                <span class='disclaimer_body'>I can't guarantee that the suggestions below will not break your site 
                or that they will prevent it from being hacked.
                Before attempting any of these fixes, you should be comfortable 
                experimenting and know how to undo any change you make.
                Read more about how to use this plugin on the 
                <a href='<?php echo admin_url('admin.php?page=gauntlet-more-info'); ?>'>info page</a>.</span>
            </div>
        

            <div class='toggle_all_wrapper'>
                <a href='#' class='toggle_all'>Expand All</a>
            </div>
        
        </div>
    
	
	
    	<?php if($test_results): ?>

    		<?php foreach($test_results as $category => $test_statuses): ?>
    	<h3><?php echo $category; ?></h3>
    	<ol class='test_group content_wrap'>
		
    		<?php foreach($test_statuses as $test_group): ?>
    		<?php foreach($test_group as $test): ?>
		
    		<li class='<?php echo $test->html_class() ?>'>			
			
    			<h2<? if($test->message): ?> class='toggle has_icon_lg'<? endif; ?>>
                    <? if($test->message): ?><span class='more_link'>More</span><? endif; ?>
                    <?php echo $test->title() ?>
                </h2>
			
    			<? if($test->message): ?>
    			<div class='test_message'><?php echo $test->message ?></div>			
    			<? endif; ?>
    		</li>
		
    		<?php endforeach; ?>
    		<?php endforeach; ?>

    	</ol>
    		<?php endforeach; ?>


    	<?php endif; ?>

	
    	<script id="test_result_template" type="x-tmpl-mustache">
		
    		<h2 class='toggle has_icon_lg'>
                <span class='more_link'>More</span>
                {{ title }}
            </h2>			
		
    		<div class='test_message'>{{{ message }}}</div>

    	</script>

    </div>

</div>
