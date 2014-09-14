(function ( $ ) {
	"use strict";

	$(function () {

		// Run the google code prettifier
		prettyPrint();


		/*
			Run the scan
		*/
		$(".button-scan").on('click', function(event) {
            
			/*
				Look through the list of tests on the page and run each one
				individually
			*/
			var tests = [
				['gus_FilePermissions', 'slow'],
				['gus_DirectoryIndexing', 2],
				['gus_ExecutableUploads', 2],
				['gus_SecureIncludes', 2],
				['gus_WpContentLocation', 2],
				['gus_PhpFunctions', 2],
				['gus_PhpAllowUrl', 2],
				['gus_DbPassword', 2],
				['gus_WpTable', 2],
				['gus_WpVersion', 2],
				['gus_PhpDisplayErrors', 2],
				['gus_FileEditing', 2],
				['gus_KeysAndSalts', 2],
				['gus_WpGenerator', 2],
				['gus_AnyoneCanRegister', 2],
				['gus_SslAdmin', 2],
				['gus_PluginAudit', 'slow'],
				['gus_UnusedThemes', 2],
				['gus_BmuhtMit', 'slow'],
				['gus_AdminUsername', 2],
				['gus_CommonPasswords', 'slow'],
				['gus_UserIdOne', 'slow'],
				['gus_AdminCount', 'slow'],
				['gus_NickNames', 'slow'],
				['gus_UserNames', 'slow']
            ];
            var finished_tests = 0;

            // Start the "running" state
            for(var i=0; i<tests.length; i++){
                // Set test icons to "loading"
                // removeClass must be called AFTER addClass for repaint 
                $('.' + tests[i][0]).addClass('running').removeClass('pass fail critical notrun undetermined');

                // Disable the Scan Now button
                $(".button-scan").attr('disabled', 'disabled').html('Scanning');
            }
            

            for(var i=0; i<tests.length; i++){
                
				var data = {
					'action': 'run_a_test',
					'test_id': tests[i][0],
					'nonce': $(this).data('nonce')
				};
                
				// Uses ajaxq plugin from Foliotek: https://github.com/Foliotek/ajaxq

				$.postq('queue'+tests[i][1], ajaxurl, data, function(response, x_status, xhr) {
                    
                    // console.dir( response );

                    try {
    				    if( response.test_id ) {

                            // console.log('Test complete: ' + response.test_id);

        					/*
        						Get test result and display it..				
        					*/
        					var template = $('#test_result_template').html();
        					Mustache.parse(template);
        					var rendered = Mustache.render(template, {
        						"title": response.title,
        						"message": response.message					
        					});
        					$('.' + response.test_id).html(rendered);
        					$('.' + response.test_id).addClass(response.class);
        					$('.' + response.test_id).removeClass('running');       // removeClass must be called AFTER addClass for repaint
					
        					// re-run google code pretty print
        					prettyPrint();				        
    				    }
                    }
                    catch(e) {
                        // console.dir(e)
                    }
                    
                    finished_tests++;

                    // If tests are complete, re-enable Scan Now button
                    if( finished_tests === tests.length) {
                        $(".button-scan").removeAttr('disabled').html('Scan Now');
                        
                        // Any still "running" tests should now show as error'd
                        $('.running').addClass('undetermined').removeClass('running');
                                            
                    }
				}, 'json');
			}
			
			event.preventDefault();
			return false;
		});


        /*
            Toggle disclaimer
        */
        $(".disclaimer_toggle").on('click', function(event) {
            $(".disclaimer").toggleClass('show_disclaimer');            
        });
        
        


		/*
			Toggle info sections
		*/
		$(".test_group").on('click', '.toggle', function(event) {
			var $item = $(this).parent('li');
			if( $item.hasClass('open') ) {
				$item.removeClass('open');
			} else {
				$item.addClass('open');
			}
			// $item.find('.test_message').toggle();

			event.preventDefault();
			return false;
		});

		$(".toggle_all").on('click', function(event) {
			if( $(this).hasClass('open') ) {
				$(this).removeClass('open');
				$(this).html('Expand All');
				// $('.test_message').toggle(false);
				
				$(".test_group > li.open").removeClass('open');
				
			} else {
				$(this).addClass('open');
				$(this).html('Close All');
				// $('.test_message').toggle(true);

				$(".test_group > li").has(".test_message").addClass('open');
			}

			event.preventDefault();
			return false;
		});
		
		

	});

}(jQuery));