<?php
/**
 * Restrict access by a cookie.
 *
 * @wordpress-plugin
 * Plugin Name: Access Cookie (MU)
 * Description: Restrict access to generated frontend pages.
 * Version: 0.1.2
 * Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
 * License: The MIT License (MIT)
 * Author: Viktor Szépe
 * GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
 * Style: phpcs --standard=WordPress --exclude=WordPress.Files.FileName,Generic.WhiteSpace.DisallowSpaceIndent,WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput access-cookie.php
 * Constants: O1_ACCESS_COOKIE_USER
 *
 * @package access-cookie
 */

/**
 * Access Cookie all-in-one class.
 */
class O1_Access_Cookie {

    /**
     * This is the expected code word which is a user name.
     *
     * @var string $secret_user
     */
    private $secret_user = '';
    /**
     * Cookie name prefix.
     *
     * @var string $cookie_name_prefix
     */
    private $cookie_name_prefix = 'wp-access-cookie_';
    /**
     * Encoded URL of the maintenance page.
     *
     * @var string $maintenance_page
     */
    private $maintenance_page = '/website-under-construction.html';

    /**
     * Initalize properties and set hooks.
     */
    public function __construct() {

        if ( ! $this->set_secret_user() ) {

            // Do nothing without a code word.
            return;
        }

        add_action( 'login_form_lostpassword', array( $this, 'set_cookie' ) );
        add_action( 'template_redirect', array( $this, 'check_cookie' ) );
        add_action( 'wp_logout', array( $this, 'delete_cookie' ) );
    }

    /**
     * Set cookie.
     */
    public function set_cookie() {

        if ( empty( $_POST['user_login'] ) || ! is_string( $_POST['user_login'] ) ) {

            return;
        }

        $login = trim( $_POST['user_login'] );
        $value = password_hash( $login, PASSWORD_DEFAULT );
        setcookie( $this->get_cookie_name(), $value, time() + DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, true );
    }

    /**
     * Check cookie.
     */
    public function check_cookie() {

        $cookie_name = $this->get_cookie_name();
        if (
            (
                isset( $_COOKIE[ $cookie_name ] )
                && password_verify( $this->secret_user, $_COOKIE[ $cookie_name ] )
            ) || true === apply_filters( 'access_cookie_allow', false )
        ) {

            // OK.
            return;
        }

        // Restrict access.
        status_header( 302 );
        wp_safe_redirect( $this->maintenance_page, 302 );
        exit();
    }

    /**
     * Clear cookie.
     */
    public function delete_cookie() {

        setcookie( $this->get_cookie_name(), '', -1, COOKIEPATH, COOKIE_DOMAIN );
    }

    /**
     * Return cookie name.
     *
     * COOKIEHASH is not available at MU plugin execution.
     *
     * @return string
     */
    private function get_cookie_name() {

        return $this->cookie_name_prefix . COOKIEHASH;
    }

    /**
     * Set code word from various sources.
     *
     * @return bool
     */
    private function set_secret_user() {

        // Constant.
        if ( defined( 'O1_ACCESS_COOKIE_USER' ) ) {
            $this->secret_user = O1_ACCESS_COOKIE_USER;

            return true;
        }

        // Environment variable.
        $secret_user = getenv( 'ACCESS_COOKIE_USER' );
        if ( false !== $secret_user ) {
            $this->secret_user = $secret_user;

            return true;
        }

        // Filter.
        $secret_user = apply_filters( 'access_cookie_user', false );
        if ( false !== $secret_user ) {
            $this->secret_user = $secret_user;

            return true;
        }

        // Option.
        $secret_user = get_option( 'access_cookie_user' );
        if ( false !== $secret_user ) {
            $this->secret_user = $secret_user;

            return true;
        }

        return false;
    }
}

new O1_Access_Cookie();
