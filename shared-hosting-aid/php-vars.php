<?php
/*
Snippet Name: Display extended phpinfo()
Version: 1.0.0
Snippet URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
*/

phpinfo();
$php_configs = array(
    'user_ini.filename',
    'expose_php',
    'max_execution_time',
    'memory_limit',
    'upload_max_filesize',
    'post_max_size',
    'allow_url_fopen',
    'default_charset',
    'date.timezone',
    'disable_functions',
    'open_basedir',
    'session.save_path',
    'upload_tmp_dir',
    'display_errors',
    'log_errors',
    'error_log'
);
print "<div class='center'><h2 id='impvar'>*Important variables</h2><table width='600' border='0' cellpadding='3'>";
foreach ( $php_configs as $ini ) {
    $value = ini_get( $ini );
    printf( '<tr><td class="e">%s</td><td class="v">%s</td></tr>',
        $ini,
        var_export( $value, true )
    );
}
print "<tr><td class='e'>_SERVER['DOCUMENT_ROOT']</td><td class='v'>{$_SERVER['DOCUMENT_ROOT']}</td></tr>";
print "</table><br/></div><script>window.location.hash='impvar';</script>";
