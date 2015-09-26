<?php
/*
Plugin name: Manual cron
Plugin URI: https://github.com/szepeviktor/manual-cron
Description: Triggers wp-cron by an AJAX call and displays possible output.
Version: 1.0.0
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/manual-cron
*/

add_action( 'wp_dashboard_setup', 'o1_manual_cron_add_widget' );

function o1_manual_cron_add_widget() {

    wp_add_dashboard_widget(
        'manual_cron_widget',
        'Manual WP-cron',
        'o1_manual_cron_output'
    );
}

function o1_manual_cron_output() {

    // In the middle of a PHP is not the nicest place for JavaScript
    $trigger_script = '
<script>
(function ($) {
    var $output = $("#manual-cron-output");

    $("#manual-cron").click(function () {
        $.ajax({
            url: "%s",
            method: "POST",
            timeout: %d,
            dataType: "html",
            success: function (data) {
                $output.html("<code>OK</code>" + data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $output.html("<code>ERROR</code> " + textStatus + " " + errorThrown);
            }
         });
     });
}(jQuery));
</script>
    ';

    $doing_wp_cron = sprintf( '%.22F', microtime( true ) );
    $url = add_query_arg( 'doing_wp_cron', $doing_wp_cron, site_url( 'wp-cron.php' ) );
    $timeout = apply_filters( 'manual-cron-timeout', 20000 );

    print '<button id="manual-cron" class="button button-primary">Run now</button>';
    print '<div id="manual-cron-output" style="margin-top: 8px;"></div>';
    printf( $trigger_script, $url, $timeout );
}
