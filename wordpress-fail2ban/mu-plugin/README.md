# WordPress fail2ban MU

This is the Must Use (mu-plugin) version of WordPess fail2ban.

### Advantages

- Early execution: Must Use plugins run before normal plugins thus banning sooner, causing less server load on DoS
- Security: cannot be deactivated, fiddled with by WordPress administrators
- Speed: because it is much simplier then the normal plugin with options

## Parts

- add_action( 'wp_login_failed', array( $this, 'login_failed' ) );
- add_action( 'wp_login', array( $this, 'login' ) );
- add_action( 'wp_logout', array( $this, 'logout' ) );
- add_action( 'retrieve_password', array( $this, 'lostpass' ) );
- add_action( 'init', array( $this, 'url_hack' ) );
- add_filter( 'redirect_canonical', array( $this, 'redirect' ), 1, 2 );
- add_action( 'plugins_loaded', array( $this, 'robot_403' ), 0 );
- add_action( 'template_redirect', array( $this, 'wp_404' ) );
- add_filter( 'wp_die_ajax_handler', array( $this, 'wp_die_ajax' ), 1 );
- add_filter( 'wp_die_xmlrpc_handler', array( $this, 'wp_die_xmlrpc' ), 1 );
- add_filter( 'wp_die_handler', array( $this, 'wp_die' ), 1 );
- add_action( 'robottrap_hiddenfield', array( $this, 'wpcf7_spam' ) );
- add_action( 'robottrap_mx', array( $this, 'wpcf7_spam_mx' ) );

#### Disabling parts

By default all options (fail2ban triggers) are enabled. If you would like to disable any of them
you have to a `remove_action()` or `remove_filter()` in your own code at `init`.

### Warning on updates!

An mu-plugin will not appear in the update notifications nor show its update status on the plugins page.
A nice global solution is a symlink in wp-content/mu-plugins which keeps it up-to-date.

### Set up the fail2ban filter

For fail2ban 0.8.x

```
failregex = [[]client <HOST>[]] (File does not exist|script not found or unable to stat): /\S*(, referer: \S+)?\s*$
            [[]client <HOST>[]] script '.*' not found or unable to stat/\S*(, referer: \S+)?\s*$
```

For fail2ban 0.9.x

```
failregex = ^%(_apache_error_client)s ((AH001(28|30): )?File does not exist|(AH01264: )?script not found or unable to stat): /\S*(, referer: \S+)?\s*$
            ^%(_apache_error_client)s script '.*' not found or unable to stat(, referer: \S+)?\s*$

```

Please examine tha latest filter failregexp in the
[fail2ban GitHub repo](https://github.com/fail2ban/fail2ban/blob/master/config/filter.d).
You can customize the fail2ban trigger string in the `$prefix` property of the class.

### Epilogue

All the best wishes to you!
