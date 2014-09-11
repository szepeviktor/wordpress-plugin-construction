# WordPress fail2ban

Trigger banning on malicious requests.

### block-bad-requests

In wp-config.php:

```php
require_once( dirname( __FILE__ ) . '/wp-login-bad-request.inc.php' );
```

### mu-plugin

Copy to `wp-content/mu-plugins`.

### wp-fail2ban.php

The normal plugin version. (not yet syncronized to the mu-plugin)

(symlink mu to the plugins dir `cd wp-content/; ln -s plugins/wordpress-fail2ban/mu-plugin/errorlog-404.php mu-plugins/errorlog-404.php`)

