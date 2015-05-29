## Protect normal plugins

1. Add your normal plugins to `$protected_plugins` array. Plugin paths look like `plugin-dir/plugin-file.php`.
1. Activate your plugins once.
1. From that time on they cannot be deactivated, deleted or edited.
1. Automatic updates are enabled.

### wp-cli example

```
# wp plugin deactivate sucuri-scanner
Warning: Could not deactivate the 'sucuri-scanner' plugin.
```

### Notice

`sucuri-scanner/sucuri.php` is in `$protected_plugins` for demonstration purposes.
