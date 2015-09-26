<?php
/*
Snippet name: PHP Post-Mortem Tool
Version: 2.0
Usage: Include it from your config file or entry point.
Source: https://gist.github.com/uuf6429/6719756
*/

function postmortem_on_shutdown(){
    global $php_errormsg;
    $file = $line = null;
    $last_errors = $included_files = '';
    $sep = "\n\t\t";

    // headers
    headers_sent( $file, $line );

    // errors
    if ( function_exists( 'error_get_last' ) && error_get_last() )
        foreach( error_get_last() as $k => $v )
            $last_errors .= "{$sep}{$k}: {$v}";
    elseif ( isset( $php_errormsg ) && $php_errormsg )
        $last_errors .= "{$sep}Error: {$php_errormsg}";
    else
        $last_errors .= "{$sep}none";

    // included files
    $included_files = implode( $sep, get_included_files() );

    // output
?>
<!-- Post-Mortem

	Headers Sent:
		<?php echo $file . ": " . $line ?>

	Last Error:<?php echo $last_errors ?>

	Included Files:
		<?php echo $included_files ?>

-->
<?php
}

register_shutdown_function( 'postmortem_on_shutdown' );

while ( ob_get_level() )
    ob_end_clean();
ob_implicit_flush( true );

error_reporting( E_ALL );
ini_set( 'display_errors', true );
