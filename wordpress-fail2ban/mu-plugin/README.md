## WordPress fail2ban MU

This is the Must Use (mu-plugin) version of WordPess fail2ban.

### Advantages

- Early execution: Must Use plugins run before normal plugins thus banning sooner, causing less server load on DoS
- Security: cannot be deactivated, fiddled with by WordPress administrators
- Speed: because it is much simplier then the normal plugin with options

#### Disabling parts

By default all options (fail2ban triggers) are enabled. If you would like to disable any of them
you have to comment out the corresponding `add_action()` or `add_filter()` in the constructor.
Please remeber to repeate this on each plugin update.

### Warning on updates

An mu-plugin will not appear in the update notifications nor show its update status on the plugins page.
You should have two copies of this file. One in wp-content/plugins to enable update notification,
and a copy or a symlink in wp-content/mu-plugins for the above advantages.

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
