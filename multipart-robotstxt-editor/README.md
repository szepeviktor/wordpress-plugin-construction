### Robots.txt Specifications

- World Wide Web Consortium recommendation http://www.w3.org/TR/html4/appendix/notes.html#h-B.4.1.1
- by Google https://developers.google.com/webmasters/control-crawl-index/docs/robots_txt
- by MOZ http://moz.com/learn/seo/robotstxt

### Parts

- 'robotstxt' hook/other plugins' output [x] $public = get_option( 'blog_public' );
                                             apply_filters( 'robots_txt', $output, $public );
- manual records [x]
- remote records [x] a URL

### Notes

- Sitemap examples: $home/sitemap.xml $home/sitemap_index.xml
- recommended sitemaps: http://smythies.com/robots.txt http://www.lemgo.net/robots.txt
- separator \n ################################## \n
- add_filters( 'robots_txt', '$output, $public' 2, 99999 );
- core adds
    $site_url = parse_url( site_url() );
    $path = ( !empty( $site_url['path'] ) ) ? $site_url['path'] : '';
    $output .= "Disallow: $path/wp-admin/\n";
- don't run on '0' == $public
- admin notice in case of subdir, parse_url(home URL)
- one non-autoload option array
- 1 day transient
- only one "User-agent: *" -> join those
- preview link/button
- file creation instruction: wget -O ABSPATH . "robots.txt" home . "robots.txt"