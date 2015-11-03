<?php
/*
Plugin Name: Encode e-mail
Version: 1.0.1
Description: It encodes e-mail addresses using random mix of urlencode, HTML entities and then generates markup that's as tricky as possible, while remaining valid and parseable by browsers and XML-compliant parsers.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: GPLv2 or later
Author: Viktor Szépe
Upstream: https://github.com/pornel/hCardValidator/blob/master/encode/index.php
Upstream: https://wordpress.org/plugins/obfuscate-email/developers/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-encode-email
*/

/**
 * Encode e-mail
 *
 * - [email2]email@addre.ss[/email2]
 * - [email2 nolink]email@addre.ss[/email2]
 * - [email2 id="contact-address" class="email"]email@addre.ss[/email2]
 * - [email2 address="email@addre.ss"]Contact Us[/email2]
 * - [email2 address="email@addre.ss"]
 * - [email2 link="mailto:email@addre.ss?subject=About the site&message=Hello!"]Contact Us[/email2]
 * - [email2 link="mailto:email@addre.ss?subject=About the site&message=Hello!"]
 * - <?php echo do_shortcode( '[email2]email@addre.ss[/email2]' ); ?>
 *
 * Don't URL-encode subject or message!
 */
class O1_Obfuscate_Email {

    /**
     * Basically anything roughly like x@y.zz looks like an email address
     */
    private $email_regex = '([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})';
    /**
     * HTML comment to insert in email addresses
     */
    private $html_comment_template = "<!--\nmailto:%s\n</a>\n-->&shy;";
    /**
     * HTML template for the anchor element
     * That's class attribute containing newlines and attribute-like syntax.
     * Should be enough to confuse regex-based extractors.
     */
    private $link_attr_template = "href\n =\t'\t\n&#x20;%s%s\n'";
    private $link_template = "<a%s\nclass='%s\nhref=\"mailto:%s\"\n'\n%s>%s</a>";
    /**
     * Public address to send spam to
     */
    private $spamtrap = 'abuse@hotmail.com';

    public function __construct() {

        add_shortcode( 'email2', array( $this, 'shortcode' ) );

        // Don't obfuscate email addresses in the admin area.
        if ( is_admin() ) {
            return;
        }

        if ( defined ( 'EMAIL2_SPAMTRAP' ) && EMAIL2_SPAMTRAP ) {
            $this->spamtrap = EMAIL2_SPAMTRAP;
        }

        $filters = array(
            'link_description',
            'link_notes',
            'bloginfo',
            'nav_menu_description',
            'term_description',
            'the_title',
            'the_content',
            'get_the_excerpt',
            'comment_text',
            'list_cats',
            'widget_text',
            'the_author_email',
            'get_comment_author_email',
        );

        foreach( $filters as $filter ) {
            add_filter( $filter, array( $this, 'filter' ), 15 );
        }
    }

    public function filter( $content ) {

        // This matches emails except for those appearing in tag attributes.
        $content = preg_replace_callback( "#(?!<.*?){$this->email_regex}(?![^<>]*?>)#i",
// @TODO This need a wrapper to pass , false
            array( $this, 'encode' ),
            $content
        );

        // If checking again, then the only emails that are left are those in attributes.
        $content = preg_replace_callback( "#(mailto:\s*)?{$this->email_regex}#i",
            array( $this, 'encode' ),
            $content
        );

        return $content;
    }

    /**
     * Handle [email2] shortcode
     *
 @TODO Check all: empty, non-ascii, html-encoded, url-encoded
     */
    public function shortcode( $shortcode_atts, $content = null ) {

        $email2_defaults = array(
            'id' => null,
            'class' => null,
            'address' => null,
            'link' => null,
            'nolink' => null,
        );
        $email2 = shortcode_atts( $email2_defaults, $shortcode_atts );
        $link = '';
        $output = '';

        if ( ! empty( $email2['id'] ) ) {
            $id_attr = sprintf( ' id="%s"', esc_attr( $email2['id'] ) );
        } else {
            $id_attr = '';
        }

        if ( ! empty( $email2['class'] ) ) {
            $class = esc_attr( $email2['class'] );
        } else {
            $class = 'email';
        }

        if ( ! empty( $email2['address'] ) ) {
            $email2['address'] = trim( $email2['address'] );
            if ( is_email( $email2['address'] ) ) {
                // Query string is allowed in mailto:, even if empty
                $link = $email2['address'];
            }
        }

        if ( isset( $email2['link'] ) ) {
            $url = parse_url( $email2['link'] );
            $query = array();
            if ( isset( $url['query'] ) ) {
                parse_str( $url['query'], $query );
                $query_string = http_build_query( $query );
            } else {
                $query_string = '';
            }

            if ( ! empty( $url['path'] ) ) {
                $url['path'] = trim( $url['path'] );
                if ( is_email( $url['path'] ) ) {
                    $link = $url['path'];
                } else {
                    // ERROR: Not email in "link"
                    return '<!-- email -->';
                }
            } else {
                // ERROR: Missing email address in "link"
                return '<!-- email -->';
            }
        } else {
            $query_string = '';
        }

        if ( empty( $link ) ) {
            if ( is_email( $content ) ) {
                $link = $content;
            }
        }

        if ( isset( $email2['nolink'] ) || false !== array_search( 'nolink', $shortcode_atts ) ) {
            $link_attr = '';
            $query_string = '';
        } else {
            $link = sprintf( 'mailto:%s?', $this->random_url_encode( $link ) );
            $link_attr = sprintf( $this->link_attr_template,
                $this->encode( $link ),
                $query_string
            );
        }

        // Self-closing shortcode
        if ( empty( $content ) ) {
            if ( ! empty( $email2['address'] ) ) {
                $content = $email2['address'];
            } elseif ( ! empty( $url['path'] ) ) {
                $content = $url['path'];
            } else {
                // ERROR: Missing shortcode content and "link" with email and "address"
                return '<!-- email -->';
            }
        }

        $output = sprintf( $this->link_template,
            $id_attr,
            $class,
            $this->generate_invalid_address(),
            $link_attr,
            $this->encode( $content, false )
        );

        return $output;
    }

    /**
     * Apply URL encoding at random just to be more confusing
     *
     * Works only with ASCII strings.
     */
    private function random_url_encode( $string ) {

        if ( false === mb_detect_encoding( $string, 'ASCII', true ) ) {
            return esc_url( $string, array( 'mailto' ) );
        }

        $output = '';
        $length = strlen( $string );

        for ( $i = 0; $i < $length; $i += 1 ) {
            $output .= ( mt_rand( 0, 100 ) > 60 || ! ctype_alnum( $string[ $i ] ) ) ?
                sprintf( '%%%02x', ord( $string[ $i ] ) ) :
                $string[ $i ];
        }

        return $output;
    }

    public function encode( $address, $in_attribute = true ) {

        if ( false === mb_detect_encoding( $address, 'ASCII', true ) ) {
            if ( $in_attribute ) {
                return esc_attr( $address );
            } else {
                return esc_html( $address );
            }
        }

        $output = '';

        for( $i = 0; $i < strlen( $address ); $i++ ) {
            // Insert HTML comment in the middle
            if ( ! $in_attribute && strlen( $address ) >> 1 === $i ) {
                $output .= sprintf( $this->html_comment_template, $this->spamtrap );
            }

            // Random characters are encoded + few special characters for added trickyness.
            // < > & characters are encoded to protect encoder against XSS.
            if ( mt_rand( 0, 100 ) > 40 || false !== strpos( ' .:<>&', $address[ $i ] ) ) {
                // Mix of decimal and hexadecimal entities
                $hexa_format = sprintf( '&#x%%%s;',
                    ( mt_rand() & 4 ) ? 'X' : 'x'
                );
                $format = ( mt_rand( 0, 100 ) > 66 ) ? '&#%d;' : $hexa_format;
                $output .= sprintf( $format, ord( $address[ $i ] ) );
            } else {
                $output .= $address[ $i ];
            }
        }

        return $output;
    }

    private function generate_invalid_address() {

        // [a-z]
        $username = mt_rand( 97, 122 );
        $domain = mt_rand( 97, 122 );

        $address = sprintf( '%c@%c', $username, $domain );

        return $address;
    }

}

new O1_Obfuscate_Email();

/*
- HTML class trap with invalid address
- Multiline href attribute
- URL encoded href attribute
- Empty query string in href attribute
- HTML entities
- HTML comment trap with spamtrap address

+ regexp match in HTML text -> HTML encode
+ regexp match in HTML attribute, possibly in href="mailto:email@addre.ss?subject=Hello" -> URL encode + HTML encode

Test output: http://codebeautify.org/html-decode-string http://meyerweb.com/eric/tools/dencoder/
*/
