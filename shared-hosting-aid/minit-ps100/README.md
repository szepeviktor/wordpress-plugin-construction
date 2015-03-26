- minit
- frontend-debugger, look for: style  <script
- uncss + cleancss > style-inline.css, check url()-s
- "?" in URL (e.g. rev=)
- script concat ")\n(" -> JS error, add ";" at EOF
- external js/css
- printed css
- printed js
- jQuery in head: doc.write()

```php
// above the fold styles
add_action( 'wp_print_styles', 'o1_print_inline_style', 0 );

function o1_print_inline_style() {
    printf( '<style id="theme-inline-css" type="text/css">%s</style>' . "\n",
        file_get_contents( get_stylesheet_directory() . '/style-inline.css' )
    );
}
```

scribe-comb!!
