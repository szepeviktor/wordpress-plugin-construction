tool

idea: wp-clean-up, https://github.com/ruhanirabin/WP-Optimize

db
- DB check & optimization
- delete transients with conditions
- delete revisions, drafts
- orphan post meta, comment meta, user meta
- SELECT SUBSTRING_INDEX(`option_name`, '_', 1) AS `prefix`, SUM(LENGTH(option_value)) AS `bytes` FROM `subd_options` GROUP BY prefix
  SELECT SUBSTRING_INDEX(`option_name`, '_', 2) AS `prefix`, SUM(LENGTH(option_value)) AS `bytes` FROM `subd_options` GROUP BY prefix
  exclude core options by pattern

comments
- spam/trash comments

media
- orphan relationships (media attachings)

files
- core checksum verify

links
- find internal 404s in the post content

dashboard
- remove some parts of the dashboard -> admin-trim-interface plugin
- remove comments/posts https://github.com/solarissmoke/disable-comments/raw/master/disable-comments.php

What db tables WP has?

+ wp-cli implementation

integration with other plugin
- Yoast
- pods
- Google XML Sitemaps
- etc.
