# WordPress website setup on cPanel

### cPanel settings

- Contact Information ×2
- FTP Accounts
- Backup
- Subdomains
- Email Account, Quota
- Email Account Forwarders
- Email Authentication
- Spamassassin
- Optimize Website https://github.com/szepeviktor/hosting-check/blob/master/templates/cpanel-mime.txt
- Web analytics
- Cron Jobs
- PHP Version / Options / Extensions

```
log_errors          Off
max_execution_time  65
memory_limit        512M
open_basedir        /home/USER:/opt/alt/php56/usr/share/pear:/opt/alt/php56/usr/share/php
post_max_size       32M
short_open_tag      Off
upload_max_filesize 32M
```

### Check PHP vars

see: shared-hosting-aid/php-vars.php

### WordPress maintenance

- Core update
- Uninstall and update Themes, Plugins
- Install Classic Smilies plugin
- Copy wordpress-fail2ban/mu-plugin/wp-fail2ban-mu.php into wp-content/mu-plugins/ and set `$trigger_count = 1;`
- .htaccess: root, wp-admin, wp-content/uploads

```php

// Upload, run and copy output of shared-hosting-aid/enable-logging.php

/*
// Enable email opens
$newsletter_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
if ( '/wp-content/plugins/newsletter/statistics/open.php' === $newsletter_path
    || '/wp-content/plugins/newsletter/statistics/link.php' === $newsletter_path
) {
    // UA hack for old email clients.
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 ' . $_SERVER['HTTP_USER_AGENT'];
}
*/

// Copy wordpress-fail2ban/block-bad-requests/wp-login-bad-request.inc.php
define( 'O1_BAD_REQUEST_COUNT', 1 );
require_once( dirname( __FILE__ ) . '/wp-login-bad-request.inc.php' );

define( 'WP_MEMORY_LIMIT', '96M' );
//define( 'WP_MAX_MEMORY_LIMIT', '384M' );
define( 'WP_POST_REVISIONS', 10 );
define( 'WP_USE_EXT_MYSQL', false );
define( 'DISALLOW_FILE_EDIT', true );
```

### WordPress settings

- blog_public
- admin_email

### Checks

http://www.webpagetest.org/

### Backup files and database

cPanel/Backup
