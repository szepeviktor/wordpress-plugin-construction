### Full featured PHP firewall

http://ninjafirewall.com/pro/overview.php



### Plugins and tools for shared hosting problems

- See: "Recommended plugins" in [../README.md](../README.md)
- Error log setup: shared-hosting-aid/enable-logging.php
- Error log watch: `error-log-monitor`
- Outgoing HTTP traffic monitoring: `snitch`
- Email traffic checking: ${D}/monitoring/cse/
- Cron job: shared-hosting-aid/wp-cron-http.sh
- WordPress database cleanup: `wp-clean-up`
- Integrity: `exploit-scanner`
- Security checklist: `gauntlet-security`



### FTP/SFTP access with lftp

```bash
SSLOFF="set ftp:ssl-allow off"

# lftp -e "${SSLOFF}; cd" -u 'FTP-USER,FTP_PASS' FTP_HOST.
lftp -e "cd" -u 'FTP-USER,FTP_PASS' FTP_HOST.
```



### Move/clone site with lftp

Turn off Maxer SiteProtection!

```bash
#!/usr/bin/lftp -f

#open ftp://DOMAIN.TLD
#cd website/html/
lcd tmp/
mkdir sr; cd sr
!wget -nv -N https://github.com/interconnectit/Search-Replace-DB/raw/master/index.php
!wget -nv -N https://github.com/interconnectit/Search-Replace-DB/raw/master/srdb.class.php
!ls -ltr | tail
put index.php; put srdb.class.php

# rm index.php srdb.class.php; cd ../; rmdir sr/
```

#### Search & replace items with wp-cli

`wp search-replace --precise --recurse-objects --all-tables-with-prefix`

1. http://DOMAIN.TLD or https (no trailing slash)
1. /home/PATH/TO/SITE (no trailing slash)
1. EMAIL@ADDRESS.ES (all addresses)
1. DOMAIN.TLD (now without http)

Manual replace constants in `wp-config.php`.



### Webserver settings

https://github.com/szepeviktor/hosting-check

- keep-alive
- mime-type
- content-compression
- content-cache

https://github.com/h5bp/html5-boilerplate/blob/master/dist/.htaccess

https://redbot.org/

#### Check PHP settings

See: shared-hosting-aid/php-vars.php

`wget -nv https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/shared-hosting-aid/php-vars.php`

#### Default email from address

See: shared-hosting-aid/php-mail-sender.php

`wget -nv https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/shared-hosting-aid/php-mail-sender.php`

- Set sender or forward as necessary.
- Set usual addresses: info@, postmaster@, abuse@
- Set up your account: webmaster@DOMAIN.TLD



### Security plugin

- sucuri-scanner plugin + sucuri-cleanup
- gauntlet-security plugin



### Plugins to protect

`wget -nv https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-protect-plugins/protect-plugins.php`



### wp-config.php

#### Skeleton

See wp-config.php

#### Change salt

- Sucuri plugin
- https://api.wordpress.org/secret-key/1.1/salt/

#### Block Bad Requests

`wget -nv https://github.com/szepeviktor/wordpress-fail2ban/raw/master/block-bad-requests/wp-fail2ban-bad-request-instant.inc.php`



### Root files

- .htaccess
- \*htaccess\*
- .user.ini
- index.html
- index.php
- robots.txt
- sitemap.xml
- sitemap.xml.gz
- crossdomain.xml
- labels.rdf
- apple-app-site-association (content: `{}`)

https://github.com/h5bp/mobile-boilerplate/blob/master/index.html

- favicon.ico
- apple-touch-icon.png
- apple-touch-icon-precomposed.png
- apple-touch-icon-120x120.png
- apple-touch-icon-120x120-precomposed.png
- apple-touch-icon*.png ...
- browserconfig.xml

- http://realfavicongenerator.net/
- http://realfavicongenerator.net/favicon_checker

Files for robots should not be indexed.

`.htaccess`

```apache
# Don't index files for robots
<FilesMatch "^(robots\.txt|sitemap\.xml|sitemap\.xml\.gz)$">
    Header append X-Robots-Tag "noindex"
</FilesMatch>
```



### PHP security check

`wget -nv https://github.com/sektioneins/pcc/raw/master/phpconfigcheck.php`

Enable access in `.htaccess`.

```apache
SetEnv PCC_ALLOW_IP 1.2.3.4
```



### File change notification

#### Sucuri plugin

#### Tripwire

```bash
git clone --recursive https://github.com/szepeviktor/Tripwire.git
cd Tripwire/
cp tripwire_config.sample.ini tripwire_config.ini
editor tripwire_config.ini
```

Tripwire access protection

`.htaccess`

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{REMOTE_ADDR} !=<MANAGEMENT-SERVER-IP-ADDRESS> [OR]
  RewriteCond %{REQUEST_URI} !=/<SECRET_DIR>/tripwire.php
  RewriteRule ^ - [F]
</IfModule>
```



### Block access to a directory

Deny all traffic `.htaccess`.

```apache
# Apache < 2.3
<IfModule !mod_authz_core.c>
  Order allow,deny
  Deny from all
  Satisfy All
</IfModule>
# Apache ≥ 2.3
<IfModule mod_authz_core.c>
  Require all denied
</IfModule>
```



### MySQL

- ALTER table engine
- @TODO



### List WordPress plugin names and paths

Go to the Plugins page and execute:

```js
// List plugin names
jQuery('#wpbody .plugins .plugin-title strong').each(function (){console.log(jQuery(this).text());});

// List plugin slugs (from "Deactivate" links)
var plungins=[].slice.call(document.querySelectorAll('#wpbody .plugins .plugin-title .deactivate a'));
plungins.forEach(function(p){console.log(decodeURIComponent(p.search.split('&')[1].split('=')[1]));});
```



### MU plugins

- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/wordpress-fail2ban/mu-plugin/wp-fail2ban-mu.php
- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-protect-plugins/protect-plugins.php
- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-disable-updates/disable-updates.php



### SMTP

Use reliable smart host with STARTTLS for email sending.

Consider using [Sendgrid](https://sendgrid.com/pricing) or [Mandrill](https://mandrill.com/signup/) for low volume email traffic.

`wget -nv https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-smtp-uri/smtp-uri.php`



### Monitoring

....... üzemeltetés @TODO
....... -----------
.......
....... 1. ügyfeleknek szolg leírása en/hu
....... 2. áttekintés/ütemezés magamnak
....... 3. setup with snippets and links
....... 4. routine: pseudo script for copy&pasting

```
# Static file check
01 *	* * *	web	/usr/bin/wget -qO- SITE-URL/wp-includes/wlwmanifest.xml|grep -qF '<serviceName>WordPress</serviceName>'

# PHP version and MySQL version check
01 *	* * *	web	/usr/bin/wget -qO- SITE-URL/ping.php|grep -qFx 'MD5-SUM'

# Front-page fixed string
# @FIXME Only one request for the front-page.
02 *	* * *	web	/usr/local/bin/firefox.sh -qO- FRONT-PAGE|grep -q '<h1>Title string'

# Front-page errors
02 *	* * *	web	/usr/local/bin/firefox.sh -qO- FRONT-PAGE|grep -qEi 'PHP \S+: |MySQL|error|notice|warning|Account.*Suspend'

# Front-page -exceptions = MD5
```

- Visual changes: https://visualping.io/ (PhantomJS, slimerJS, `compare -metric MAE ???PAE reference.png current.png`)
- [pingdom](https://www.pingdom.com/free/), shared-hosting-aid/ping.php
- @TODO rbl-watch.sh (also shared-hosting servers)
- [RBL blacklists monitoring](https://www.rblmon.com/)
- @TODO detect JavaScript errors, Piwik, http://jserrlog.appspot.com/ , https://github.com/mperdeck/jsnlog.js , https://developers.google.com/analytics/devguides/collection/analyticsjs/exceptions
https://github.com/errbit/errbit + https://github.com/airbrake/airbrake-js
- dns-watch, see: ${D}/monitoring/dns-watch.sh
- can-send-email @daily, see: ${D}/monitoring/cse/
- see: shared-hosting-aid/remote-log-watch.sh @*/30
- @TODO munin-plugin: log size in lines
- @TODO remote-rotate error.log
- opcache/apc/memcache control panels @weekly
- domain name expiry @monthly
- Safebrowsing, Sucuri, Virustotal check @daily
- SEO Panel @weekly
- Analytics @weekly
- Google Search Console @weekly
- PageSpeed, webpagetest.org @weekly
