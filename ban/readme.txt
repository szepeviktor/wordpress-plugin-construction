- WordPress fail2ban MU plugin installer -

*Proactive security with Fail2ban* consists of three parts:

1. The normal plugin to install the MU plugin
1. The MU plugin that handles WordPress related malicious traffic
1. *Block Bad Requests* that is a HTTP analyzer
1. *Miniban* that bans IP addresses.
1. A WP-cron event that unbans IP addresses after 1 day.

--- Block Bad Requests ---

Block Bad Requests is a HTTP analyzer, it examines almost all HTTP headers.
It usage can be found in the
[WordPress fail2ban GitHub repository](https://github.com/szepeviktor/wordpress-fail2ban/tree/master/block-bad-requests)

To install Block Bad Requests copy this line to your `wp-config.php`:

`
require_once dirname( __FILE__ ) . '/wp-content/plugins/ban/block-bad-requests/wp-fail2ban-bad-request-instant.inc.php';
`

If you `wp-config.php` is above the document root or your content directory is not the standard
then you should modify the `require_once` statement.

--- Miniban ---

Miniban is trying to striving to replace Fail2ban.
Miniban can can IP addresses in several ways. This plugin includes the `.htaccess` ban method.
Other methods can be found in the
[WordPress fail2ban GitHub repository](https://github.com/szepeviktor/wordpress-fail2ban/tree/master/miniban).
There is even a method that keeps IP address in a WordPress option for cases when
`.htaccess` banning is not suitable.

To install Miniban copy these lines to your `wp-config.php`:

`
require_once dirname( __FILE__ ) . '/wp-content/plugins/ban/miniban/wp-miniban-htaccess.inc.php';
Miniban::init(
    dirname( __FILE__ ) . '/.htaccess',
    // These IP addresses and IP ranges will get whitelisted.
    array( '127.0.0.0/8', '193.188.137.175', '66.249.64.0/19' ),
    array( 'header' => 'Remote_Addr' )
);
`

If you `wp-config.php` is above the document root or your content directory is not the standard
then you should modify the `require_once` statement.

Modify the whitelisted IP addresses as necessary.

If your web host supports writing to the parent directory - relative to the document root -
you can change the first parameter to `dirname( dirname( __FILE__ ) ) . '/.htaccess'`.
This way Miniban will have a separate `.htaccess` file.

If your website is behind a proxy server replace `Remote_Addr` with the appropriate header.
It should be usable in `SetEnvIf` Apache directive.
