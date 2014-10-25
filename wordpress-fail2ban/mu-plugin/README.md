# WordPress fail2ban MU

This is the Must Use (mu-plugin) version of *WordPress fail2ban* plugin.

### Advantages

- Early execution: Must Use plugins run before normal plugins thus banning sooner, causing less server load on DoS
- Security: cannot be deactivated, fiddled with by WordPress administrators
- Speed: because it is much simplier then the normal plugin with options

## Parts

- prevent redirection to admin (log in at `wp-admin`)
- stop brute force attacks (multiple login probes and password reminders from one IP)
- stop robots scanning non-existent URLs (404s, redirects, simple URL hacks, misinterpreted relative protocols)
- reply twith HTTP/403 Forbidden to robots on non-frontend requests
- stop showing 404 pages to robots but send HTTP/404
- ban sequential 404 requests (from the same IP)
- ban on invalid AJAX, XMLRPC and other `wp_die()`-handled requests
- stop spammers in cooperation with the [Contact Form 7 Robot Trap](https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/contact-form-7-robot-trap) plugin
- log logins and logouts

#### Disabling parts

By default all parts (fail2ban triggers) are enabled. If you would like to disable any of them
you have to `remove_action()` or `remove_filter()` it in your own code at `init`.

### Warning on updates!

An mu-plugin will not appear in the update notifications nor show its update status on the Plugins page.
A nice solution is a symlink in `wp-content/mu-plugins` which keeps it activated and also up-to-date.

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

Please examine the latest filter failregexp-s in the
[fail2ban GitHub repository](https://github.com/fail2ban/fail2ban/blob/master/config/filter.d).
You can customize the fail2ban trigger string in the `$prefix` property of the class.

**All the best wishes to you!**
