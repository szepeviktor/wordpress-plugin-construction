### FTP/SFTP access

```bash
SSLOFF="set ftp:ssl-allow off;"

# lftp -e "$SSLOFF cd" -u 'FTP-USER,FTP_PASS' FTP_HOST.
lftp -e "cd" -u 'FTP-USER,FTP_PASS' FTP_HOST.
```

### Check hosting

https://github.com/szepeviktor/hosting-check

### wp-config.php

https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/wordpress-fail2ban/block-bad-requests/wp-login-bad-request.inc.php

```php
define( 'O1_BAD_REQUEST_COUNT', 1 );
//define( 'O1_BAD_REQUEST_ALLOW_CONNECTION_CLOSE', true );
require_once( dirname( __FILE__ ) . '/wp-login-bad-request.inc.php' );
```

```php
// see: shared-hosting-aid/enable-logging.php

//define( 'WP_DEBUG', true );
define( 'WP_DEBUG', false );

define( 'WP_MAX_MEMORY_LIMIT', '96M' );
//define( 'WP_MAX_MEMORY_LIMIT', '196M' );
define( 'WP_POST_REVISIONS', 10 );
define( 'WP_USE_EXT_MYSQL', false );

//define( 'WP_CACHE', true);

// web cron:  wget -q -O- http://<DOMAIN-TLD>/wp-cron.php||echo "<WEBSITE>: $?"
// CLI cron:  /usr/bin/php <ABSPATH>/wp-cron.php  # stdout, stderr -> cron email
define( 'DISABLE_WP_CRON', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'DISALLOW_FILE_EDIT', true );

define( 'ITSEC_FILE_CHECK_CRON', true );
define( 'ITSEC_BACKUP_CRON', true );

define( 'ENABLE_FORCE_CHECK_UPDATE', true );

/*
// Upload and session directory.
ini_set( 'upload_tmp_dir', '%s/tmp' );
ini_set( 'session.save_path', '%s/session' );
// comment out after first use
mkdir( '%s/tmp', 0700 );
mkdir( '%s/session', 0700 );
*/

/*
// For different FTP/PHP UID.
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

`php-vars.php`

```php
<?php
phpinfo();
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
print '<div class="center"><h2>Important variables</h2><table width="600" border="0" cellpadding="3">';
foreach ( $php_configs as $ini ) {
    printf( '<tr><td class="e">%s</td><td class="v">%s</td></tr>',
        $ini,
        ini_get( $ini )
    );
}
print '</table><br/></div>';
```

Default `mail-sender.php`?

```php
<pre><?php
ini_set( 'display_errors', 1 );
error_reporting( E_ALL );
$to      = "viktor@szepe.net";
$subject = "[Default mail sender] First mail from {$_SERVER['SERVER_NAME']}";
//FIXME minimum email size: user id, user name, current dir, php version, webserver version
$message = var_export( $_ENV, true );
$headers = "X-Mailer: PHP/" . phpversion();
$mail = mail( $to, $subject, $message, $headers );
echo "mail() returned: " . var_export( $mail, true );
```

Set sender or forward as necessary.
Set usual mail accounts: info@, postmaster@, webmaster@, abuse@.

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

Tripwire access protection `.htaccess`.

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  #TODO: only allow from management server
  RewriteCond %{REQUEST_URI} !=/<SECRET_DIR>/tripwire.php
  RewriteRule ^ - [F]
</IfModule>
```

### Block access to a directory

`.htaccess`

```apache
Deny from all
# Apache 2.4
#Require all denied
```

### MySQL

- ALTER table engine
- //TODO

### MU plugins

- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/wordpress-fail2ban/mu-plugin/wp-fail2ban-mu.php
- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-disable-updates/disable-updates.php
- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-protect-plugins/protect-plugins.php

### SMTP

Use reliable smart host with STARTTLS for email sending.

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

- front page|grep "\<h1>string check" , |grep -Ei "mysql|php|error|notice|warning"
- pingdom  https://www.pingdom.com/free/
- RBL blacklists  https://www.rblmon.com/
- can-send-email @daily  use smarthost, whitelist on the smarthost, wget can-send-email.php,
wait 5 minutes, check mailbox for message with this subject:

`Subject: [admin] can-send-email from <HOSTNAME>`

```php
<?php
$headers = "X-Mailer: PHP/" . phpversion();
$subject = "[admin] can-send-email from {$_SERVER['SERVER_NAME']}";
$to      = "viktor@szepe.net";
// http://www.randomtext.me/download/txt/gibberish/p-5/20-35
$message = '
Much bowed when mammoth for lusciously lost a dear whooped some ouch
insufferably one indefatigably contemplated manifestly therefore much
mongoose and llama far feeble a cocky.

Robin the whistled scorpion mongoose fleetly past together toucan compulsively
coarsely inadvertent far hence within when up prissily amicable one and since
gawked jollily rude.

Met patiently excluding because and far sleazily sufficiently hyena enormously
that goodness much hawk mastodon walking this this whale ouch shed kookaburra
sleekly that one the affably.

Alarmingly much this the inoffensive in more much sobbed aboard reined that
labrador ordered much less jeez gibbered checked a wove selflessly goodness
this adjusted honey flustered a that turtle unavoidable hello messily.

Cringed apart complete bat knitted impulsively domestic behind jokingly a far
jeepers folded blubbered wildebeest lighthearted much exultingly yikes yawned
well winced swept far slowly decorously.
';
// @TODO SMTP STARTTLS by PHPMailer in WordPress `$mail->SMTPSecure = 'tls';`
$mail = mail( $to, $subject, $message, $headers );
if ( true !== $mail )
    print "mail() returned: " . var_export( $mail, true );
exit;
```

- see: shared-hosting-aid/remote-log-watch.sh @*/30
- FIXME munin-plugin: log size in lines
- FIXME remote-rotate error.log
- opcache/apc/memcache control panels @weekly
- domain name expiry @monthly
- Safebrowsing, Sucuri, Virustotal check @daily
- SEO Panel @weekly
- Analytics @weekly
- Google WMT @weekly
- PageSpeed, webpagetest.org @weekly

### List WordPress plugin names and paths

```js
plugin_names=jQuery('#wpbody .plugins .plugin-title strong').each(function (){console.log(jQuery(this).text());});

plugin_slugs=jQuery('#wpbody .plugins #the-list tr').each(function (){console.log(jQuery(this).attr('id'));});
```

### Move/clone site

```bash
# lftp
mkdir sr; cd sr
!wget -qN https://github.com/interconnectit/Search-Replace-DB/raw/master/index.php
!wget -qN https://github.com/interconnectit/Search-Replace-DB/raw/master/srdb.class.php
put index.php; put srdb.class.php
#mrm *; rmdir sr
```

#### Things to replace

1. http://domain.tld (no trailing slash)
2. /var/www/path/to/site (no trailing slash)
3. email@address.es
4. domain.tld

#### Change salt

Sucuri plugin

