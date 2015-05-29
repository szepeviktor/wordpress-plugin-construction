<pre><?php
/*
Snippet name: Test mail sender
*/

test_mail_sender( "viktor@szepe.net" );

function test_mail_sender( $to = "viktor@szepe.net" ) {
    ini_set( 'display_errors', 1 );
    error_reporting( E_ALL );

    if ( function_exists( 'posix_getuid' ) ) {
        $uid = posix_getuid();
    } else {
        $uid = '??';
    }
    if ( function_exists( 'posix_geteuid' ) ) {
        $euid = posix_geteuid();
    } else {
        $euid = '??';
    }
    if ( function_exists( 'posix_getpwuid' ) ) {
        $real_user = posix_getpwuid( $uid );
        $effective_user = posix_getpwuid( $euid );
    } else {
        $real_user = $uid;
        $effective_user = $euid;
    }
    if ( function_exists( 'posix_getcwd' ) ) {
        $cwd = posix_getcwd();
    } else {
        $cwd = getcwd();
    }

    $subject = sprintf( "[Default mail sender] First mail from %s", $_SERVER['SERVER_NAME'] );
    $message = sprintf( "SAPI: %s\nreal user: %s\neffective user: %s\ncurrent dir: %s\nPHP version: %s",
        var_export( php_sapi_name(), true ),
        var_export( $real_user, true ),
        var_export( $effective_user, true ),
        var_export( $cwd, true ),
        var_export( phpversion(), true )
    );
    $headers = sprintf( "X-Mailer: PHP/%s", phpversion() );
    $mail = mail( $to, $subject, $message, $headers );
    printf( "mail() returned: %s", var_export( $mail, true ) );
}
