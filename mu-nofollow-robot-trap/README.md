# Nofollow Robot Trap WordPress MU Plugin

*Nofollow Robot Trap* catches malicious robots not obeying `nofollow`.

1. Add the following line to your style.css: `.nfrt { display: none !important; }`
1. Add the allow page and the nofollow page to your sitemap.
1. Optionally add cache exceptions for the four URLs.
1. Flush rules on deletion of this mu-plugin (`wp rewrite flush`).
1. Install WordPress Fail2ban MU Plugin.

List of bait pages and their links:

- invisible link on the front page:
    - allow page
- allow page links to:
    - nofollow page
    - `rel=nofollow` block URL
    - protocol relative URL
- nofollow (meta tag) page links to:
    - block URL
- sitemap contains:
    - allow page
    - nofollow page
- the immediate block URL
- robots.txt contains:

```
Disallow: block URL
Allow: allow page
Allow: nofollow page
```
