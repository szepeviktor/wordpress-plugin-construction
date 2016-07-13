# Nofollow Robot Trap WordPress MU Plugin

*Nofollow Robot Trap* catches malicious robots not obeying `nofollow`.

1. Add the following line to your style.css: `.nfrt { display: none !important; }`
1. Add the allow page and the nofollow page to your sitemap.
1. Install WordPress Fail2ban MU Plugin.
1. Optionally add cache exceptions for the four URLs.
1. Flush rules on removal of this mu-plugin: `wp rewrite flush`

### List of baits and links

Invisible link on the front page to allow page.

Allow page links to

    - nofollow page
    - `rel=nofollow` for block URL
    - protocol relative URL

Nofollow (meta tag) page links to block URL.

The immediate block URL.

The protocol relative URL.

Sitemap contains

    - allow page
    - nofollow page

robots.txt contains:

```
Disallow: block URL
Allow: allow page
Allow: nofollow page
```
