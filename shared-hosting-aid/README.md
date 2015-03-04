### FTP/SFTP access

```bash
SSLOFF="set ftp:ssl-allow off;"

# lftp -e "$SSLOFF" -u 'FTP-USER,FTP_PASS' FTP_HOST.
lftp -e "cd ~" -u 'FTP-USER,FTP_PASS' FTP_HOST.
```

### Check hosting

https://github.com/szepeviktor/hosting-check

### wp-config.php

https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/wordpress-fail2ban/block-bad-requests/wp-login-bad-request.inc.php

```php
require_once( dirname( __FILE__ ) . '/wp-login-bad-request.inc.php' );
```

```php
// see: shared-hosting-aid/enable-logging.php
// https://github.com/szepeviktor/hosting-check/blob/master/hc-query.php
ini_set( 'error_log', '<HOME/SECRETDIR>/error.log' );
ini_set( 'log_errors', 1 );

//define( 'WP_DEBUG', true );
define( 'WP_DEBUG', false );

define( 'WP_MAX_MEMORY_LIMIT', '96M' );
//define( 'WP_MAX_MEMORY_LIMIT', '196M' );
define( 'WP_POST_REVISIONS', 10 );
define( 'WP_USE_EXT_MYSQL', false );

//define( 'WP_CACHE', true);

// cron:  wget -q -O- http://<DOMAIN-TLD>/wp-cron.php||echo "<WEBSITE>: $?"
define( 'DISABLE_WP_CRON', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'DISALLOW_FILE_EDIT', true );

define( 'ITSEC_FILE_CHECK_CRON', true );
define( 'ITSEC_BACKUP_CRON', true );

define( 'ENABLE_FORCE_CHECK_UPDATE', true );

/*
// upload and session directory
ini_set( 'upload_tmp_dir', '%s/tmp' );
ini_set( 'session.save_path', '%s/session' );
// comment out after first use
mkdir( '%s/tmp', 0700 );
mkdir( '%s/session', 0700 );
*/

/*
// for different FTP/PHP UID
define( 'FS_METHOD', 'direct' );
define( 'FS_CHMOD_DIR', (0775 & ~ umask()) );
define( 'FS_CHMOD_FILE', (0664 & ~ umask()) );
*/
```

### Root files

- .htaccess
- *htaccess*
- index.html
- index.php
- favicon.ico
- robots.txt
- apple-touch-icon.png
- apple-touch-icon-precomposed.png
- sitemap.xml
- sitemap.xml.gz
- browserconfig.xml
- crossdomain.xml
- labels.rdf

https://github.com/h5bp/mobile-boilerplate/blob/master/index.html
http://realfavicongenerator.net/

```apache
# NO index files for robots
<FilesMatch "^(robots\.txt|sitemap\.xml|sitemap\.xml\.gz)$">
  Header append X-Robots-Tag "noindex"
</FilesMatch>
```

### Webserver settings

- keep-alive
- mime-type
- content-compression
- content-cache

https://github.com/h5bp/html5-boilerplate/blob/master/.htaccess
https://redbot.org/

`l.php`

```php
<?php
phpinfo();
echo '<pre style="white-space: pre-wrap;">';
$php_configs = array(
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
    'error_log'
);
foreach ( $php_configs as $ini ) {
    echo "{$ini} = " . ini_get( $ini ) . PHP_EOL . PHP_EOL;
}
echo '<hr/>';
var_dump( $_SERVER );
```

Default `mail-sender.php`?

```php
<pre><?php
$to      = "viktor@szepe.net";
$subject = "[Default mail sender] First mail from {$_SERVER['SERVER_NAME']}";
$message = var_export( $_ENV, true );
$headers = "X-Mailer: PHP/" . phpversion();
$mail = mail( $to, $subject, $message, $headers );
echo "mail() returned: " . var_export( $mail, true );
```

Set or redirect as necessary.
Set usual mail accounts: info, postmaster, webmaster, abuse

### PHP security check

`.htaccess`

```apache
SetEnv PCC_ALLOW_IP 1.2.3.4
```

https://github.com/sektioneins/pcc/raw/master/phpconfigcheck.php

### File change notification

```bash
git clone https://github.com/szepeviktor/Tripwire.git
cd Tripwire
git submodule init && git submodule update
cp tripwire_config.sample.ini tripwire_config.ini
```

### Block access to a directory

`.htaccess`

```apache
Deny from all
#Require all denied
```

### MySQL

- ALTER table engine
- //TODO

### MU plugins

- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/wordpress-fail2ban/mu-plugin/wp-fail2ban-mu.php
- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-disable-updates/disable-updates.php
- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-protect-plugins/protect-plugins.php

### File change notification

- https://github.com/szepeviktor/Tripwire
- tripwire access protection `.htaccess`

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{REQUEST_URI} !=/<SECRET_DIR>/tripwire.php
  RewriteRule ^ - [F]
</IfModule>
```

### SMTP

Use reliable smart host with STARTTLS.

### Monitoring

- DNS: NS, A, MX
- <DOMAIN.TLD>/ping.txt|grep -F "file content"
- <DOMAIN.TLD>/ping.php|grep -F "PHP version + MySQL version MD5"

```php
<?php
if ( '<MANAGEMENT_SERVER_IP>' !== @$_SERVER['REMOTE_ADDR'] ) {
    error_log( 'Malicious traffic detected by wpf2b: ping_direct_access '
        . addslashes( @$_SERVER['REQUEST_URI'] )
    );
    ob_get_level() && ob_end_clean();
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.0 403 Forbidden' );
    exit();
}
$wpload_path = dirname( __FILE__ ) . '/wp-load.php';
define( 'WP_USE_THEMES', false );
require_once( $wpload_path );
global $wpdb;
$mysql_version_query = "SHOW VARIABLES LIKE 'version'";
$pong = phpversion() . '|' . $wpdb->get_var( $mysql_version_query, 1 );
exit( md5( $pong ) );
```

- front page|grep "<h1>string check" , |grep -Ei "mysql|php|error|notice|warning"
- pingdom  https://www.pingdom.com/free/
- RBL blacklists  https://www.rblmon.com/
- can-send-email/day: use smarthost, whitelist on smarthost, get can-send-email.php, wait 5 minutes, check mailbox for subject:

`Subject: [admin] can-send-email from <HOSTNAME>`

```php
<?php
$to      = "viktor@szepe.net";
$subject = "[admin] can-send-email from {$_SERVER['SERVER_NAME']}";
$headers = "X-Mailer: PHP/" . phpversion();
// http://www.randomtext.me/download/txt/gibberish/p-5/20-35
$message = '
Much bowed when mammoth for lusciously lost a dear whooped some ouch insufferably one indefatigably contemplated manifestly therefore much mongoose and llama far feeble a cocky.

Robin the whistled scorpion mongoose fleetly past together toucan compulsively coarsely inadvertent far hence within when up prissily amicable one and since gawked jollily rude.

Met patiently excluding because and far sleazily sufficiently hyena enormously that goodness much hawk mastodon walking this this whale ouch shed kookaburra sleekly that one the affably.

Alarmingly much this the inoffensive in more much sobbed aboard reined that labrador ordered much less jeez gibbered checked a wove selflessly goodness this adjusted honey flustered a that turtle unavoidable hello messily.

Cringed apart complete bat knitted impulsively domestic behind jokingly a far jeepers folded blubbered wildebeest lighthearted much exultingly yikes yawned well winced swept far slowly decorously.
';
$mail = mail( $to, $subject, $message, $headers );
if ( true !== $mail )
    echo "mail() returned: " . var_export( $mail, true );
exit;
```

- monitor error-log/30 minutes ???munin-plugin: log size in lines
- rotate error.log ???
- opcache, apc, memcache/week
- domain name expiry
- Safebrowsing check
- SEO Panel/week
- Analytics/week
- WMT/week
- PageSpeed, webpagetest/week
