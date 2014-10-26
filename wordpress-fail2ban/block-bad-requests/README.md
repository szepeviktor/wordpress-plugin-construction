# WordPress Block Bad Requests

Bans commonly used user names (hardcoded in a property), blocks non-static requests from CDN,
prevents author sniffing and examines every HTTP header in a login request.
Then triggers fail2ban accordingly.

To install copy `wp-login-bad-request.inc.php` beside your `wp-config.php` and put this line in top of `wp-config.php`:

```php
require_once( dirname( __FILE__ ) . '/wp-login-bad-request.inc.php' );
```

Or – in a worse case – install it as an mu-plugin, or in the **worst case** as a normal plugin.
It [executes very early](https://wordpress.org/plugins/whats-running/) when require()-d from `wp-config.php`.
As an mu-plugin WordPress loads before it executes. As a normal plugin the theme and some
(all before this one) active plugins are executed before it executes.
You can find out plugin execution order by this simple wp-cli command:

```
wp option get "active_plugins"
```

### List of HTTP request parts checked

- custom CDN headers `*`
- `author` query field
- request method to identify POST requests
- `log` POST variable (the WordPress username) `*`
- Accept header
- Accept-Language header
- Content-Type header
- Content-Length header
- Referer header `*`
- `action` query field to allow requests for password protected posts
- protocol `*`
- Connection header `*`
- Accept-Encoding
- cookie named `wordpress_test_cookie` `*`
- User-Agent header `*`

The list is in order of appearance, `*` means it can be disabled by an option below.

### Options

You can set these options by defining constants in your `wp-config.php`.
E.g. to allow Connection header other than `keep-alive` use this:

```php
define( 'O1_BAD_REQUEST_ALLOW_CONNECTION_CLOSE', true );
```

To blocks non-static requests from Amazon CloudFront use this:

```php
define( 'O1_BAD_REQUEST_CDN_HEADERS', 'HTTP_X_AMZ_CF_ID:HTTP_VIA:HTTP_X_FORWARDED_FOR' );
```

- (integer) `O1_BAD_REQUEST_COUNT` fail2ban trigger limit, `maxretry`
- (string) `O1_BAD_REQUEST_CDN_HEADERS` a colon separated list of HTTP headers your CDN is recognized by **can be faked**
- (boolean) `O1_BAD_REQUEST_ALLOW_REG` allow WP registration, disabled referer and test cookie checks
- (boolean) `O1_BAD_REQUEST_ALLOW_IE8` allow login with IE8 too (IE8 is not a `Mozilla/5.0` browser)
- (boolean) `O1_BAD_REQUEST_ALLOW_OLD_PROXIES` allow `HTTP/1.0` login requests
- (boolean) `O1_BAD_REQUEST_ALLOW_CONNECTION_CLOSE` allow other HTTP Connection headers than `keep-alive`
- (boolean) `O1_BAD_REQUEST_ALLOW_TWO_CAPS` allow user names like `JohnDoe`

### Other notes

You can customize the fail2ban trigger string in the `$prefix` property.

Tests are included as a shell script: `bad-request-test.sh`

[GitHub repository](https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/wordpress-fail2ban)
