## Protect normal plugins

1. Add your normal plugins to `$protected_plugins` array. Plugin paths look like `plugin-dir/plugin-file.php`.
1. Activate your plugins once.
1. From that time on they cannot be deactivated or deleted.
1. Automatic updates are enabled.

### wp-cli example

```
# wp plugin deactivate wp-solarized
Warning: Could not deactivate the 'wp-solarized' plugin.
```

### Notice

`wp-solarized/wp-solarized.php` is in `$protected_plugins` for demo purposes.
