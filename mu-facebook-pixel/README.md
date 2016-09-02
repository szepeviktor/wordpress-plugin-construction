# Facebook Pixel for WordPress

### Setup

1. Copy `facebook-pixel.php` to `/wp-content/mu-plugins/`
1. On WordPress admin go to Settings / General
1. Enter your Facebook Pixel ID, click "Save settings"

## Constants

### `FBP_DISABLE`

Disables the tracking code. Define it as `true` in `wp-config`

### `FBP_FACEBOOK_RECOMMENDATION`

Inserts the tracking code before the ending `</head>` tag. Defaults is before the closing `</body>` tag.

## Filters

### `fbp_capability`

Disable for users with this capability. Defaults to `edit_pages`

### `fbp_extra_javascript`

Add extra JavaScript code before `fbq('track', "PageView");`. Defaults to an empty string.

## Examples

Adding extra JavaScript.

```php
// Analytics.js Field Reference
// https://developers.google.com/analytics/devguides/collection/analyticsjs/field-reference
add_filter( 'gua_extra_javascript', function ( $js ) {
    $js .= "ga('set', 'anonymizeIp', true);\n";
    $js .= "ga('set', 'forceSSL', true);\n";

    return $js;
});
```

Adding a Dynamic Event.

```js
( typeof fbq === 'function' ) &&  fbq('track', 'Lead' {
    content_name: 'Main Contact Form submit',
    referrer: document.referrer,
    userAgent: navigator.userAgent,
    language: navigator.language
});
```

## Remarks



On development sites the tracking code is not enabled, when `WP_ENV` is not `production`.
