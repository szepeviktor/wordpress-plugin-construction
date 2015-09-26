<?php
/*
Snippet name: PHP Post-Mortem Tool
Source: https://gist.github.com/uuf6429/6719756
*/

function on_shutdown(){
    global $php_errormsg;
    $file = $line = null;

    echo "<!--\n\n\t";

    echo "Headers Sent:";
    headers_sent($file, $line);
    echo "\n\t\t$file: $line\n\t";

    echo "Last Error:";
    if (function_exists('error_get_last') && error_get_last())
        foreach(error_get_last() as $k => $v)
            echo "\n\t\t$k: $v";
    elseif (isset($php_errormsg) && $php_errormsg)
        echo "\n\t\tError: $php_errormsg";
    else
        echo "\n\t\tnone";
    echo "\n\t";

    echo "Included Files:";
    echo "\n\t\t" . implode("\n\t\t", get_included_files());

    echo "\n-->";
}

register_shutdown_function('on_shutdown');
while (ob_get_level()) ob_end_clean();
ob_implicit_flush(true);
error_reporting(E_ALL);
ini_set('display_errors', true);
