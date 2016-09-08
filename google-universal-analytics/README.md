# @TODO - Google Universal Analytics for WordPress

add_inline to jquery-core

    // Track basic JavaScript errors - http://davidwalsh.name/track-errors-google-analytics
    window.addEventListener('error', function (e) {
        ga('send', 'exception', {'exDescription':e.message, 'exFatal':true, 'line':e.filename + ':  ' + e.lineno});
    });
    // Track AJAX errors
    jQuery(document).ajaxError(function (e, request, settings) {
        ga('send', 'exception', {'exDescription':'AJAX error', 'exFatal':true, 'url':settings.url, 'result':e.result});
    });

How it works... in "General Settings"

+ obfuscate UA number

### defines, filters ...


if ((this.protocol === 'http:' || this.protocol === 'https:') && this.hostname.indexOf(document.location.hostname) === -1) {
    ga('send', 'event', 'Outbound', this.hostname, this.pathname);


### mouse hover/focus tracking example ...

### 404

ga('send', 'event', 'Error', '404', 'page: ' + document.location.pathname + document.location.search + ' ref: ' + document.referrer, {'nonInteraction': 1});

### Custom usage in themes

In `wp-config.php` define `define( 'GUA_DISABLE', true );`

In the template file output the code `GUA()->print_script();`

### Adding extra JavaScript

```php
// Analytics.js Field Reference
// https://developers.google.com/analytics/devguides/collection/analyticsjs/field-reference
add_filter( 'gua_extra_javascript', function ( $js ) {
    $js .= "ga('set', 'anonymizeIp', true);\n";
    $js .= "ga('set', 'forceSSL', true);\n";

    return $js;
});
```


+ https://developers.google.com/tag-manager/quickstart
