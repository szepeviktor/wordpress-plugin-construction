<?php
/*
Plugin Name: E-mail shortcode
Version: 1.0.0
Description: It encodes an e-mail address using random mix of urlencode, HTML entities and then generates markup that's as tricky as possible, while remaining valid and parseable by browsers and XML-compliant parsers.
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: GPLv2 or later
Author: Viktor Szépe
Upstream: https://github.com/pornel/hCardValidator/blob/master/encode/index.php
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

/**
 * Encode e-mail
 *
 * - HTML class trap with invalid address
 * - Multiline href attribute
 * - URL encoded href attribute
 * - Empty query string in href attribute
 * - HTML entities
 * - HTML comment trap with spamtrap address
 *
 * Don't URL-encode subject or message in link attribute!
 *
 * [email2]1email@addre.ss[/email2]
 * [email2 nolink]2email@addre.ss[/email2]
 * [email2 id="contact-address" class="email"]3email@addre.ss[/email2]
 * [email2 address="email@addre.ss"]4Contact Us[/email2]
 * [email2 address="5email@addre.ss" /]
 * [email2 link="mailto:email@addre.ss?subject=About the site&message=Hello!"]6Contact Us[/email2]
 * [email2 link="mailto:7email@addre.ss?subject=About the site&message=Hello!" /]
 */
class O1_Email2_Shortcode {

    /**
     * HTML comment to insert in email addresses.
     */
    private $html_comment_template = "<!--\nmailto:%s\n</a>\n-->&shy;";
    /**
     * HTML template for the anchor element.
     *
     * It's class attribute contains newlines and attribute-like syntax.
     * Should be enough to confuse regex-based extractors.
     */
    private $link_attr_template = "href\n =\t'\t\n&#x20;%s%s\n'";
    private $link_template = "<a%s\nclass='%s\nhref=\"mailto:%s\"\n'\n%s>%s</a>";
    private $no_link_template = "<span%s\nclass='%s\nhref=\"mailto:%s\"\n'\n>%s</span>";
    /**
     * Public address to send spam to.
     */
    private $spamtrap = 'abuse@hotmail.com';

    /**
     * Register shortcode and set spamtrap address.
     */
    public function __construct() {

        if ( defined( 'EMAIL2_SPAMTRAP' ) && is_email( EMAIL2_SPAMTRAP ) ) {
            $this->spamtrap = EMAIL2_SPAMTRAP;
        }

        add_shortcode( 'email2', array( $this, 'shortcode' ) );
    }

    /**
     * Handle [email2] shortcode.
     */
    public function shortcode( $shortcode_atts, $content = null ) {

        $email2_defaults = array(
            'id' => null,
            'class' => null,
            'address' => null,
            'link' => null,
            'nolink' => null,
        );
        if ( '' === $shortcode_atts ) {
            $shortcode_atts = array();
        }
        $email2 = shortcode_atts( $email2_defaults, $shortcode_atts );
        $id_attr = '';
        $class = 'email2';
        $link = '';
        $query_string = '';
        $output = '';

        // HTML ID
        if ( ! empty( $email2['id'] ) ) {
            $id_attr = sprintf( ' id="%s"', esc_attr( $email2['id'] ) );
        }

        // HTML class
        if ( ! empty( $email2['class'] ) ) {
            $class = esc_attr( $email2['class'] );
        }

        // Address attribute
        if ( ! empty( $email2['address'] ) ) {
            $email2['address'] = trim( $email2['address'] );
            if ( is_email( $email2['address'] ) ) {
                // Query string is allowed in mailto:, even if empty
                $link = $email2['address'];
            }
        }

        // Link attribute
        if ( isset( $email2['link'] ) ) {
            $url = parse_url( $email2['link'] );
            $query = array();
            if ( isset( $url['query'] ) ) {
                parse_str( $url['query'], $query );
                $query_string = http_build_query( $query );
            }

            // ERROR: Missing email address in "link" attribute
            if ( empty( $url['path'] ) ) {
                return '<!-- email -->';
            }
            $url['path'] = trim( $url['path'] );

            // ERROR: Non-email in "link" attribute
            if ( ! is_email( $url['path'] ) ) {
                return '<!-- email -->';
            }

            $link = $url['path'];
        }

        // Email address is in the content
        if ( empty( $link ) && is_email( $content ) ) {
            $link = $content;
        }

        // Self-closing shortcode
        if ( empty( $content ) ) {
            if ( ! empty( $email2['address'] ) ) {
                $content = $email2['address'];
            } elseif ( ! empty( $url['path'] ) ) {
                $content = $url['path'];
            } else {
                // ERROR: Missing: content, link attribute, address attribute
                return '<!-- email -->';
            }
        }

        // No link target
        if ( empty( $link ) || isset( $email2['nolink'] ) || false !== array_search( 'nolink', $shortcode_atts ) ) {
            $class .= ' nolink';
            $output = sprintf( $this->no_link_template,
                $id_attr,
                $class,
                $this->generate_invalid_address(),
                $this->encode( $content, false )
            );
        } else {
            $link = sprintf( 'mailto:%s?', $this->random_url_encode( $link ) );
            $link_attr = sprintf( $this->link_attr_template,
                $this->encode( $link ),
                $query_string
            );
            $output = sprintf( $this->link_template,
                $id_attr,
                $class,
                $this->generate_invalid_address(),
                $link_attr,
                $this->encode( $content, false )
            );
        }

        return $output;
    }

    /**
     * Apply URL encoding at random just to be more confusing.
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

    /**
     * Apply HTML encoding.
     *
     * @param $addredd string       Email address.
     * @param $in_attribute boolean Address is in a HTML attribute.
     */
    public function encode( $address, $in_attribute = true ) {

        // @FIXME $address could be an array
        if ( false === mb_detect_encoding( $address[0], 'ASCII', true ) ) {
            if ( $in_attribute ) {
                return esc_attr( $address );
            } else {
                return esc_html( $address );
            }
        }

        $output = '';

        for ( $i = 0; $i < strlen( $address ); $i++ ) {
            // Insert HTML comment in the middle
            if ( ! $in_attribute && strlen( $address ) >> 1 === $i ) {
                $output .= sprintf( $this->html_comment_template, $this->spamtrap );
            }

            // Random characters are encoded + few special characters for added trickyness.
            // < > & characters are encoded to protect encoder against XSS.
            if ( mt_rand( 0, 100 ) < 40 && false === strpos( ' .:<>&', $address[ $i ] ) ) {
                $output .= $address[ $i ];
                continue;
            }

            // Mix of decimal and hexadecimal entities
            $hexa_format = sprintf( '&#x%%%s;',
                ( mt_rand() & 4 ) ? 'X' : 'x'
            );
            $format = ( mt_rand( 0, 100 ) > 66 ) ? '&#%d;' : $hexa_format;
            $output .= sprintf( $format, ord( $address[ $i ] ) );
        }

        return $output;
    }

    private function generate_invalid_address() {

        // Characters [a-z]
        $username = mt_rand( 97, 122 );
        $domain = mt_rand( 97, 122 );

        $address = sprintf( '%c@%c', $username, $domain );

        return $address;
    }

}

new O1_Email2_Shortcode();

// https://github.com/tillkruss/email-encoder/blob/master/email-address-encoder.php
